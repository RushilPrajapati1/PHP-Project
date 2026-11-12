<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->integer('amount_cents'); // always positive; direction comes from `type`
            $table->string('type'); // App\Enums\TransactionType
            $table->string('category');
            $table->string('description')->nullable();
            $table->date('occurred_on');
            $table->timestamps();

            $table->index(['account_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
