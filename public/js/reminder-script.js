/**
 * Front-end JavaScript for Reminder List plugin
 */
(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initReminderList();
    });

    /**
     * Initialize the reminder list functionality
     */
    function initReminderList() {
        // Handle marking reminders as complete
        $('.reminder-item .mark-complete').on('click', function(e) {
            e.preventDefault();
            
            var $item = $(this).closest('.reminder-item');
            var reminderId = $item.data('id');
            
            updateReminderStatus(reminderId, 'completed', $item);
        });
        
        // Handle marking reminders as pending
        $('.reminder-item .mark-pending').on('click', function(e) {
            e.preventDefault();
            
            var $item = $(this).closest('.reminder-item');
            var reminderId = $item.data('id');
            
            updateReminderStatus(reminderId, 'pending', $item);
        });
        
        // Initialize any date-related displays
        updateDueDateDisplay();
        
        // Set up periodic refresh for time-sensitive elements
        setInterval(updateDueDateDisplay, 60000); // Update every minute
    }
    
    /**
     * Update the status of a reminder via AJAX
     */
    function updateReminderStatus(reminderId, status, $item) {
        $.ajax({
            url: reminder_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'update_reminder',
                id: reminderId,
                status: status,
                nonce: reminder_ajax.nonce
            },
            beforeSend: function() {
                $item.addClass('updating');
            },
            success: function(response) {
                if (response.success) {
                    $item.removeClass('pending completed').addClass(status);
                    
                    // Update button visibility
                    if (status === 'completed') {
                        $item.find('.mark-complete').hide();
                        $item.find('.mark-pending').show();
                    } else {
                        $item.find('.mark-complete').show();
                        $item.find('.mark-pending').hide();
                    }
                    
                    // Optional: Show success message
                    showNotification('Reminder updated successfully!', 'success');
                } else {
                    showNotification('Failed to update reminder: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotification('Server error occurred. Please try again.', 'error');
            },
            complete: function() {
                $item.removeClass('updating');
            }
        });
    }
    
    /**
     * Update the display of due dates (e.g., "2 hours ago", "in 3 days")
     */
    function updateDueDateDisplay() {
        $('.reminder-item').each(function() {
            var $item = $(this);
            var dueDate = new Date($item.data('due-date'));
            var now = new Date();
            
            // Skip if no due date
            if (!$item.data('due-date')) {
                return;
            }
            
            // Calculate time difference
            var diff = dueDate - now;
            var diffDays = Math.floor(diff / (1000 * 60 * 60 * 24));
            var diffHours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var diffMinutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            
            var $dueDateDisplay = $item.find('.due-date-relative');
            
            // Format the relative time
            if (diff < 0) {
                // Past due
                if (diffDays < -1) {
                    $dueDateDisplay.text('Overdue by ' + Math.abs(diffDays) + ' days');
                } else if (diffHours < -1) {
                    $dueDateDisplay.text('Overdue by ' + Math.abs(diffHours) + ' hours');
                } else {
                    $dueDateDisplay.text('Overdue by ' + Math.abs(diffMinutes) + ' minutes');
                }
                
                // Add overdue class if not already present
                if (!$item.hasClass('overdue')) {
                    $item.addClass('overdue');
                }
            } else {
                // Upcoming
                if (diffDays > 1) {
                    $dueDateDisplay.text('Due in ' + diffDays + ' days');
                } else if (diffHours > 1) {
                    $dueDateDisplay.text('Due in ' + diffHours + ' hours');
                } else if (diffMinutes > 1) {
                    $dueDateDisplay.text('Due in ' + diffMinutes + ' minutes');
                } else {
                    $dueDateDisplay.text('Due now');
                    
                    // Add due-now class
                    $item.addClass('due-now');
                }
            }
        });
    }
    
    /**
     * Display a notification message
     */
    function showNotification(message, type) {
        // Create notification element if it doesn't exist
        if ($('#reminder-notification').length === 0) {
            $('body').append('<div id="reminder-notification"></div>');
        }
        
        var $notification = $('#reminder-notification');
        
        // Set message and type
        $notification.text(message).attr('class', type);
        
        // Show notification
        $notification.fadeIn(300).delay(3000).fadeOut(500);
    }

})(jQuery);
