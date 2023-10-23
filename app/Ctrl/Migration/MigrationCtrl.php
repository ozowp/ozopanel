<?php
namespace OzoPanel\Ctrl\Migration;

use OzoPanel\Ctrl\Migration\Tables\{
    Restrictions,
    SchemaMigrations
};

class MigrationCtrl {

    private $last_migration_version;

    public function __construct()
    {
        $this->check_migration();
    }

    /*
        To create a new migration
        1. Go to "run" method
        2. Specify a version and table name inside "migratio_to_table" method
        3. Go to table folder and specified table file
        4. call the parent::version_migration method inside "run_migration" method with version, table_name and method name where you will write your SQL command
    */

    public function run()
    {
        $this->migration_to_table(2.0, Restrictions::class);
    }

    public function check_migration()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "ozopanel_schema_migrations";

        $table_exists = $this->is_table_exists($table_name);
        // Check if the table exists
        if ($table_exists) {
            $this->last_migration_version = $this->get_last_migration_ver( $table_name );
            $this->run();
        } else {
            new SchemaMigrations(1.0);
        }
    }

    public function migration_to_table($version, $className)
    {
        if ($version > $this->last_migration_version) {
            new $className($version);
            $this->last_migration_version = $version;
        }
    }

    public function is_table_exists($table_name)
    {
        global $wpdb;
        $sql = "SHOW TABLES LIKE '$table_name'";
        return $wpdb->get_var($sql);
    }

    public function get_last_migration_ver($table_name)
    {
        global $wpdb;
        $last_migration = $wpdb->get_row(
            "SELECT * FROM $table_name ORDER BY id DESC LIMIT 1"
        );
        return (float) $last_migration->version;
    }
}
