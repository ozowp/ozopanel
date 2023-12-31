<?php

namespace OzoPanel\Ctrl\Migration\Tables;

use OzoPanel\Ctrl\Migration\Table;

class Restrictions extends Table {

    private $table_name = 'ozopanel_restrictions';

    public function run_migration( $current_version, $helpers ) {
        parent::version_migration(
            2.0, $current_version, $this->table_name, array(
				$this,
				'create_table',
            )
        );
    }

    public function create_table( $helpers ) {
        $helpers->create_table(
            $this->table_name,
            'ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            label VARCHAR(255),
            slug VARCHAR(255) NOT NULL,
            PRIMARY KEY (ID)'
        );
        $this->insert_preset();
    }

    public function insert_preset() {
    }
}
