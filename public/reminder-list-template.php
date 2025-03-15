<?php
/**
 * Template untuk menampilkan reminder list di frontend
 */
?>
<div class="rl-reminder-list-public">
    <h3>My Reminders</h3>
    
    <ul class="rl-reminder-items">
        <?php
        $count = 0;
        foreach ($reminders as $reminder) {
            // Skip jika sudah mencapai limit
            if ($count >= $limit) {
                break;
            }
            
            // Skip reminder yang completed jika show_completed = false
            if (!$show_completed && $reminder->status === 'completed') {
                continue;
            }
            
            $count++;
            
            // Hitung selisih waktu untuk tampilan relatif
            $due_date = new DateTime($reminder->due_date);
            $now = new DateTime();
            $is_overdue = ($due_date < $now && $reminder->status !== 'completed');
            
            // Tentukan class CSS berdasarkan status
            $item_class = $reminder->status;
            if ($is_overdue) {
                $item_class .= ' overdue';
            }
            ?>
            <li class="rl-reminder-item <?php echo esc_attr($item_class); ?>" 
                data-id="<?php echo esc_attr($reminder->id); ?>"
                data-due-date="<?php echo esc_attr($reminder->due_date); ?>">
                
                <h4><?php echo esc_html($reminder->title); ?></h4>
                <?php if (!empty($reminder->description)): ?>
                    <p class="rl-reminder-description"><?php echo esc_html($reminder->description); ?></p>
                <?php endif; ?>
                
                <div class="rl-reminder-meta">
                    <span class="rl-due-date">Due: <?php echo esc_html(date('F j, Y, g:i a', strtotime($reminder->due_date))); ?></span>
                    <span class="rl-due-date-relative"></span>
                    
                    <?php if (is_user_logged_in() && current_user_can('edit_posts')): ?>
                        <div class="rl-reminder-actions">
                            <?php if ($reminder->status !== 'completed'): ?>
                                <button class="rl-mark-complete" data-id="<?php echo esc_attr($reminder->id); ?>">Mark Complete</button>
                            <?php else: ?>
                                <button class="rl-mark-pending" data-id="<?php echo esc_attr($reminder->id); ?>">Mark Pending</button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
            <?php
        }
        
        if ($count === 0) {
            echo '<li class="rl-no-reminders">No reminders found.</li>';
        }
        ?>
    </ul>
</div>
