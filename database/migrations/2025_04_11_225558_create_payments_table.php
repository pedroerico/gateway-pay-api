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
            $table->foreignId('gateway_customer_id')
                ->nullable()
                ->constrained('gateway_customers')
                ->onDelete('cascade');
            $table->foreignId('api_client_customer_id')
                ->nullable()
                ->constrained('api_client_customers')
                ->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('method', PaymentMethodEnum::toArray());
            $table->enum('status', PaymentStatusEnum::toArray());
            $table->text('error_details')->nullable();
            $table->json('metadata')->nullable();
            $table->string('external_payment_id')->nullable()->comment('ID do pagamento no gateway externo');
            $table->json('gateway_response')->nullable()->comment('Resposta completa do gateway');
            $table->json('pix_data')->nullable();
            $table->json('boleto_data')->nullable();
            $table->json('credit_card_data')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'method']);
            $table->index('external_payment_id');
            $table->index('gateway_customer_id');
            $table->index('api_client_customer_id');
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
