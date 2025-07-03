<?php
/**
 * Plugin Name: Display CSV ADW Radio Stations
 * Plugin URI: https://brandonlavello.com
 * Description: Display csv radio station content using a shortcode and admin UI
 * Version: 2.7
 * Author: Brandon Lavello
 * License: GNU GPLv3
 */

// --- Register Custom Post Type ---
add_action('init', 'adw_register_radio_station_cpt');
function adw_register_radio_station_cpt() {
    register_post_type('radio_station', [
        'labels' => [
            'name' => 'Radio Stations',
            'singular_name' => 'Radio Station',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Radio Station',
            'edit_item' => 'Edit Radio Station',
            'new_item' => 'New Radio Station',
            'view_item' => 'View Radio Station',
            'search_items' => 'Search Radio Stations',
            'not_found' => 'No radio stations found',
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-microphone',
        'supports' => ['title'],
    ]);
}

// --- Meta Box ---
add_action('add_meta_boxes', 'adw_add_radio_station_meta_box');
add_action('save_post', 'adw_save_radio_station_meta');

function adw_add_radio_station_meta_box() {
    add_meta_box(
        'adw_radio_station_meta',
        'Station Details',
        'adw_render_radio_station_meta_box',
        'radio_station',
        'normal',
        'high'
    );
}

function adw_render_radio_station_meta_box($post) {
    $fields = ['country', 'state', 'city', 'station_name', 'frequency'];
    foreach ($fields as $field) {
        $value = get_post_meta($post->ID, $field, true);
        echo "<p><label for='$field'>" . ucfirst(str_replace('_', ' ', $field)) . ":</label><br />";
        echo "<input type='text' name='$field' id='$field' value='" . esc_attr($value) . "' style='width:100%' /></p>";
    }
}

function adw_save_radio_station_meta($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    $fields = ['country', 'state', 'city', 'station_name', 'frequency'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}

// --- Admin Columns ---
add_filter('manage_radio_station_posts_columns', 'adw_add_admin_columns');
function adw_add_admin_columns($columns) {
    $columns['state'] = 'State';
    $columns['country'] = 'Country';
    $columns['frequency'] = 'Frequency';
    return $columns;
}

add_action('manage_radio_station_posts_custom_column', 'adw_render_admin_columns', 10, 2);
function adw_render_admin_columns($column, $post_id) {
    if (in_array($column, ['state', 'country', 'frequency'])) {
        echo esc_html(get_post_meta($post_id, $column, true));
    }
}

// --- Admin Filters ---
add_action('restrict_manage_posts', 'adw_filter_radio_station_admin');
function adw_filter_radio_station_admin(){
    global $typenow;
    if ($typenow !== 'radio_station') return;

    foreach (['country', 'state'] as $key) {
        $selected = $_GET[$key] ?? '';
        $values = adw_get_unique_meta_values($key);

        echo "<select name='$key'><option value=''>All " . ucfirst($key) . "s</option>";
        foreach ($values as $val) {
            $val_esc = esc_attr($val);
            $selected_attr = selected($selected, $val, false);
            echo "<option value='$val_esc' $selected_attr>$val</option>";
        }
        echo "</select>";
    }
}

add_filter('parse_query', 'adw_filter_radio_station_query');
function adw_filter_radio_station_query($query){
    global $pagenow;
    if (is_admin() && $pagenow === 'edit.php' && $query->query['post_type'] === 'radio_station') {
        foreach (['country', 'state'] as $key) {
            if (!empty($_GET[$key])) {
                $query->query_vars['meta_query'][] = [
                    'key' => $key,
                    'value' => sanitize_text_field($_GET[$key]),
                    'compare' => '='
                ];
            }
        }
    }
}

function adw_get_unique_meta_values($meta_key) {
    global $wpdb;
    $results = $wpdb->get_col( $wpdb->prepare("
        SELECT DISTINCT meta_value
        FROM $wpdb->postmeta
        WHERE meta_key = %s
        ORDER BY meta_value ASC
    ", $meta_key) );
    return array_filter($results);
}

// --- CSV Upload Page ---
add_action('admin_menu', 'adw_plugin_setup_menu');
function adw_plugin_setup_menu(){
    add_menu_page('Upload Radio Stations CSV', 'Upload Station CSV', 'manage_options', 'adw-upload-csv', 'adw_csv_upload_page');
}

function adw_csv_upload_page(){
    adw_handle_csv_upload();
    adw_handle_delete_all();
    echo '<h1>Upload a CSV of Radio Stations</h1>';
    echo '<p>This tool allows you to import a list of radio stations from a CSV file. The file must follow this format with 5 columns: <strong>Country, State, City, Frequency, Station</strong>.</p>';
    echo '<p>Each row represents one radio station. Uploading a new CSV will add those stations to the list. Existing stations will not be overwritten or removed unless deleted.</p>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="csv_upload">';
    submit_button('Upload');
    echo '</form>';
    echo '<hr><h2>Danger Zone</h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="delete_all_stations" value="1">';
    submit_button('Delete All Radio Stations', 'delete');
    echo '</form>';
}

function adw_handle_csv_upload(){
    if (!empty($_FILES['csv_upload']['tmp_name'])) {
        $file = $_FILES['csv_upload']['tmp_name'];
        adw_import_csv_to_posts($file);
        echo '<div class="notice notice-success is-dismissible"><p>CSV Imported!</p></div>';
    }
}

function adw_handle_delete_all(){
    if (isset($_POST['delete_all_stations']) && current_user_can('manage_options')) {
        $posts = get_posts(['post_type' => 'radio_station', 'numberposts' => -1]);
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
        echo '<div class="notice notice-warning is-dismissible"><p>All radio stations deleted.</p></div>';
    }
}

function adw_import_csv_to_posts($csv_path) {
    if (!file_exists($csv_path)) return;
    $f = fopen($csv_path, 'r');
    while (($line = fgetcsv($f)) !== false) {
        if (count($line) < 5) continue;
        wp_insert_post([
            'post_type' => 'radio_station',
            'post_title' => $line[2],
            'post_status' => 'publish',
            'meta_input' => [
                'country'       => $line[0],
                'state'         => $line[1],
                'city'          => $line[2],
                'frequency'     => $line[3],
                'station_name'  => $line[4]
            ]
        ]);
    }
    fclose($f);
}

// --- Shortcode ---
add_shortcode('adw_csv', 'render_adw_csv');
function render_adw_csv(){
    $output = '';
    $args = ['post_type' => 'radio_station', 'posts_per_page' => -1];
    $stations = get_posts($args);
    $grouped = [];

    foreach ($stations as $station) {
        $country = get_post_meta($station->ID, 'country', true);
        $state = get_post_meta($station->ID, 'state', true);
        $city = get_post_meta($station->ID, 'city', true);
        if (!$city) $city = $station->post_title;
        $frequency = get_post_meta($station->ID, 'frequency', true);
        $station_name = get_post_meta($station->ID, 'station_name', true);

        $grouped[$country][$state][] = [
            'city' => $city,
            'frequency' => $frequency,
            'station' => $station_name
        ];
    }

    uksort($grouped, function($a, $b) {
        if ($a === 'United States Of America') return -1;
        if ($b === 'United States Of America') return 1;
        return strcmp($a, $b);
    });

    foreach ($grouped as $country => &$states) {
        ksort($states);
        foreach ($states as &$stations) {
            usort($stations, fn($a, $b) => strcmp($a['city'], $b['city']));
        }
    }

    foreach ($grouped as $country => $states) {
        $output .= "<h2>$country</h2>";
        foreach ($states as $state => $station_rows) {
            $output .= "<h3>$state</h3><figure class=\"wp-block-table\">
                        <table style=\"width: 100%\">
                        <colgroup><col width='50%'><col width='25%'><col width='25%'></colgroup>
                        <thead><tr><th>City</th><th>Frequency</th><th>Station</th></tr></thead>
                        <tbody>";
            foreach ($station_rows as $row) {
                $output .= "<tr><td>{$row['city']}</td><td>{$row['frequency']}</td><td>{$row['station']}</td></tr>";
            }
            $output .= "</tbody></table></figure>";
        }
    }

    return $output;
}
