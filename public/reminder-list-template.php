<?php
$limit = intval($atts['limit']);
$show_completed = ($atts['show_completed'] === 'yes');

$reminders = $this->db->get_reminders();
?>

<div class="reminder-list-public">
    <h3>My Reminders</h3>
    
    <ul class="reminder-items">
        <?php
        $count = 0;
        foreach ($reminders as $reminder) {
            if ($count >= $limit) {
                break;
            }
            
            if (!$show_completed && $reminder->status === 'completed') {
                continue;
            }
            
            $count++;
            ?>
            <li class="reminder-item <?php echo esc_attr($reminder->status); ?>" 
                data-id="<?php echo esc_attr($reminder->id); ?>"
                data-due-date="<?php echo esc_attr($reminder->due_date); ?>">
                
                <h4><?php echo esc_html($reminder->title); ?></h4>
                <p><?php echo esc_html($reminder->description); ?></p>
                
                <div class="reminder-meta">
                    <span class="due-date">Due: <?php echo esc_html(date('F j, Y, g:i a', strtotime($reminder->due_date))); ?></span>
                    <span class="due-date-relative"></span>
                    
                    <?php if (current_user_can('edit_posts')): ?>
                        <div class="reminder-actions">
                            <?php if ($reminder->status !== 'completed'): ?>
                                <button class="mark-complete">Mark Complete</button>
                            <?php else: ?>
                                <button class="mark-pending">Mark Pending</button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
            <?php
        }
        
        if ($count === 0) {
            echo '<li>No reminders found.</li>';
        }
        ?>
    </ul>
</div>
