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
        Schema::create('template_availability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->nullable(); 
            $table->foreign('group_id')
            ->references('id')
            ->on('groups')
            ->onDelete('set null'); 
            $table->unsignedBigInteger('template_id')->nullable(); 
            $table->foreign('template_id')
            ->references('id')
            ->on('mail_templates')
            ->onDelete('set null'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_availability');
    }
};
