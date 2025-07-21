<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function success(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
        
            if (!$sessionId) {
                \Log::error('Payment success: No session_id provided');
                return redirect()->route('subscription')
                    ->with('error', 'Не удалось подтвердить платеж. Обратитесь в поддержку.');
            }

            \Log::info('Processing payment success for session: ' . $sessionId);

            Stripe::setApiKey(config('services.stripe.secret'));
        
            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);
                \Log::info('Stripe session retrieved', ['session' => $session->toArray()]);
            } catch (\Exception $e) {
                \Log::error('Failed to retrieve Stripe session: ' . $e->getMessage());
                return redirect()->route('subscription')
                    ->with('error', 'Ошибка при получении данных платежа. Обратитесь в поддержку.');
            }

            if ($session->payment_status !== 'paid') {
                \Log::warning('Payment not completed', ['status' => $session->payment_status]);
                return redirect()->route('subscription')
                    ->with('error', 'Платеж не был завершен. Статус: ' . $session->payment_status);
            }

            $user = auth()->user();
        
            // Проверяем, не обработан ли уже этот платеж
            $existingPayment = Payment::where('stripe_payment_id', $session->payment_intent)->first();
            if ($existingPayment) {
                \Log::info('Payment already processed', ['payment_id' => $existingPayment->id]);
                return redirect()->route('dashboard')
                    ->with('info', 'Этот платеж уже был обработан ранее.');
            }

            // Создаем запись о платеже
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $session->amount_total / 100, // Конвертируем из копеек
                'currency' => $session->currency,
                'status' => 'paid',
                'type' => 'subscription',
                'stripe_payment_id' => $session->payment_intent,
                'metadata' => $session->metadata ? $session->metadata->toArray() : []
            ]);

            \Log::info('Payment record created', ['payment_id' => $payment->id]);

            // Определяем количество месяцев
            $plan = $session->metadata['plan'] ?? 'monthly';
            $months = $plan === 'yearly' ? 12 : 1;

            // Активируем премиум
            $oldPremiumUntil = $user->premium_until;
            $user->activatePremium($months);
        
            \Log::info('Premium activated', [
                'user_id' => $user->id,
                'old_premium_until' => $oldPremiumUntil,
                'new_premium_until' => $user->premium_until,
                'months_added' => $months
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Премиум подписка успешно активирована! Действует до ' . $user->premium_until->format('d.m.Y'));

        } catch (\Exception $e) {
            \Log::error('Payment success error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        
            return redirect()->route('subscription')
                ->with('error', 'Произошла ошибка при обработке платежа. Если деньги были списаны, обратитесь в поддержку. Ошибка: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('subscription')
            ->with('info', 'Платеж был отменен.');
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            \Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return response('Webhook signature verification failed', 400);
        }

        // Обрабатываем события
        switch ($event['type']) {
            case 'checkout.session.completed':
                $session = $event['data']['object'];
                $this->handleSuccessfulPayment($session);
                break;
            
            case 'invoice.payment_failed':
                $invoice = $event['data']['object'];
                $this->handleFailedPayment($invoice);
                break;
                
            default:
                \Log::info('Unhandled webhook event: ' . $event['type']);
        }

        return response('Webhook handled', 200);
    }

    private function handleSuccessfulPayment($session)
    {
        try {
            $userId = $session['metadata']['user_id'] ?? null;
            $plan = $session['metadata']['plan'] ?? 'monthly';
            
            if (!$userId) {
                \Log::error('No user_id in webhook metadata');
                return;
            }

            $user = \App\Models\User::find($userId);
            if (!$user) {
                \Log::error('User not found: ' . $userId);
                return;
            }

            // Создаем запись о платеже
            Payment::create([
                'user_id' => $user->id,
                'amount' => $session['amount_total'] / 100,
                'currency' => $session['currency'],
                'status' => 'paid',
                'type' => 'subscription',
                'stripe_payment_id' => $session['payment_intent'],
                'metadata' => $session['metadata']
            ]);

            // Активируем премиум
            $months = $plan === 'monthly' ? 1 : 12;
            $user->activatePremium($months);

            \Log::info('Premium activated for user: ' . $user->id);

        } catch (\Exception $e) {
            \Log::error('Error handling successful payment: ' . $e->getMessage());
        }
    }

    private function handleFailedPayment($invoice)
    {
        try {
            $customerId = $invoice['customer'];
            \Log::warning('Payment failed for customer: ' . $customerId);
            
            // Здесь можно добавить логику для обработки неудачных платежей
            // Например, отправка уведомления пользователю
            
        } catch (\Exception $e) {
            \Log::error('Error handling failed payment: ' . $e->getMessage());
        }
    }
}
