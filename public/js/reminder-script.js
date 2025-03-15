/**
 * Frontend JavaScript untuk Reminder List
 */
(function($) {
    'use strict';

    // Inisialisasi ketika dokumen siap
    $(document).ready(function() {
        rl_initReminderList();
    });

    /**
     * Inisialisasi fungsi reminder list
     */
    function rl_initReminderList() {
        // Update tampilan waktu relatif
        rl_updateDueDateDisplay();
        
        // Set interval untuk update waktu relatif setiap menit
        setInterval(rl_updateDueDateDisplay, 60000);
        
        // Handle tombol mark complete
        $('.rl-reminder-item .rl-mark-complete').on('click', function() {
            var id = $(this).data('id');
            var $item = $(this).closest('.rl-reminder-item');
            
            rl_updateReminderStatus(id, 'completed', $item);
        });
        
        // Handle tombol mark pending
        $('.rl-reminder-item .rl-mark-pending').on('click', function() {
            var id = $(this).data('id');
            var $item = $(this).closest('.rl-reminder-item');
            
            rl_updateReminderStatus(id, 'pending', $item);
        });
    }
    
    /**
     * Update status reminder via AJAX
     */
    function rl_updateReminderStatus(id, status, $item) {
        $.ajax({
            url: rl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'rl_update_reminder',
                id: id,
                status: status,
                nonce: rl_ajax.nonce
            },
            beforeSend: function() {
                $item.addClass('updating');
            },
            success: function(response) {
                if (response.success) {
                    // Update class dan tampilan
                    $item.removeClass('pending completed').addClass(status);
                    
                    // Update tombol
                    if (status === 'completed') {
                        $item.find('.rl-mark-complete').replaceWith('<button class="rl-mark-pending" data-id="' + id + '">Mark Pending</button>');
                    } else {
                        $item.find('.rl-mark-pending').replaceWith('<button class="rl-mark-complete" data-id="' + id + '">Mark Complete</button>');
                    }
                    
                    // Reinisialisasi event handlers
                    rl_initReminderList();
                    
                    // Tampilkan notifikasi
                    rl_showNotification('Reminder updated successfully!', 'success');
                } else {
                    rl_showNotification('Failed to update reminder', 'error');
                }
            },
            error: function() {
                rl_showNotification('Server error occurred', 'error');
            },
            complete: function() {
                $item.removeClass('updating');
            }
        });
    }
    
    /**
     * Update tampilan waktu relatif
     */
    function rl_updateDueDateDisplay() {
        $('.rl-reminder-item').each(function() {
            var $item = $(this);
            var dueDate = new Date($item.data('due-date'));
            var now = new Date();
            
            // Hitung selisih waktu
            var diff = dueDate - now;
            var diffDays = Math.floor(diff / (1000 * 60 * 60 * 24));
            var diffHours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var diffMinutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            
            var $dueDateRelative = $item.find('.rl-due-date-relative');
            
            // Format waktu relatif
            if (diff < 0) {
                // Sudah lewat
                if (diffDays < -1) {
                    $dueDateRelative.text('(Overdue by ' + Math.abs(diffDays) + ' days)');
                } else if (diffHours < -1) {
                    $dueDateRelative.text('(Overdue by ' + Math.abs(diffHours) + ' hours)');
                } else if (diffMinutes < -1) {
                    $dueDateRelative.text('(Overdue by ' + Math.abs(diffMinutes) + ' minutes)');
                } else {
                    $dueDateRelative.text('(Overdue just now)');
                }
                
                // Tambahkan class overdue
                if (!$item.hasClass('overdue') && !$item.hasClass('completed')) {
                    $item.addClass('overdue');
                }
            } else {
                // Belum lewat
                if (diffDays > 1) {
                    $dueDateRelative.text('(Due in ' + diffDays + ' days)');
                } else if (diffDays === 1) {
                    $dueDateRelative.text('(Due tomorrow)');
                } else if (diffHours > 1) {
                    $dueDateRelative.text('(Due in ' + diffHours + ' hours)');
                } else if (diffMinutes > 1) {
                    $dueDateRelative.text('(Due in ' + diffMinutes + ' minutes)');
                } else {
                    $dueDateRelative.text('(Due now)');
                }
            }
        });
    }
    
    /**
     * Tampilkan notifikasi
     */
    function rl_showNotification(message, type) {
        // Buat elemen notifikasi jika belum ada
        if ($('#rl-notification').length === 0) {
            $('body').append('<div id="rl-notification"></div>');
        }
        
        var $notification = $('#rl-notification');
        
        // Set pesan dan tipe
        $notification.text(message).attr('class', type);
        
        // Tampilkan notifikasi
        $notification.fadeIn(300).delay(3000).fadeOut(500);
    }

})(jQuery);
