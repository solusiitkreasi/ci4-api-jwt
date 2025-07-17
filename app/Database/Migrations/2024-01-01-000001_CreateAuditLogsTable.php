<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'CREATE, UPDATE, DELETE, LOGIN, LOGOUT, etc.',
            ],
            'resource' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'users, products, transactions, etc.',
            ],
            'resource_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID of the affected resource',
            ],
            'old_data' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Data before change (for UPDATE/DELETE)',
            ],
            'new_data' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Data after change (for CREATE/UPDATE)',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->addKey(['action', 'resource']);
        $this->forge->addKey('created_at');
        
        // Foreign key constraint
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('audit_logs');
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs');
    }
}
