<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Zipcode_Groups extends CI_Migration
{

    public function up()
    {
        // -----------------------------
        // Create zipcode_groups table
        // -----------------------------
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'group_name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => FALSE
            ],
            'delivery_charges' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => FALSE
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => TRUE
            ]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('zipcode_groups');

        // -----------------------------
        // Create zipcode_group_items table (pivot)
        // -----------------------------
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'group_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE
            ],
            'zipcode_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => FALSE
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => TRUE
            ]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('group_id'); // index for foreign key
        $this->dbforge->create_table('zipcode_group_items');

        // Optionally, you can add a foreign key manually using db->query:
        $this->db->query('ALTER TABLE `zipcode_group_items` ADD CONSTRAINT `fk_group_id` FOREIGN KEY (`group_id`) REFERENCES `zipcode_groups`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;');
    }

    public function down()
    {
        $this->dbforge->drop_table('zipcode_group_items');
        $this->dbforge->drop_table('zipcode_groups');
    }
}
