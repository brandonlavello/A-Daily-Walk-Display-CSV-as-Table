<?php
// Exit if uninstall not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

// Remove old option (if it still exists)
delete_option('adw_csv_id');

// Delete all radio_station posts
$radio_posts = get_posts([
    'post_type' => 'radio_station',
    'numberposts' => -1,
    'post_status' => 'any',
]);

foreach ($radio_posts as $post) {
    wp_delete_post($post->ID, true);
}
