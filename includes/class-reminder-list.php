<?php
/**
 * The core plugin class
 */
class RL_Main {

    private $db;
    
    public function __construct() {
        require_once RL_PLUGIN_DIR . 'includes/class-rl-database.php';
        $this->db = new RL_Database();
    }
    
    public function run() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        
        // Shortcode
        add_shortcode('rl_reminder_list', array($this, 'reminder_list_shortcode'));
        
        // Public scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Reminder List',
            'Reminders',
            'manage_options',
            'rl-reminder-list',
            array($this, 'display_admin_page'),
            'dashicons-list-view',
            30
        );
    }
    
    public function display_admin_page() {
        require_once RL_PLUGIN_DIR . 'admin/admin-page.php';
    }
    
    public function enqueue_admin_styles($hook) {
        // Hanya muat di halaman plugin kita
        if ('toplevel_page_rl-reminder-list' !== $hook) {
            return;
        }
        
        wp_enqueue_style('rl-admin-style', RL_PLUGIN_URL . 'admin/css/admin-style.css', array(), RL_VERSION);
        
        // Enqueue admin script
        wp_enqueue_script('rl-admin-script', RL_PLUGIN_URL . 'admin/js/admin-script.js', array('jquery'), RL_VERSION, true);
        
        // Localize script dengan variabel AJAX
        wp_localize_script('rl-admin-script', 'rl_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rl_reminder_nonce')
        ));
    }
    
    public function enqueue_public_scripts() {
        // Hanya muat jika shortcode digunakan di halaman
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'rl_reminder_list')) {
            wp_enqueue_style('rl-public-style', RL_PLUGIN_URL . 'public/css/reminder-style.css', array(), RL_VERSION);
            wp_enqueue_script('rl-public-script', RL_PLUGIN_URL . 'public/js/reminder-script.js', array('jquery'), RL_VERSION, true);
            
            // Localize script untuk AJAX
            wp_localize_script('rl-public-script', 'rl_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rl_reminder_nonce')
            ));
        }
    }
    
    /**
     * Shortcode untuk menampilkan reminder list di frontend
     */
    public function reminder_list_shortcode($atts) {
        // Default atribut
        $atts = shortcode_atts(array(
            'limit' => 10,
            'show_completed' => 'no',
        ), $atts, 'rl_reminder_list');
        
        // Konversi atribut
        $limit = intval($atts['limit']);
        $show_completed = ($atts['show_completed'] === 'yes');
        
        // Ambil reminder dari database
        $reminders = $this->db->get_reminders();
        
        // Mulai output buffering
        ob_start();
        
        // Include template
        include RL_PLUGIN_DIR . 'public/reminder-list-template.php';
        
        // Kembalikan output
        return ob_get_clean();
    }
}
