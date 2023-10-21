<?php
namespace WAM\Ctrl\Migration;

class Helpers {

    public function create_table($table_name, $fields)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $table_name;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name ($fields) $charset_collate;";

        require_once ABSPATH . "wp-admin/includes/upgrade.php";
        maybe_create_table($table_name, $sql);
    }

    public function insert_schema_migration($migration_table, $version) {
        global $wpdb;
        $table_name = $wpdb->prefix . "wam_schema_migrations";

        $data = [
            "version" => sprintf("%.1f", $version),
            "table_name" => $migration_table,
            "applied_at" => date("Y-m-d H:i:s"),
        ];

        $wpdb->insert($table_name, $data);
    }
}
