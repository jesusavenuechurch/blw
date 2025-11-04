<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('registration_token')->unique()->nullable()->after('verification_token');
            $table->timestamp('token_used_at')->nullable()->after('registration_token');
            $table->boolean('is_registered')->default(false)->after('token_used_at');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['registration_token', 'token_used_at', 'is_registered']);
        });
    }
};