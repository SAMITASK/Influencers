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
        Schema::create('user_influencer', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_uid', 191)->unique()->nullable(); // ID de Firebase
            $table->string('name');
            $table->string('phone_number')->unique();
            $table->email('email')->nullable();
            $table->string('social_handle')->nullable(); // Ej: @usuario
            $table->string('code')->unique(); // Código único del influencer
            $table->string('code_description')->nullable(); // Descripción opcional del código
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_influencer');
    }
};
