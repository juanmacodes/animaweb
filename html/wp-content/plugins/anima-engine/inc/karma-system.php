<?php
/**
 * Anima Karma System
 * Handles XP, Ranks, and Gamification logic.
 */

if (!defined('ABSPATH'))
    exit;

class Anima_Karma_System
{

    private static $instance = null;

    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        // Hooks for actions that award Karma
        add_action('anima_nexus_post_published', array($this, 'award_karma_for_post'), 10, 2);
        add_action('anima_minigame_complete', array($this, 'award_karma_for_minigame'), 10, 2);
        add_action('anima_style_duel_vote', array($this, 'award_karma_for_vote'), 10, 1);

        // New Hooks (Level System Expansion)
        add_action('learndash_lesson_completed', array($this, 'award_karma_for_lesson'), 10, 1);
        add_action('comment_post', array($this, 'award_karma_for_comment'), 10, 2);
        add_action('woocommerce_order_status_completed', array($this, 'award_karma_for_purchase'), 10, 1);

        // AJAX for Minigames
        add_action('wp_ajax_anima_minigame_complete', array($this, 'handle_minigame_complete'));
    }

    public function handle_minigame_complete()
    {
        check_ajax_referer('anima_minigame_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
        $game = isset($_POST['game']) ? sanitize_text_field($_POST['game']) : 'unknown';
        $user_id = get_current_user_id();

        // Trigger the action (which awards Karma)
        do_action('anima_minigame_complete', $user_id, $score);

        $new_xp = $this->get_user_rank($user_id); // Actually returns rank name, but let's return total XP for UI
        $total_xp = (int) get_user_meta($user_id, 'anima_karma_xp', true);

        wp_send_json_success(array('xp' => $total_xp, 'rank' => $new_xp));
    }

    /**
     * Add Karma (XP) to a user
     */
    public function add_karma($user_id, $amount, $reason = '')
    {
        if (!$user_id)
            return;

        $current_xp = (int) get_user_meta($user_id, 'anima_karma_xp', true);
        $new_xp = $current_xp + $amount;

        update_user_meta($user_id, 'anima_karma_xp', $new_xp);

        // Check for Rank Up
        $this->check_rank_up($user_id, $new_xp);

        // Optional: Log history (could be a custom table, simplified here)
        // error_log("User $user_id gained $amount XP for: $reason");
    }

    /**
     * Get User Rank based on XP
     */
    public function get_user_rank($user_id)
    {
        $xp = (int) get_user_meta($user_id, 'anima_karma_xp', true);
        return $this->calculate_rank($xp);
    }

    /**
     * Get Next Rank Info for Progress Bar
     */
    public function get_next_rank_info($user_id)
    {
        $xp = (int) get_user_meta($user_id, 'anima_karma_xp', true);
        $ranks = array(
            0 => 'Novato',
            100 => 'Iniciado',
            500 => 'Hacker',
            1000 => 'Cyber-Agent',
            2500 => 'Netrunner',
            5000 => 'Neon Samurai',
            10000 => 'Cyberlord'
        );

        $current_rank = 'Novato';
        $next_rank = 'Max Level';
        $xp_needed = 0;
        $prev_threshold = 0;

        foreach ($ranks as $threshold => $rank_name) {
            if ($xp >= $threshold) {
                $current_rank = $rank_name;
                $prev_threshold = $threshold;
            } else {
                $next_rank = $rank_name;
                $xp_needed = $threshold;
                break;
            }
        }

        if ($xp_needed === 0) {
            return array(
                'current_rank' => $current_rank,
                'next_rank' => 'Max Level',
                'xp_needed' => 0,
                'progress_percent' => 100
            );
        }

        $total_range = $xp_needed - $prev_threshold;
        $current_progress = $xp - $prev_threshold;
        $percent = ($current_progress / $total_range) * 100;

        return array(
            'current_rank' => $current_rank,
            'next_rank' => $next_rank,
            'xp_needed' => $xp_needed,
            'progress_percent' => round($percent)
        );
    }

    /**
     * Calculate Rank from XP
     */
    private function calculate_rank($xp)
    {
        $ranks = array(
            0 => 'Novato',
            100 => 'Iniciado',
            500 => 'Hacker',
            1000 => 'Cyber-Agent',
            2500 => 'Netrunner',
            5000 => 'Neon Samurai',
            10000 => 'Cyberlord'
        );

        $current_rank = 'Novato';
        foreach ($ranks as $threshold => $rank_name) {
            if ($xp >= $threshold) {
                $current_rank = $rank_name;
            } else {
                break;
            }
        }
        return $current_rank;
    }

    /**
     * Check and process Rank Up
     */
    private function check_rank_up($user_id, $new_xp)
    {
        $old_xp = (int) get_user_meta($user_id, 'anima_karma_xp', true); // This might be stale if called after update, but logic holds if we check thresholds
        // Simplified: Just update the rank meta always to be safe
        $new_rank = $this->calculate_rank($new_xp);
        update_user_meta($user_id, 'anima_karma_rank', $new_rank);
    }

    // --- Event Handlers ---

    public function award_karma_for_post($post_id, $user_id)
    {
        $this->add_karma($user_id, 10, 'Nexus Post Published');
    }

    public function award_karma_for_minigame($user_id, $score)
    {
        // Cap max XP per game to avoid farming
        $xp = min(50, ceil($score / 10));
        $this->add_karma($user_id, $xp, 'Minigame Complete');
    }

    public function award_karma_for_vote($user_id)
    {
        $this->add_karma($user_id, 5, 'Style Duel Vote');
    }

    public function award_karma_for_lesson($data)
    {
        $user_id = get_current_user_id();
        $this->add_karma($user_id, 50, 'Lesson Completed');
    }

    public function award_karma_for_comment($comment_id, $comment_approved)
    {
        if ($comment_approved === 1) {
            $comment = get_comment($comment_id);
            $this->add_karma($comment->user_id, 5, 'Comment Posted');
        }
    }

    public function award_karma_for_purchase($order_id)
    {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        $total = $order->get_total();
        $xp = floor($total * 10); // 10 XP per currency unit
        $this->add_karma($user_id, $xp, 'Purchase Completed');
    }
}

// Initialize
Anima_Karma_System::get_instance();

// Global Helper
function anima_get_user_xp($user_id = null)
{
    if (!$user_id)
        $user_id = get_current_user_id();
    return (int) get_user_meta($user_id, 'anima_karma_xp', true);
}

function anima_get_user_rank_label($user_id = null)
{
    if (!$user_id)
        $user_id = get_current_user_id();
    return Anima_Karma_System::get_instance()->get_user_rank($user_id);
}
