<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_deliverable_groups_to_products extends CI_Migration
{

    public function up()
    {
        $fields = [
            'deliverable_group_type' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'null' => FALSE,
                'comment' => '(0:none, 1:all, 2:include, 3:exclude)',
                'after' => 'deliverable_cities'
            ],
            'deliverable_zipcodes_group' => [
                'type' => 'VARCHAR',
                'constraint' => 512,
                'null' => TRUE,
                'default' => NULL,
                'after' => 'deliverable_group_type'
            ],
            'deliverable_city_group_type' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'null' => FALSE,
                'comment' => '(0:none, 1:all, 2:include, 3:exclude)',
                'after' => 'deliverable_zipcodes_group'
            ],
            'deliverable_cities_group' => [
                'type' => 'VARCHAR',
                'constraint' => 512,
                'null' => TRUE,
                'default' => NULL,
                'after' => 'deliverable_city_group_type'
            ],
        ];

        $this->dbforge->add_column('products', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('products', 'deliverable_group_type');
        $this->dbforge->drop_column('products', 'deliverable_zipcodes_group');
        $this->dbforge->drop_column('products', 'deliverable_city_group_type');
        $this->dbforge->drop_column('products', 'deliverable_cities_group');
    }
}
