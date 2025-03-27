<?php
/**
 * Plugin Name: PHP File Scanner
 * Description: Displays the contents of one or more PHP files (configured via a custom post type) within code viewing containers.
 * Version: 1.4
 * Author: KISS Plugins
 * License: GPL v2
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register the custom post type for PHP File Scanner entries.
 */
function pfs_register_post_type() {
    $labels = array(
        'name'               => __( 'PHP File Scanners', 'php-file-scanner' ),
        'singular_name'      => __( 'PHP File Scanner', 'php-file-scanner' ),
        'menu_name'          => __( 'PHP File Scanner', 'php-file-scanner' ),
        'name_admin_bar'     => __( 'PHP File Scanner', 'php-file-scanner' ),
        'add_new'            => __( 'Add New', 'php-file-scanner' ),
        'add_new_item'       => __( 'Add New File Scanner Entry', 'php-file-scanner' ),
        'new_item'           => __( 'New File Scanner Entry', 'php-file-scanner' ),
        'edit_item'          => __( 'Edit File Scanner Entry', 'php-file-scanner' ),
        'view_item'          => __( 'View File Scanner Entry', 'php-file-scanner' ),
        'all_items'          => __( 'All File Scanner Entries', 'php-file-scanner' ),
        'search_items'       => __( 'Search File Scanner', 'php-file-scanner' ),
        'not_found'          => __( 'No file scanner entries found.', 'php-file-scanner' ),
        'not_found_in_trash' => __( 'No file scanner entries found in Trash.', 'php-file-scanner' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => current_user_can('manage_options'),
        'show_in_menu'       => current_user_can('manage_options'),
        'capabilities'       => array(
            'create_posts'           => 'manage_options',
            'edit_post'              => 'manage_options',
            'read_post'              => 'manage_options',
            'delete_post'            => 'manage_options',
            'edit_posts'             => 'manage_options',
            'edit_others_posts'      => 'manage_options',
            'publish_posts'          => 'manage_options',
            'read_private_posts'     => 'manage_options',
            'delete_posts'           => 'manage_options',
            'delete_private_posts'   => 'manage_options',
            'delete_others_posts'    => 'manage_options',
            'create_private_posts'   => 'manage_options',
        ),
        'map_meta_cap'       => true, // Enable meta capability mapping
        'supports'           => array( 'title' ),
        'menu_icon'          => 'dashicons-media-code',
    );

    register_post_type( 'php_file_scanner', $args );
}
add_action( 'init', 'pfs_register_post_type' );

/**
 * Add a meta box for entering multiple file paths.
 */
function pfs_add_meta_boxes() {
    add_meta_box(
        'pfs_file_paths_box',
        __( 'File Paths', 'php-file-scanner' ),
        'pfs_file_paths_meta_box_callback',
        'php_file_scanner',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'pfs_add_meta_boxes' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post The post object.
 */
function pfs_file_paths_meta_box_callback( $post ) {
    // Add nonce for security.
    wp_nonce_field( 'pfs_save_meta_box_data', 'pfs_meta_box_nonce' );

    // Retrieve current meta value.
    $file_paths = get_post_meta( $post->ID, 'pfs_file_paths', true );
    if ( ! is_array( $file_paths ) ) {
        $file_paths = array();
    }
    // Convert array to a newline-separated string.
    $file_paths_text = implode( "\n", $file_paths );
    ?>
    <p>
        <label for="pfs_file_paths"><?php _e( 'Enter one file path per line (relative to the WordPress root directory).', 'php-file-scanner' ); ?></label>
    </p>
    <textarea name="pfs_file_paths" id="pfs_file_paths" rows="5" style="width:100%;"><?php echo esc_textarea( $file_paths_text ); ?></textarea>
    <?php
}

/**
 * Save the meta box data when the post is saved.
 *
 * @param int $post_id The post ID.
 */
function pfs_save_post( $post_id ) {
    // Check if our nonce is set.
    if ( ! isset( $_POST['pfs_meta_box_nonce'] ) ) {
        return;
    }
    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['pfs_meta_box_nonce'], 'pfs_save_meta_box_data' ) ) {
        return;
    }
    // Check for autosave.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'php_file_scanner' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    if ( isset( $_POST['pfs_file_paths'] ) ) {
        $file_paths_raw = sanitize_textarea_field( $_POST['pfs_file_paths'] );
        // Split the input by new lines, trim whitespace and remove empty lines.
        $paths_array = array_filter( array_map( 'trim', explode( "\n", $file_paths_raw ) ) );
        update_post_meta( $post_id, 'pfs_file_paths', $paths_array );
    }
}
add_action( 'save_post', 'pfs_save_post' );

/**
 * Shortcode to display the PHP file contents based on a custom post ID.
 *
 * Usage: [file_scanner id="123"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output of file contents.
 */
function pfs_display_scanned_file_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'id' => ''
    ), $atts, 'file_scanner' );

    $post_id = intval( $atts['id'] );
    if ( ! $post_id ) {
         return __( 'Invalid post ID.', 'php-file-scanner' );
    }

    $post = get_post( $post_id );
    if ( ! $post || 'php_file_scanner' !== $post->post_type ) {
         return __( 'Post not found or invalid post type.', 'php-file-scanner' );
    }

    // Get file paths meta.
    $file_paths = get_post_meta( $post_id, 'pfs_file_paths', true );
    if ( empty( $file_paths ) || ! is_array( $file_paths ) ) {
         return __( 'No file paths specified for this post.', 'php-file-scanner' );
    }

    $output = '';
    foreach ( $file_paths as $path ) {
         // Prepend ABSPATH if not already included.
         if ( strpos( $path, ABSPATH ) !== 0 ) {
             $file_path = ABSPATH . ltrim( $path, '/' );
         } else {
             $file_path = $path;
         }

         if ( ! file_exists( $file_path ) ) {
             $output .= '<p>' . sprintf( __( 'File does not exist at the specified path: %s', 'php-file-scanner' ), esc_html( $file_path ) ) . '</p>';
             continue;
         }

         $content = file_get_contents( $file_path );
         $output .= '<div class="pfs-code-container" style="background: #f5f5f5; padding: 10px; border: 1px solid #ccc; overflow-x: auto; margin-bottom: 20px;">';
         $output .= '<pre><code>' . esc_html( $content ) . '</code></pre>';
         $output .= '</div>';
    }

    return $output;
}
add_shortcode( 'file_scanner', 'pfs_display_scanned_file_shortcode' );

/**
 * Add a settings link to the Plugins page. The link directs to the "Add New" screen
 * for the PHP File Scanner custom post type.
 */
function pfs_add_settings_link( $links ) {
    $settings_link = '<a href="post-new.php?post_type=php_file_scanner">' . __( 'Add New', 'php-file-scanner' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pfs_add_settings_link' );
