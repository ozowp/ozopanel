<?php

namespace WAM\Ctrl\Migration\Tables;

use WAM\Ctrl\Migration\Table as Table;
use WAM\Ctrl\Migration\Helpers as Helpers;

class Notifications extends Table
{
    private $table_name = "wam_notifications";

    public function run_migration($current_version, $helpers)
    {
        parent::version_migration(1.0, $current_version, $this->table_name, [
            $this,
            "create_notifications_table",
        ]);
    }

    public function create_notifications_table($helpers)
    {
        global $wpdb;
        $helpers->create_table(
            $this->table_name,
            "ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            activity_id BIGINT(20) UNSIGNED NOT NULL ,
            receiver_id BIGINT(20) UNSIGNED NOT NULL ,
            is_seen TINYINT(1) NOT NULL DEFAULT 0,
            is_mail_sent TINYINT(1) NOT NULL DEFAULT 0,
            is_new TINYINT(1) NOT NULL DEFAULT 1,
            notification_type VARCHAR(255),
            mail_sent_at DATETIME,
            seen_at DATETIME,
            PRIMARY KEY (ID),
            FOREIGN KEY (activity_id) REFERENCES {$wpdb->prefix}wam_activities (ID) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES {$wpdb->prefix}users (ID) ON DELETE CASCADE"
        );
    }
}
