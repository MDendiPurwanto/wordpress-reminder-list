<?php
/**
 * The core plugin class
 */
class Reminder_List {

    private $db;
    
    public function __construct() {
        require_once REMINDER_LIST_PLUGIN_DIR . 'includes/class-reminder-database.php';
        $this->db = new Reminder_Database();
    }
    
    public function run() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        
        // AJAX handlers
        add_action('wp_ajax_add_reminder', array($this, 'ajax_add_reminder'));
        add_action('wp_ajax_update_reminder', array($this, 'ajax_update_reminder'));
        add_action('wp_ajax_delete_reminder', array($this, 'ajax_delete_reminder'));
        
        // Shortcode
        add_shortcode('reminder_list', array($this, 'reminder_list_shortcode'));
        
        // Public scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Reminder List',
            'Reminders',
            'manage_options',
            'reminder-list',
            array($this, 'display_admin_page'),
            'dashicons-list-view',
            30
        );
    }
    
    public function display_admin_page() {
        require_once REMINDER_LIST_PLUGIN_DIR . 'admin/admin-page.php';
    }
    
    public function enqueue_admin_styles() {
        // Enqueue CSS
        wp_enqueue_style('reminder-admin-style', REMINDER_LIST_PLUGIN_URL . 'admin/css/admin-style.css', array(), REMINDER_LIST_VERSION);
        
        // Enqueue JavaScript
        wp_enqueue_script('reminder-admin-script', REMINDER_LIST_PLUGIN_URL . 'admin/js/admin-script.js', array('jquery'), REMINDER_LIST_VERSION, true);
        
        // Localize script - PENTING: Ini harus dipanggil SETELAH wp_enqueue_script
        wp_localize_script('reminder-admin-script', 'reminder_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('reminder_nonce')
        ));
    }
    
    
    public function enqueue_public_scripts() {
        wp_enqueue_style('reminder-public-style', REMINDER_LIST_PLUGIN_URL . 'public/css/reminder-style.css', array(), REMINDER_LIST_VERSION);
        wp_enqueue_script('reminder-public-script', REMINDER_LIST_PLUGIN_URL . 'public/js/reminder-script.js', array('jquery'), REMINDER_LIST_VERSION, true);
        
        wp_localize_script('reminder-public-script', 'reminder_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('reminder_nonce')
        ));
    }
    
    public function ajax_add_reminder() {
        check_ajax_referer('reminder_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $title = sanitize_text_field($_POST['title']);
        $description = sanitize_textarea_field($_POST['description']);
        $due_date = sanitize_text_field($_POST['due_date']);
        
        $result = $this->db->add_reminder($title, $description, $due_date);
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to add reminder');
        }
    }
    
    public function ajax_update_reminder() {
        // Similar to add_reminder with update logic
    }
    
    public function ajax_delete_reminder() {
        // Delete reminder logic
    }
    
    public function reminder_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'show_completed' => 'no',
        ), $atts, 'reminder_list');
        
        ob_start();
        include REMINDER_LIST_PLUGIN_DIR . 'public/reminder-list-template.php';
        return ob_get_clean();
    }
}
