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
