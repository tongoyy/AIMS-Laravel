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
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('Item_Name');
            $table->text('Item_Description');
            $table->bigInteger('Quantity');
            $table->bigInteger('Price');
            $table->bigInteger('Unit');
            $table->bigInteger('Tax');
            $table->bigInteger('Total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
