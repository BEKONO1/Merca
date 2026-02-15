<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_email_column_to_otps extends CI_Migration
{
    public function up()
    {
        $fields = [
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => TRUE,
                'after'      => 'mobile'
            ],
        ];

        $this->dbforge->add_column('otps', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('otps', 'email');
    }
}
