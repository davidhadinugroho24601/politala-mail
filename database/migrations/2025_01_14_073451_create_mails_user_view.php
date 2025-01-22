<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE VIEW mails_user_view AS
            SELECT 
                m.id,
                g.manager_id AS current_manager,
                m.group_id,
                u.name AS user_name,
                u.email AS user_email,
                g.name AS group_name,
                m.created_at,
                m.updated_at
            FROM mails m
            JOIN `groups` g ON m.group_id = g.id
            JOIN users u ON g.manager_id = u.id

        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS mails_user_view");
    }
};
