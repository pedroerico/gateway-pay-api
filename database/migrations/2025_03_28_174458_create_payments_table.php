<?php

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('amount', 10, 2);
            $table->enum('method', PaymentMethodEnum::toArray());
            $table->enum('status', PaymentStatusEnum::toArray());
            $table->json('metadata')->nullable();
            $table->string('client_id')->nullable();
            $table->string('gateway_id')->nullable();
            $table->string('gateway')->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('pix_data')->nullable();
            $table->json('boleto_data')->nullable();
            $table->json('credit_card_data')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'method']);
            $table->index('gateway_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
