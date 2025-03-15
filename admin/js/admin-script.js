jQuery(document).ready(function($) {
    console.log('Main script loaded');
    
    // Add AJAX form submission code here
    $('#add-reminder-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');
        
        var formData = $(this).serialize();
        formData += '&action=add_reminder&nonce=' + reminder_ajax.nonce;
        
        console.log('Sending data:', formData);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#add-reminder-form button[type="submit"]').prop('disabled', true).text('Adding...');
                $('#form-messages').html('<div class="notice notice-info"><p>Adding reminder...</p></div>');
            },
            success: function(response) {
                console.log('Response received:', response);
                
                if (response.success) {
                    $('#form-messages').html('<div class="notice notice-success"><p>Reminder added successfully!</p></div>');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $('#form-messages').html('<div class="notice notice-error"><p>Error: ' + (response.data || 'Failed to add reminder') + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                $('#form-messages').html('<div class="notice notice-error"><p>Server error occurred. Please try again.</p></div>');
            },
            complete: function() {
                $('#add-reminder-form button[type="submit"]').prop('disabled', false).text('Add Reminder');
            }
        });
    });
    
    // Handle edit button click
    $('.edit-reminder').on('click', function() {
        var id = $(this).data('id');
        console.log('Edit reminder:', id);
        
        // Implementasi untuk edit reminder (bisa ditambahkan nanti)
        alert('Edit functionality will be implemented soon.');
    });
    
    // Handle delete button click
    $('.delete-reminder').on('click', function() {
        var id = $(this).data('id');
        var $row = $(this).closest('tr');
        
        if (confirm('Are you sure you want to delete this reminder?')) {
            console.log('Delete reminder:', id);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_reminder',
                    id: id,
                    nonce: reminder_ajax.nonce
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