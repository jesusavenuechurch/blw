<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('partners', 'kc_handle')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->string('kc_handle')->nullable()->after('phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('partners', 'kc_handle')) {
            Schema::table('partners', function (Blueprint $table) {
                $table->dropColumn('kc_handle');
            });
        }
    }
};
