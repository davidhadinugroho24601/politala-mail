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
        Schema::create('mail_code_details', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->integer('number');
            $table->enum('type',['number', 'date', 'month', 'year', 'text'])->default('text');
            $table->unsignedBigInteger('code_id'); // Manager reference
            $table->timestamps();


            $table->foreign('code_id')->references('id')->on('mail_code')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_code_details_2');
    }
};
