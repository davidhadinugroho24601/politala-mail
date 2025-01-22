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
    CREATE VIEW mail_receiver_view AS
SELECT 
    m.id AS mail_id,
    g.manager_id AS current_manager,
    m.group_id AS mail_group_id,
    u.name AS user_name,
    u.email AS user_email,
    g.name AS group_name,
    ac.status AS approval_status,
    ac.group_id AS group_receiver_id,
    CASE 
        WHEN g.id = ac.group_id THEN g.name 
        ELSE NULL 
    END AS group_receiver_name,
    m.created_at AS mail_created_at,
    m.updated_at AS mail_updated_at
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
        DB::statement("DROP VIEW IF EXISTS mail_receiver_view");

    }
};
