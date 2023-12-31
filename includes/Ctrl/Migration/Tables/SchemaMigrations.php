<?php
namespace OzoPanel\Ctrl\Migration\Tables;

use OzoPanel\Ctrl\Migration\Table;

class SchemaMigrations extends Table {

    private $table_name = 'ozopanel_schema_migrations';

    public function run_migration( $current_version, $helpers ) {
        parent::version_migration(
            1.0, $current_version, $this->table_name, array(
				$this,
				'create_table',
            )
        );
    }

    public function create_table( $helpers ) {
        $helpers->create_table(
            $this->table_name,
            'ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            version VARCHAR(255) NOT NULL,
            table_name VARCHAR(255) NOT NULL,
            created_at DATETIME,
            PRIMARY KEY (ID)'
        );
    }
}
