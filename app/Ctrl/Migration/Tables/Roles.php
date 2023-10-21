<?php

namespace WAM\Ctrl\Migration\Tables;

use WAM\Ctrl\Migration\Table as Table;

class Roles extends Table
{
    private $table_name = "wam_roles";

    public function run_migration($current_version, $helpers)
    {
        parent::version_migration(3.0, $current_version, $this->table_name, [
            $this,
            "create_table",
        ]);
    }

    public function create_table($helpers)
    {
        $helpers->create_table(
            $this->table_name,
            "ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME,
            message TEXT,
            post_id BIGINT(20) UNSIGNED,
            action_id BIGINT(20) UNSIGNED,
            created_by BIGINT(20) UNSIGNED,
            PRIMARY KEY (ID)"
        );
    }
}
