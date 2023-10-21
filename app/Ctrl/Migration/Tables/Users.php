<?php

namespace WAM\Ctrl\Migration\Tables;

use WAM\Ctrl\Migration\Table as Table;

class Users extends Table
{
    private $table_name = "wam_users";

    public function run_migration($current_version, $helpers)
    {
        parent::version_migration(2.0, $current_version, $this->table_name, [
            $this,
            "create_table",
        ]);
    }

    public function create_table($helpers)
    {
        $helpers->create_table(
            $this->table_name,
            "ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slug VARCHAR(255) NOT NULL,
            label VARCHAR(255),
            PRIMARY KEY (ID)"
        );
        $this->insert_preset();
    }

    public function insert_preset()
    {

    }
}
