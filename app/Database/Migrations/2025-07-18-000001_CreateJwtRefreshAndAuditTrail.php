<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJwtRefreshAndAuditTrail extends Migration
{
    public function up()
    {
        // Table for refresh tokens
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'auto_increment' => true],
            'user_id'       => ['type' => 'BIGINT', 'null' => false],
            'refresh_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'expires_at'    => ['type' => 'DATETIME', 'null' => false],
            'created_at'    => ['type' => 'DATETIME', 'null' => false],
            'revoked'       => ['type' => 'BOOLEAN', 'default' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('jwt_refresh_tokens', true);

        // Table for revoked tokens
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'auto_increment' => true],
            'token'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'revoked_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('token');
        $this->forge->createTable('jwt_revoked_tokens', true);

        // Table for audit trail
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'auto_increment' => true],
            'user_id'    => ['type' => 'BIGINT', 'null' => true],
            'action'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'description'=> ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('audit_trails', true);
    }

    public function down()
    {
        $this->forge->dropTable('jwt_refresh_tokens', true);
        $this->forge->dropTable('jwt_revoked_tokens', true);
        $this->forge->dropTable('audit_trails', true);
    }
}
