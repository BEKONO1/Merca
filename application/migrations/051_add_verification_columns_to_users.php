<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_verification_columns_to_users extends CI_Migration
{
    public function up()
    {
        $fields = [
            'email_verified' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => FALSE,
                'comment'    => '0:not verified, 1:verified',
                'after'      => 'mobile'
            ],
            'mobile_verified' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => FALSE,
                'comment'    => '0:not verified, 1:verified',
                'after'      => 'email_verified'
            ],
        ];

        $this->dbforge->add_column('users', $fields);

        $this->db->where_in('type', ['google', 'ios']);
        $this->db->update('users', ['email_verified' => 1]);

        $this->db->where('type', 'google');
        $this->db->update('users', ['mobile_verified' => 0]);
    }

    public function down()
    {
        $this->dbforge->drop_column('users', 'email_verified');
        $this->dbforge->drop_column('users', 'mobile_verified');
    }
}
