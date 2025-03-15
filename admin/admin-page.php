<?php
// Pastikan jQuery dan ajaxurl tersedia
wp_enqueue_script('jquery');
?>

<script>
// Definisikan variabel reminder_ajax secara global
var reminder_ajax = {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('reminder_nonce'); ?>'
};
// Pastikan ajaxurl tersedia
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="reminder-list-container">
        <div class="reminder-form">
            <h2>Add New Reminder</h2>
            <form id="add-reminder-form">
                <div class="form-group">
                    <label for="reminder-title">Title:</label>
                    <input type="text" id="reminder-title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="reminder-description">Description:</label>
                    <textarea id="reminder-description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="reminder-due-date">Due Date:</label>
                    <input type="datetime-local" id="reminder-due-date" name="due_date" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="button button-primary">Add Reminder</button>
                </div>
                
                <div id="form-messages"></div>
            </form>
        </div>
        
        <div class="reminder-list">
            <h2>Your Reminders</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $reminders = $this->db->get_reminders();
                    if (!empty($reminders)) {
                        foreach ($reminders as $reminder) {
                            ?>
                            <tr>
                                <td><?php echo esc_html($reminder->title); ?></td>
                                <td><?php echo esc_html($reminder->description); ?></td>
                                <td><?php echo esc_html(date('F j, Y, g:i a', strtotime($reminder->due_date))); ?></td>
                                <td><?php echo esc_html(ucfirst($reminder->status)); ?></td>
                                <td>
                                    <button type="button" class="button edit-reminder" data-id="<?php echo esc_attr($reminder->id); ?>">Edit</button>
                                    <button type="button" class="button delete-reminder" data-id="<?php echo esc_attr($reminder->id); ?>">Delete</button>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="5">No reminders found.</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- Tombol test untuk debugging (tersembunyi) -->
<button id="test-ajax" style="display: none;">Test AJAX</button>

<script>
jQuery(document).ready(function($) {
    console.log('Debug script loaded');
    console.log('AJAX URL:', ajaxurl);
    console.log('Reminder AJAX object:', reminder_ajax);
    
    // Test AJAX connection
    $('#test-ajax').on('click', function() {
        $.post(ajaxurl, {
            action: 'test_ajax',
            nonce: reminder_ajax.nonce
        }, function(response) {
            console.log('Test response:', response);
        });
    });
});
</script>
