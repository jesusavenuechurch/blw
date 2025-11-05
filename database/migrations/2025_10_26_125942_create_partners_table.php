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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // Bro/Sis/Dcn/Pastor
            $table->string('designation')->nullable(); // Bro/Sis/Dcn/Pastor
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('region')->nullable();
            $table->string('zone')->nullable();
            $table->string('group')->nullable();
            $table->string('church')->nullable();
            $table->integer('ror_copies_sponsored')->nullable();

            $table->boolean('will_attend_ippc')->default(false);
            $table->boolean('will_be_at_exhibition')->nullable(); // only relevant if above = yes
            $table->text('delivery_method')->nullable(); // if not attending, how to deliver

            // QR code storage (can store file path or unique code)
            $table->string('qr_code_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
