<?php
/**
 * Database operations for Reminder List
 */
class Reminder_Database {

    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'reminder_list';
    }
    
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $this->table_name;
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title text NOT NULL,
            description text,
            due_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        // Log hasil untuk debugging
        error_log('Database table creation result: ' . print_r($result, true));
        
        // Periksa apakah tabel sudah ada
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        error_log('Table exists: ' . ($table_exists ? 'Yes' : 'No'));
    }
    
    
    public function get_reminders() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM $this->table_name ORDER BY due_date ASC");
    }
    
    public function add_reminder($title, $description, $due_date) {
        global $wpdb;
        
        return $wpdb->insert(
            $this->table_name,
            array(
                'title' => $title,
                'description' => $description,
                'due_date' => $due_date,
                'status' => 'pending'
            )
        );
    }
    
    public function update_reminder($id, $data) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id)
        );
    }
    
    public function delete_reminder($id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $id)
        );
    }
}
