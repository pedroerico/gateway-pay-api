<?php

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
        Schema::create('gateway_customers', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignUuid('gateway_id')->constrained('gateways')->onDelete('cascade');
            $table->string('external_id');
            $table->json('external_data')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'gateway_id']);
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateway_customers');
    }
};
