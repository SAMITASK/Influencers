<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_influencer', function (Blueprint $table) {
            $table->enum('role', ['influencer', 'admin'])->default('influencer')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('user_influencer', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
