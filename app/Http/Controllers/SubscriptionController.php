<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class SubscriptionController extends Controller
{
    public function index()
    {
        return view('subscription.index');
    }

    public function subscribe(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'plan' => 'required|in:monthly'
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            \Log::info('Creating Stripe session for user: ' . $user->id . ', plan: ' . $request->plan);

            // Создаем сессию Stripe Checkout только для месячного плана
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'rub',
                        'product_data' => [
                            'name' => 'Finance Manager - Премиум подписка',
                            'description' => 'Месячная подписка на премиум функции',
                        ],
                        'unit_amount' => 29900, // 299 рублей в копейках
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel'),
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => 'monthly',
                    'months' => 1
                ],
                'customer_email' => $user->email,
            ]);

            \Log::info('Stripe session created', ['session_id' => $session->id]);

            return redirect($session->url);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error('Stripe API error: ' . $e->getMessage());
            return redirect()->route('subscription')
                ->with('error', 'Ошибка платежной системы: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Subscription error: ' . $e->getMessage());
            return redirect()->route('subscription')
                ->with('error', 'Ошибка при создании подписки. Попробуйте позже.');
        }
    }
}
