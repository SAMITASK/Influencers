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
            $table->string('email')->nullable();
            $table->string('social_handle')->nullable(); // Ej: @usuario
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('influencer_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('influencer_id')->constrained('user_influencer')->onDelete('cascade');
            $table->string('code')->unique(); // el código que se usa en las entradas
            $table->string('description')->nullable(); // ejemplo: "Promo Navidad"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('influencer_codes');
        Schema::dropIfExists('user_influencer');
    }
};
