<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scanner_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pairing_code', 12)->nullable()->unique();
            $table->timestamp('pairing_expires_at')->nullable();
            $table->timestamp('paired_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('platform')->nullable();
            $table->string('app_version')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('check_ins', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->foreignId('scanner_device_id')->nullable()->after('partner_id')->constrained('scanner_devices')->nullOnDelete();
        });

        // Remembers deleted partners so scanners can drop them on their next sync.
        Schema::create('partner_tombstones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id')->index();
            $table->timestamp('deleted_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_tombstones');
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scanner_device_id');
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
        Schema::dropIfExists('scanner_devices');
    }
};
