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
        Schema::table('partners', function (Blueprint $table) {
            // Add KC Handle
            $table->string('kc_handle')->nullable()->after('tier');
            
            // Add spouse information
            $table->boolean('coming_with_spouse')->default(false)->after('delivery_method');
            $table->enum('spouse_title', ['Bro', 'Sis', 'Dcn', 'Pastor'])->nullable()->after('coming_with_spouse');
            $table->string('spouse_name')->nullable()->after('spouse_title');
            $table->string('spouse_kc_handle')->nullable()->after('spouse_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'tier',
                'kc_handle',
                'coming_with_spouse',
                'spouse_title',
                'spouse_name',
                'spouse_kc_handle',
            ]);
        });
    }
};