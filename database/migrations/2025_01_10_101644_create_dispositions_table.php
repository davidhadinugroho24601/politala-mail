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
        Schema::create('dispositions', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('mail_id'); // Foreign key to 'mails' table
            $table->text('text'); // Text column for disposition description
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('mail_id')->references('id')->on('mails')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispositions');
    }
};
