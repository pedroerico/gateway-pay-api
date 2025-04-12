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
        Schema::create('api_client_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignUuid('api_client_id')->constrained('api_clients')->onDelete('cascade');
            $table->string('external_id')->nullable();
            $table->json('external_data')->nullable();
            $table->timestamps();

            $table->unique(['api_client_id', 'external_id']);
            $table->unique(['customer_id', 'api_client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_client_customers');
    }
};
