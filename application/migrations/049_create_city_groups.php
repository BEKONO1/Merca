<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_City_Groups extends CI_Migration
{

    public function up()
    {
        // -----------------------------
        // Create city_groups table
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
        $this->dbforge->create_table('city_groups');

        // -----------------------------
        // Create city_group_items table (pivot)
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
            'city_id' => [
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
        $this->dbforge->add_key('group_id'); 
        $this->dbforge->create_table('city_group_items');

        // Optionally, you can add a foreign key manually using db->query:
        // $this->db->query('ALTER TABLE `city_group_items` ADD CONSTRAINT `fk_group_id` FOREIGN KEY (`group_id`) REFERENCES `city_groups`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;');
    }

    public function down()
    {
        $this->dbforge->drop_table('city_group_items');
        $this->dbforge->drop_table('city_groups');
    }
}
