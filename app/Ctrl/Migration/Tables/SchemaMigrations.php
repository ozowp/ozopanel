<?php
namespace WAM\Ctrl\Migration\Tables;

use WAM\Ctrl\Migration\Table;

class SchemaMigrations extends Table {

    private $table_name = "wam_schema_migrations";

    public function run_migration($current_version, $helpers)
    {
        parent::version_migration(1.0, $current_version, $this->table_name, [
            $this,
            "create_table",
        ]);
    }

    public function create_table($helpers)
    {
        $helpers->create_table(
            $this->table_name,
            "ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            version VARCHAR(255) NOT NULL,
            table_name VARCHAR(255) NOT NULL,
            applied_at DATETIME,
            PRIMARY KEY (ID)"
        );
    }
}
