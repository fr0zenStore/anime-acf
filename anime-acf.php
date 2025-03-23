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

add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
	'key' => 'group_63f8a1a8a1b1d',
	'title' => 'Anime Fields',
	'fields' => array(
		array(
			'key' => 'field_63f8a1a8a1b1e',
			'label' => 'Title Romaji',
			'name' => 'title_romaji',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b1f',
			'label' => 'Title English',
			'name' => 'title_english',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_67e057eea5ebd',
			'label' => 'Genre',
			'name' => 'genre',
			'aria-label' => '',
			'type' => 'taxonomy',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'taxonomy' => 'category',
			'add_term' => 1,
			'save_terms' => 1,
			'load_terms' => 1,
			'return_format' => 'id',
			'field_type' => 'checkbox',
			'bidirectional' => 0,
			'multiple' => 0,
			'allow_null' => 0,
			'bidirectional_target' => array(
			),
		),
		array(
			'key' => 'field_63f8a1a8a1b20',
			'label' => 'Title Native',
			'name' => 'title_native',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b21',
			'label' => 'Description',
			'name' => 'description',
			'aria-label' => '',
			'type' => 'textarea',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'new_lines' => '',
			'maxlength' => '',
			'placeholder' => '',
			'rows' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b22',
			'label' => 'Episodes',
			'name' => 'episodes',
			'aria-label' => '',
			'type' => 'number',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'min' => '',
			'max' => '',
			'step' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b23',
			'label' => 'Status',
			'name' => 'status',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b24',
			'label' => 'Duration',
			'name' => 'duration',
			'aria-label' => '',
			'type' => 'number',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'min' => '',
			'max' => '',
			'step' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b25',
			'label' => 'Average Score',
			'name' => 'average_score',
			'aria-label' => '',
			'type' => 'number',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'min' => '',
			'max' => '',
			'step' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b26',
			'label' => 'Popularity',
			'name' => 'popularity',
			'aria-label' => '',
			'type' => 'number',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'min' => '',
			'max' => '',
			'step' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b27',
			'label' => 'Cover Image',
			'name' => 'cover_image',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b28',
			'label' => 'Banner Image',
			'name' => 'banner_image',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b29',
			'label' => 'Site URL',
			'name' => 'site_url',
			'aria-label' => '',
			'type' => 'url',
			'instructions' => '',
			'required' => false,
			'conditional_logic' => false,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
		),
		array(
			'key' => 'field_63f8a1a8a1b30',
			'label' => 'Actors',
			'name' => 'actors',
			'aria-label' => '',
			'type' => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'layout' => 'table',
			'pagination' => 0,
			'min' => 0,
			'max' => 0,
			'collapsed' => '',
			'button_label' => 'Add Row',
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_63f8a1a8a1b31',
					'label' => 'Actor Name',
					'name' => 'actor_name',
					'aria-label' => '',
					'type' => 'text',
					'instructions' => '',
					'required' => false,
					'conditional_logic' => false,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'maxlength' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'parent_repeater' => 'field_63f8a1a8a1b30',
				),
				array(
					'key' => 'field_63f8a1a8a1b32',
					'label' => 'Actor Image',
					'name' => 'actor_image',
					'aria-label' => '',
					'type' => 'text',
					'instructions' => '',
					'required' => false,
					'conditional_logic' => false,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'maxlength' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'parent_repeater' => 'field_63f8a1a8a1b30',
				),
				array(
					'key' => 'field_63f8a1a8a1b33',
					'label' => 'Character Name',
					'name' => 'character_name',
					'aria-label' => '',
					'type' => 'text',
					'instructions' => '',
					'required' => false,
					'conditional_logic' => false,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'maxlength' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'parent_repeater' => 'field_63f8a1a8a1b30',
				),
				array(
					'key' => 'field_63f8a1a8a1b34',
					'label' => 'Character Image',
					'name' => 'character_image',
					'aria-label' => '',
					'type' => 'text',
					'instructions' => '',
					'required' => false,
					'conditional_logic' => false,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'maxlength' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'parent_repeater' => 'field_63f8a1a8a1b30',
				),
			),
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'post',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => array(
		0 => 'author',
		1 => 'format',
		2 => 'send-trackbacks',
	),
	'active' => true,
	'description' => 'Campi ACF per gli anime',
	'show_in_rest' => 1,
) );
} );
?>
