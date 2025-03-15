<?php
// Pastikan jQuery tersedia
wp_enqueue_script('jquery');
?>

<script>
// Definisikan variabel yang diperlukan
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
var rl_ajax = {
    nonce: '<?php echo wp_create_nonce('rl_reminder_nonce'); ?>'
};
</script>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="rl-reminder-list-container">
        <div class="rl-reminder-form">
            <h2>Add New Reminder</h2>
            <form id="rl-add-reminder-form">
                <div class="form-group">
                    <label for="rl-reminder-title">Title:</label>
                    <input type="text" id="rl-reminder-title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="rl-reminder-description">Description:</label>
                    <textarea id="rl-reminder-description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="rl-reminder-due-date">Due Date:</label>
                    <input type="datetime-local" id="rl-reminder-due-date" name="due_date" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="button button-primary">Add Reminder</button>
                </div>
                
                <div id="rl-form-messages"></div>
            </form>
            </div>
        
        <div class="rl-reminder-list">
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
                                    <button type="button" class="button rl-edit-reminder" data-id="<?php echo esc_attr($reminder->id); ?>">Edit</button>
                                    <button type="button" class="button rl-delete-reminder" data-id="<?php echo esc_attr($reminder->id); ?>">Delete</button>
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

<script>
jQuery(document).ready(function($) {
    console.log('RL Main script loaded');
    
    // Add AJAX form submission code here
    $('#rl-add-reminder-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');
        
        var formData = $(this).serialize();
        formData += '&action=rl_add_reminder&nonce=' + rl_ajax.nonce;
        
        console.log('Sending data:', formData);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#rl-add-reminder-form button[type="submit"]').prop('disabled', true).text('Adding...');
                $('#rl-form-messages').html('<div class="notice notice-info"><p>Adding reminder...</p></div>');
            },
            success: function(response) {
                console.log('Response received:', response);
                
                if (response.success) {
                    $('#rl-form-messages').html('<div class="notice notice-success"><p>Reminder added successfully!</p></div>');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $('#rl-form-messages').html('<div class="notice notice-error"><p>Error: ' + (response.data || 'Failed to add reminder') + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                $('#rl-form-messages').html('<div class="notice notice-error"><p>Server error occurred. Please try again.</p></div>');
            },
            complete: function() {
                $('#rl-add-reminder-form button[type="submit"]').prop('disabled', false).text('Add Reminder');
            }
        });
    });
    
    // Handle edit button click
    $('.rl-edit-reminder').on('click', function() {
        var id = $(this).data('id');
        console.log('Edit reminder:', id);
        
        // Implementasi untuk edit reminder (bisa ditambahkan nanti)
        alert('Edit functionality will be implemented soon.');
    });
    
    // Handle delete button click
    $('.rl-delete-reminder').on('click', function() {
        var id = $(this).data('id');
        var $row = $(this).closest('tr');
        
        if (confirm('Are you sure you want to delete this reminder?')) {
            console.log('Delete reminder:', id);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rl_delete_reminder',
                    id: id,
                    nonce: rl_ajax.nonce
                },
                beforeSend: function() {
                    $row.addClass('updating');
                },
                success: function(response) {
                    console.log('Delete response:', response);
                    
                    if (response.success) {
                        $row.fadeOut(400, function() {
                            $(this).remove();
                            
                            // Jika tidak ada reminder lagi, tampilkan pesan
                            if ($('table tbody tr').length === 0) {
                                $('table tbody').html('<tr><td colspan="5">No reminders found.</td></tr>');
                            }
                        });
                    } else {
                        alert('Error: ' + (response.data || 'Failed to delete reminder'));
                        $row.removeClass('updating');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete Error:', error);
                    alert('Server error occurred. Please try again.');
                    $row.removeClass('updating');
                }
            });
        }
    });
});
</script>

<!-- Tombol test untuk debugging (tersembunyi) -->
<button id="rl-test-ajax" style="display: none;">Test AJAX</button>

<script>
jQuery(document).ready(function($) {
    console.log('RL Debug script loaded');
    console.log('AJAX URL:', ajaxurl);
    console.log('Reminder AJAX object:', rl_ajax);
    
    // Test AJAX connection
    $('#rl-test-ajax').on('click', function() {
        $.post(ajaxurl, {
            action: 'rl_test_ajax',
            nonce: rl_ajax.nonce
        }, function(response) {
            console.log('Test response:', response);
        });
    });
});
</script>
