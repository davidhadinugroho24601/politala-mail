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
        DB::statement("
             CREATE VIEW group_details_view AS
            SELECT 
                gd.id,
                gd.user_id,
                gd.group_id,
                u.name AS user_name,
                u.email AS user_email,
                g.name AS group_name,
                gd.created_at,
                gd.updated_at
            FROM group_details gd
            JOIN users u ON gd.user_id = u.id
            JOIN `groups` g ON gd.group_id = g.id

        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS group_details_view");
    }
};
