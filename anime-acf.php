<?php
/*
Plugin Name: Anime ACF
Description: Automatically fills ACF fields using an anime ID from AniList API.
Version: 1.0
Author: fr0zen
Author URI: https://fr0zen.store
License: MIT
*/

// Enqueue JavaScript for the admin area
add_action('admin_enqueue_scripts', function () {
    // Enqueue the JavaScript file with a random version for cache prevention
    wp_enqueue_script(
        'anilist-acf-filler-js',
        plugin_dir_url(__FILE__) . 'js/filler.js',
        array('jquery'),
        rand(1, 1000), // Prevent cache during testing
        true // Load the script in the footer
    );

    // Localize ajaxurl for WordPress AJAX calls
    wp_localize_script('anilist-acf-filler-js', 'ajax_object', array(
        'ajaxurl' => admin_url('admin-ajax.php'), // Admin AJAX handler
    ));
});

// Add the AniList ID input field and button to the post editor
add_action('edit_form_after_title', function ($post) {
    // Only add this field for the "post" post type
    if ($post->post_type !== 'post') {
        return;
    }
    ?>
    <div style="margin-bottom: 20px; margin-top: 10px;">
        <input type="text" id="anime_id" placeholder="Anilist ID" />
        <button class="button button-secondary" type="button" id="fill_fields">Scrape</button>
    </div>
    <?php
});

// Create anime genres as categories upon plugin activation
function create_anime_genres()
{
    // Define anime genres to be created as WordPress categories
    $genres = array(
        'Action' => '5',
        'Adventure' => '6',
        'Comedy' => '7',
        'Drama' => '4',
        'Ecchi' => '8',
        'Fantasy' => '9',
        'Horror' => '10',
        'Mahou Shoujo' => '11',
        'Mecha' => '12',
        'Music' => '13',
        'Mystery' => '14',
        'Psychological' => '15',
        'Romance' => '16',
        'Sci-Fi' => '17',
        'Slice of Life' => '18',
        'Sports' => '19',
        'Supernatural' => '20',
        'Thriller' => '21',
    );

    // Iterate through genres and create categories if they don't exist
    foreach ($genres as $genre_name => $category_id) {
        if (!term_exists($genre_name, 'category')) {
            wp_insert_term(
                $genre_name, // Category name
                'category',  // Taxonomy
                array(
                    'slug' => sanitize_title($genre_name), // Generate slug from genre name
                )
            );
        }
    }
}

// Hook to create genres upon plugin activation
register_activation_hook(__FILE__, 'create_anime_genres');

// Handle AJAX request to set featured image for a post
add_action('wp_ajax_set_featured_image', 'set_featured_image_callback');
function set_featured_image_callback()
{
    // Ensure the user has the required permissions
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Insufficient permissions.');
        return;
    }

    // Retrieve data sent via AJAX
    $cover_image_url = $_POST['cover_image'] ?? '';
    $post_id = $_POST['post_id'] ?? 0;

    if (!$cover_image_url || !$post_id) {
        wp_send_json_error('Missing or invalid data.');
        return;
    }

    $cover_image_url = esc_url_raw($cover_image_url);
    $post_id = intval($post_id);

    // Download the image from the URL and add it to the WordPress media library
    $media_id = media_sideload_image($cover_image_url, $post_id, null, 'id');

    if (is_wp_error($media_id)) {
        wp_send_json_error('Error downloading image: ' . $media_id->get_error_message());
        return;
    }

    // Set the downloaded image as the featured image for the post
    $set_thumbnail = set_post_thumbnail($post_id, $media_id);

    if (!$set_thumbnail) {
        wp_send_json_error('Error setting featured image.');
        return;
    }

    // Return the media ID to update the post editor frame
    wp_send_json_success(array(
        'message' => 'Featured image set successfully.',
        'media_id' => $media_id,
    ));
}
?>
