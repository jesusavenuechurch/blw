<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('qr_code_path');
            $table->timestamp('photo_updated_at')->nullable()->after('photo_path');
            $table->timestamp('photo_consent_at')->nullable()->after('photo_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'photo_updated_at', 'photo_consent_at']);
        });
    }
};
