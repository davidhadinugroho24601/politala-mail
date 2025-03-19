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
        Schema::create('path_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id'); 
            $table->unsignedBigInteger('path_id');
            $table->integer('order'); 
            $table->enum('authority', ['skip', 'read','approve']); 
            $table->timestamps();

            $table->foreign('path_id')
            ->references('id')
            ->on('mail_paths')
            ->onDelete('cascade'); 

            $table->foreign('group_id')
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
        Schema::dropIfExists('path_detail');
    }
};
