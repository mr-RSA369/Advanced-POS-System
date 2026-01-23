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
        if (!Schema::hasTable('business_days')) {
            Schema::create('business_days', function (Blueprint $table) {
                $table->id();
                $table->date('business_date');
                $table->timestamp('opened_at');
                $table->timestamp('closed_at')->nullable();
                $table->boolean('is_open')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_days');
    }
};
