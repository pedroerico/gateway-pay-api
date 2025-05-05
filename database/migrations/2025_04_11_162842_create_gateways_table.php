<?php

use App\Enums\PaymentGatewayEnum;
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
        Schema::create('gateways', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code');
            $table->string('api_key');
            $table->string('base_url');
            $table->string('webhook_header')->nullable();
            $table->string('webhook_token')->nullable();
            $table->string('webhook_url')->nullable();
            $table->integer('priority')->unique();
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->json('allowed_ips')->nullable();
            $table->timestamps();

            $table->index(['priority', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateways');
    }
};
