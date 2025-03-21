<?php
/**
 * Plugin Name: Reminder List
 * Description: A simple reminder list plugin for WordPress
 * Version: 1.0.0
 * Author: Muhamad Dendi Purwanto
 * Text Domain: reminder-list
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('RL_VERSION', '1.0.0');
define('RL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RL_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the core classes
require_once RL_PLUGIN_DIR . 'includes/class-rl-database.php';
require_once RL_PLUGIN_DIR . 'includes/class-rl-main.php';

// Tambahkan handler untuk test AJAX
add_action('wp_ajax_rl_test_ajax', 'rl_handle_test_ajax');

/**
 * Handler untuk test AJAX
 */
function rl_handle_test_ajax() {
    wp_send_json_success('AJAX is working!');
}

// Activation hook
function rl_activate_plugin() {
    $database = new RL_Database();
    $database->create_tables();
    
    // Tambahkan log aktivasi untuk debugging
    error_log('Reminder List plugin activated');
}
register_activation_hook(__FILE__, 'rl_activate_plugin');

// Deactivation hook
function rl_deactivate_plugin() {
    // Cleanup if needed
    error_log('Reminder List plugin deactivated');
}
register_deactivation_hook(__FILE__, 'rl_deactivate_plugin');

// Tambahkan hook untuk menangani AJAX di admin dan frontend
add_action('wp_ajax_rl_add_reminder', 'rl_handle_add_reminder_ajax');
add_action('wp_ajax_rl_update_reminder', 'rl_handle_update_reminder_ajax');
add_action('wp_ajax_rl_delete_reminder', 'rl_handle_delete_reminder_ajax');

/**
 * Handle AJAX request untuk menambahkan reminder
 */
function rl_handle_add_reminder_ajax() {
    // Verifikasi nonce untuk keamanan
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rl_reminder_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Periksa izin
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
        return;
    }
    
    // Validasi data
    if (empty($_POST['title']) || empty($_POST['due_date'])) {
        wp_send_json_error('Title and due date are required');
        return;
    }
    
    // Sanitasi input
    $title = sanitize_text_field($_POST['title']);
    $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
    $due_date = sanitize_text_field($_POST['due_date']);
    
    // Log untuk debugging
    error_log('Adding reminder via AJAX: ' . $title . ' - ' . $due_date);
    
    // Tambahkan reminder ke database
    $db = new RL_Database();
    $result = $db->add_reminder($title, $description, $due_date);
    
    if ($result) {
        wp_send_json_success(array('message' => 'Reminder added successfully'));
    } else {
        wp_send_json_error('Failed to add reminder to database');
    }
}

/**
 * Handle AJAX request untuk mengupdate reminder
 */
function rl_handle_update_reminder_ajax() {
    // Verifikasi nonce untuk keamanan
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rl_reminder_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Periksa izin
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
        return;
    }
    
    // Validasi data
    if (empty($_POST['id'])) {
        wp_send_json_error('Reminder ID is required');
        return;
    }
    
    $id = intval($_POST['id']);
    $data = array();
    
    if (isset($_POST['title'])) {
        $data['title'] = sanitize_text_field($_POST['title']);
    }
    
    if (isset($_POST['description'])) {
        $data['description'] = sanitize_textarea_field($_POST['description']);
    }
    
    if (isset($_POST['due_date'])) {
        $data['due_date'] = sanitize_text_field($_POST['due_date']);
    }
    
    if (isset($_POST['status'])) {
        $data['status'] = sanitize_text_field($_POST['status']);
    }
    
    // Update reminder di database
    $db = new RL_Database();
    $result = $db->update_reminder($id, $data);
    
    if ($result !== false) {
        wp_send_json_success(array('message' => 'Reminder updated successfully'));
    } else {
        wp_send_json_error('Failed to update reminder');
    }
}

/**
 * Handle AJAX request untuk menghapus reminder
 */
function rl_handle_delete_reminder_ajax() {
    // Verifikasi nonce untuk keamanan
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rl_reminder_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Periksa izin
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
        return;
    }
    
    // Validasi data
    if (empty($_POST['id'])) {
        wp_send_json_error('Reminder ID is required');
        return;
    }
    
    $id = intval($_POST['id']);
    
    // Hapus reminder dari database
    $db = new RL_Database();
    $result = $db->delete_reminder($id);
    
    if ($result) {
        wp_send_json_success(array('message' => 'Reminder deleted successfully'));
    } else {
        wp_send_json_error('Failed to delete reminder');
    }
}

// Start the plugin
function rl_init_plugin() {
    $plugin = new RL_Main();
    $plugin->run();
}
rl_init_plugin();
