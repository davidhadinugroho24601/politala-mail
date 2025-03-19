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
        Schema::create('mail_paths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id'); 
            $table->unsignedBigInteger('sender_id'); 
            $table->unsignedBigInteger('receiver_id'); 
            // $table->integer('order'); 
            // $table->enum('authority', ['skip', 'read','approve']);
            $table->timestamps();


            $table->foreign('template_id')
            ->references('id')
            ->on('mail_templates')
            ->onDelete('cascade'); 
            
            $table->foreign('sender_id')
            ->references('id')
            ->on('groups')
            ->onDelete('cascade'); 

            $table->foreign('receiver_id')
            ->references('id')
            ->on('groups')
            ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_paths');
    }
};
