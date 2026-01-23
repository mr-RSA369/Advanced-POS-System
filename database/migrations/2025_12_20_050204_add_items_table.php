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
    {   if(!Schema::hasTable('items')) {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
            ->constrained('categories')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();
            $table->string('name');
            $table->integer('price');
            $table->enum('status', ['available', 'not-available']);
            $table->timestamps();
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
