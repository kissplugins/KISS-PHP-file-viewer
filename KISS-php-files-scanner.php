<?php
/**
 * Plugin Name: PHP File Scanner
 * Description: Displays the contents of a defined PHP file (configured in WP admin) within a code viewing container.
 * Version: 1.3
 * Author: KISS Plugins
 * License: GPL v2
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Add settings link to the Plugins listing page.
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'pfs_add_settings_link' );
function pfs_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=php-file-scanner">' . __( 'Settings', 'php-file-scanner' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

// Add admin menu and settings.
add_action( 'admin_menu', 'pfs_add_admin_menu' );
add_action( 'admin_init', 'pfs_settings_init' );

function pfs_add_admin_menu() {
    add_menu_page(
        'PHP File Scanner',       // Page title.
        'PHP File Scanner',       // Menu title.
        'manage_options',         // Capability.
        'php-file-scanner',       // Menu slug.
        'pfs_options_page'        // Callback function.
    );
}

function pfs_settings_init() {
    register_setting( 'pfs_options_group', 'pfs_settings', 'pfs_sanitize_settings' );

    add_settings_section(
        'pfs_settings_section',
        __( 'Configure PHP File Path', 'php-file-scanner' ),
        'pfs_settings_section_callback',
        'pfs_options_group'
    );

    add_settings_field(
        'pfs_file_path',
        __( 'PHP File Relative Path', 'php-file-scanner' ),
        'pfs_file_path_render',
        'pfs_options_group',
        'pfs_settings_section'
    );
}

function pfs_sanitize_settings( $input ) {
    $new_input = array();
    if ( isset( $input['pfs_file_path'] ) ) {
        $new_input['pfs_file_path'] = sanitize_text_field( $input['pfs_file_path'] );
    }
    return $new_input;
}

function pfs_file_path_render() {
    $options = get_option( 'pfs_settings' );
    ?>
    <input type="text" name="pfs_settings[pfs_file_path]" value="<?php echo isset( $options['pfs_file_path'] ) ? esc_attr( $options['pfs_file_path'] ) : ''; ?>" size="50">
    <p class="description"><?php _e( 'Enter the file path relative to the WordPress root directory, e.g., <code>wp-content/plugins/php-file-scanner/test.php</code>', 'php-file-scanner' ); ?></p>
    <?php
}

function pfs_settings_section_callback() {
    echo __( 'Enter the relative path to the PHP file you want to display. The plugin will automatically prepend the WordPress root path.', 'php-file-scanner' );
}

function pfs_options_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'PHP File Scanner Settings', 'php-file-scanner' ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'pfs_options_group' );
            do_settings_sections( 'pfs_options_group' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Shortcode to display the PHP file content inside a code container.
add_shortcode( 'file_scanner', 'pfs_display_scanned_file' );

function pfs_display_scanned_file() {
    $options       = get_option( 'pfs_settings' );
    $relative_path = isset( $options['pfs_file_path'] ) ? $options['pfs_file_path'] : '';

    if ( empty( $relative_path ) ) {
        return __( 'No file path is set. Please configure the PHP file path in the admin settings.', 'php-file-scanner' );
    }

    // Prepend the ABSPATH if the provided path doesn't already include it.
    if ( strpos( $relative_path, ABSPATH ) !== 0 ) {
        $file_path = ABSPATH . ltrim( $relative_path, '/' );
    } else {
        $file_path = $relative_path;
    }

    if ( ! file_exists( $file_path ) ) {
        return sprintf( __( 'File does not exist at the specified path: %s', 'php-file-scanner' ), esc_html( $file_path ) );
    }

    // Read the file content.
    $content = file_get_contents( $file_path );

    // Wrap the file content in a styled code container.
    $output  = '<div class="pfs-code-container" style="background: #f5f5f5; padding: 10px; border: 1px solid #ccc; overflow-x: auto;">';
    $output .= '<pre><code>' . esc_html( $content ) . '</code></pre>';
    $output .= '</div>';

    return $output;
}