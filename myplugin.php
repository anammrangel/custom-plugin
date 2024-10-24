<?php
/*
Plugin Name: Custom Plugin
Description: Adiciona filtros de categorias no painel do WordPress e outras funcionalidades.
Version: 1.1
Author: Ana Maria
*/

//Adds the categories and subcategories filter for the post type 'post'
function custom_category_filter() {
    global $typenow;

    if ($typenow === 'post') {
        $taxonomy = 'category'; 
        $categories = get_categories(array('taxonomy' => $taxonomy));

        echo '<select name="category_filter" id="category_filter">';
        echo '<option value="">' . esc_html__('Filter by Category', 'custom-plugin') . '</option>';

        foreach ($categories as $category) {
            $category_filter = isset($_GET['category_filter']) ? sanitize_text_field($_GET['category_filter']) : '';
            $selected = ($category_filter === $category->slug) ? 'selected' : '';
            echo '<option value="' . esc_attr($category->slug) . '" ' . esc_attr($selected) . '>' . esc_html($category->name) . '</option>';
        }

        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'custom_category_filter');

// Filters the results based on the selected category
function filter_posts_by_category($query) {
    global $pagenow;

    if ($pagenow === 'edit.php' && isset($query->query['post_type']) && $query->query['post_type'] === 'post') {
        $category_filter = isset($_GET['category_filter']) ? sanitize_text_field($_GET['category_filter']) : '';

        if (!empty($category_filter)) {
            $query->query_vars['category_name'] = $category_filter;
        }
    }
}
add_filter('parse_query', 'filter_posts_by_category');

// Adds the subcategory filter for a specific post type
function custom_category_filter_custom_post() {
    global $typenow;

    $post_type = 'custom-post-type';
    $taxonomy = 'custom-taxonomy';
    $parent_category_slug = 'parent-category-slug';

    if ($typenow === $post_type) {
        $parent_category = get_term_by('slug', $parent_category_slug, $taxonomy);

        if ($parent_category) {
            $subcategories = get_terms(array(
                'taxonomy' => $taxonomy,
                'child_of' => $parent_category->term_id,
                'hide_empty' => false,
            ));

            echo '<select name="subcategory_filter" id="subcategory_filter">';
            echo '<option value="">' . esc_html__('Filter by Subcategory', 'custom-plugin') . '</option>';

            foreach ($subcategories as $category) {
                $subcategory_filter = isset($_GET['subcategory_filter']) ? sanitize_text_field($_GET['subcategory_filter']) : '';
                $selected = ($subcategory_filter === $category->slug) ? 'selected' : '';
                echo '<option value="' . esc_attr($category->slug) . '" ' . esc_attr($selected) . '>' . esc_html($category->name) . '</option>';
            }

            echo '</select>';
        }
    }
}
add_action('restrict_manage_posts', 'custom_category_filter_custom_post');

// Filters the results based on the selected subcategory
function filter_posts_by_subcategory_custom_post($query) {
    global $pagenow;

    if ($pagenow === 'edit.php' && isset($query->query['post_type']) && $query->query['post_type'] === 'custom-post-type') {
        $subcategory_filter = isset($_GET['subcategory_filter']) ? sanitize_text_field($_GET['subcategory_filter']) : '';

        if (!empty($subcategory_filter)) {
            $query->query_vars['tax_query'] = array(
                array(
                    'taxonomy' => 'custom-taxonomy',
                    'field'    => 'slug',
                    'terms'    => $subcategory_filter,
                ),
            );
        }
    }
}
add_filter('parse_query', 'filter_posts_by_subcategory_custom_post');

// Add content in the admin panel
function add_custom_admin_notice() {
    echo '<div class="notice notice-info is-dismissible">
            <h2>
                <a style="font-family: Poppins, sans-serif !important; color: #103f67;" href="' . esc_url('https://example.com/manual/') . '" target="_blank">' . esc_html__('Click here to access the User Manual', 'custom-plugin') . '</a>
            </h2>
          </div>';
}
add_action('admin_notices', 'add_custom_admin_notice');

// Adds the featured image column to the admin area
function custom_admin_column_header($columns) {
    $columns['featured_image'] = esc_html__('Featured Image', 'custom-plugin');
    return $columns;
}
add_filter('manage_posts_columns', 'custom_admin_column_header');

// Displays the contents of the featured image column
function custom_admin_column_content($column_name, $post_id) {
    if ($column_name === 'featured_image') {
        $featured_image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');

        if ($featured_image_url) {
            echo '<img src="' . esc_url($featured_image_url) . '" width="50" height="50" />';
        } else {
            echo esc_html__('No Featured Image', 'custom-plugin');
        }
    }
}
add_action('manage_posts_custom_column', 'custom_admin_column_content', 10, 2);

// Restricts access to a specific category
function restrict_category_access() {
    if (is_singular('page') && has_term('restricted-category', 'custom-taxonomy') && !is_user_logged_in()) {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('template_redirect', 'restrict_category_access');

// Function to create posts from an external API
function create_posts_from_api() {
    $api_url = 'https://api.example.com/posts';
    $bearer_token = getenv('API_BEARER_TOKEN'); // Usando variável de ambiente

    $args = [
        'headers' => ['Authorization' => 'Bearer ' . $bearer_token],
        'sslverify' => false,
    ];

    $response = wp_remote_get($api_url, $args);

    if (is_wp_error($response)) {
        add_action('admin_notices', function() use ($response) {
            $error_message = $response->get_error_message();
            echo "<div class='notice notice-error is-dismissible'><p>" . esc_html__('Error fetching API data:', 'custom-plugin') . " $error_message</p></div>";
        });
        return;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (!empty($data)) {
        foreach ($data as $item) {
            $existing_post = get_posts([
                'post_type' => 'api-posts',
                'meta_key' => 'process_number',
                'meta_value' => sanitize_text_field($item['process_number']),
            ]);

            if (!$existing_post) {
                $post_id = wp_insert_post([
                    'post_title'    => 'Process ' . sanitize_text_field($item['process_number']),
                    'post_type'     => 'api-posts',
                    'post_status'   => 'publish',
                    'post_content'  => 'Post type ' . sanitize_text_field($item['type']) . ' judged by ' . sanitize_text_field($item['judge']),
                ]);

                if ($post_id) {
                    $date_initial = DateTime::createFromFormat('d/m/Y H:i', $item['start_date']);
                    $start_date = $date_initial ? $date_initial->getTimestamp() : '';
                    $date_final = DateTime::createFromFormat('d/m/Y H:i', $item['end_date']);
                    $end_date = $date_final ? $date_final->getTimestamp() : '';

                    update_post_meta($post_id, 'start_date', $start_date);
                    update_post_meta($post_id, 'end_date', $end_date);
                    update_post_meta($post_id, 'type', sanitize_text_field($item['type']));
                    update_post_meta($post_id, 'class', sanitize_text_field($item['class']));
                    update_post_meta($post_id, 'judge', sanitize_text_field($item['judge']));
                    update_post_meta($post_id, 'reporter', sanitize_text_field($item['reporter']));
                    update_post_meta($post_id, 'process_number', sanitize_text_field($item['process_number']));
                }
            }
        }
    }
}
add_action('api_event', 'create_posts_from_api');

// Schedule the execution of the function
function schedule_api_check() {
    if (!wp_next_scheduled('api_event')) {
        wp_schedule_event(time(), 'hourly', 'api_event');
    }
}
register_activation_hook(__FILE__, 'schedule_api_check');

// Cancel cron when plugin is deactivated
function unschedule_api_check() {
    $timestamp = wp_next_scheduled('api_event');
    wp_unschedule_event($timestamp, 'api_event');
}
register_deactivation_hook(__FILE__, 'unschedule_api_check');
