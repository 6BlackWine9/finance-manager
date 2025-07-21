<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Удаляем таблицу если она существует
        Schema::dropIfExists('payments');
        
        // Создаем таблицу заново
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('stripe_payment_id')->nullable();
            $table->string('stripe_session_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('rub');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('type', ['subscription', 'one_time'])->default('subscription');
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Добавляем внешний ключ
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Индексы
            $table->index(['user_id', 'status']);
            $table->index('stripe_payment_id');
            $table->index('stripe_session_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};