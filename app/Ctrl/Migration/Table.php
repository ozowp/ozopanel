<?php
namespace WAM\Ctrl\Migration;

use WAM\Ctrl\Migration\Helpers;

class Table {
    public function __construct($current_version)
    {
        $helpers = new Helpers();
        $this->run_migration($current_version, $helpers);
    }

    public function run_migration($current_version, $helpers)
    {
    }

    public function version_migration(
        $version,
        $current_version,
        $table_name,
        $callback
    ) {
        $helpers = new Helpers();
        if ($current_version == $version) {
            $callback($helpers);
            $helpers->insert_schema_migration($table_name, $version);
        }
    }
}
