<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            // $table->boolean('email_pending')->default(false);
            // $table->boolean('email_sent')->default(false);
            // $table->boolean('email_failed')->default(false);
            // $table->string('verification_token')->nullable();
            // $table->text('email_response')->nullable(); // future inbound emails
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'email_pending',
                'email_sent',
                'email_failed',
                'verification_token',
                'email_response',
            ]);
        });
    }
};