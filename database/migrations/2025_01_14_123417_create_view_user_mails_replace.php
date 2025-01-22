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
        CREATE OR REPLACE VIEW mails_user_view AS
        SELECT 
            m.id,
            m.writer_id,
            CASE 
                WHEN m.writer_id= u.id THEN u.name 
                ELSE NULL 
            END AS writer_name,
            m.target_id,
            CASE 
                WHEN m.target_id= u.id THEN u.name 
                ELSE NULL 
            END AS target_name,
            m.final_id,
            CASE 
                WHEN m.final_id= u.id THEN u.name 
                ELSE NULL 
            END AS final_target_name,
            g.manager_id AS current_manager,
            m.group_id,
            u.name AS user_name,
            u.email AS user_email,
            g.name AS group_name,
            m.created_at,
            ac.id AS approval_id,
            ac.status,
            m.updated_at
        FROM mails m
        JOIN `groups` g ON m.group_id = g.id
        JOIN users u ON g.manager_id = u.id
        JOIN approval_chain ac ON ac.mail_id = m.id;

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
