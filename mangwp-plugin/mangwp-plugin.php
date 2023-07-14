<?php
/*
Plugin Name: Mangwp Plugin
Description: Colection of custom function of mine. Made by love by mangwp.com
Version: 1.2
Author: Ivan Nugraha
Author URI: https://mangwp.com
*/

// Add an action hook to initialize the plugin
add_action('init', 'mangwp_plugin_init');

function mangwp_plugin_init()
{
    // Add the menu page for Bricks Utility
    add_action('admin_menu', 'mangwp_plugin_add_menu');
}

function mangwp_plugin_add_menu()
{
    // Add the top-level menu page
    add_menu_page(
        'Mangwp Plugin',
        'Mangwp Plugin',
        'manage_options',
        'mangwp-plugin',
        'mangwp_plugin_page_callback',
        'dashicons-admin-plugins',

    );

    // Add the sub-page for Bricks Utility
    add_submenu_page(
        'mangwp-plugin',
        'Bricks Utility',
        'Bricks Utility',
        'manage_options',
        'mangwp-plugin-bricks',
        'mangwp_plugin_bricks_page_callback'
    );
    add_submenu_page(
        'mangwp-plugin',
        'Core Framework Extended',
        'Core Framework Extended',
        'manage_options',
        'mangwp-plugin-core-framework',
        'mangwp_plugin_core_framework_page_callback'
    );
}
function mangwp_plugin_activation()
{
    // Create the mangwp folder inside uploads directory
    $upload_dir = wp_upload_dir();
    $mangwp_dir = $upload_dir['basedir'] . '/mangwp';

    if (!is_dir($mangwp_dir)) {
        wp_mkdir_p($mangwp_dir);
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'mangwp_css_variables';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        css_variables TEXT NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'mangwp_plugin_activation');

function mangwp_plugin_page_callback()
{
    // Add your code for the main plugin page here
    echo '<h1>Mangwp Plugin</h1>';
}

function mangwp_plugin_bricks_page_callback()
{
    require_once __DIR__ . '/include/css-to-json.php';
    require_once __DIR__ . '/include/color-pallet-generator.php';

    ?>
    <div class="wrap">
        <h1>Bricks Utility</h1>

        <!-- Add tab navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="#upload-tab" class="nav-tab nav-tab-active">Classnames Generator</a>
            <a href="#color-tab" class="nav-tab">Color Palette Generator</a>
            <a href="#variable-tab" class="nav-tab">CSS Variable Picker</a>
        </h2>

        <!-- Add tab content -->
        <div id="upload-tab" class="tab-content">
            <form method="POST" enctype="multipart/form-data">
                <p>Select a .css file to upload:</p>
                <input type="file" name="bricks_file" accept=".css">
                <p><input type="submit" class="button button-primary" value="Upload"></p>
            </form>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>JSON File</th>
                        <th>Download</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $file_number = 1;
                    foreach ($json_files as $json_file) {
                        if (pathinfo($json_file, PATHINFO_EXTENSION) === 'json') {
                            ?>
                            <tr>
                                <td>
                                    <?php echo $file_number; ?>
                                </td>
                                <td>
                                    <?php echo $json_file; ?>
                                </td>
                                <td>
                                    <a href="<?php echo $upload_dir['baseurl'] . '/mangwp/' . $json_file . '?v=' . time(); ?>"
                                        class="button button-secondary" download>Download</a>
                                </td>

                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="json_file" value="<?php echo $json_file; ?>">
                                        <input type="submit" name="delete_json" class="button button-secondary" value="Delete">
                                    </form>
                                </td>
                            </tr>
                            <?php
                            $file_number++;
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="color-tab" class="tab-content" style="display:none;">
            <form method="POST">
                <p>Enter your palette name:</p>
                <input type="text" name="pallet_name">
                <p>Enter your CSS variables:</p>
                <textarea rows="4" cols="50" name="css_variable"></textarea>
                <p><input type="submit" name="submit" class="button button-primary" value="Submit"></p>
            </form>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>JSON File</th>
                        <th>Download</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display the uploaded JSON files in the table
                    foreach ($json_files as $index => $json_file) {
                        $filename = basename($json_file);
                        $palette_name = ''; // Replace with your code to extract the palette name from the JSON file
                
                        echo '<tr>';
                        echo '<td>' . ($index + 1) . '</td>';
                        echo '<td>' . $filename . '</td>';
                        echo '<td><a href="' . $upload_dir['baseurl'] . '/mangwp/' . $filename . '" class="button button-primary" download>Download</a></td>';
                        echo '<td><form method="POST" onsubmit="return confirm(\'Are you sure you want to delete this JSON file?\')"><input type="hidden" name="json_file" value="' . $filename . '"><input type="submit" name="delete_json" class="button button-secondary" value="Delete"></form></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div id="variable-tab" class="tab-content" style="display:none;">
            <p>Choose CSS variables from the uploaded color palettes.</p>
            <div>
                           <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <textarea name="mangwp_variable_picker" id="mangwp_variable_picker" rows="5" cols="50"></textarea>
                <br>
                <input type="submit" name="submit_variable_picker" value="Submit" class="button button-primary">
                <?php
                // Add the necessary hidden fields for WordPress admin-post.php action
                echo '<input type="hidden" name="action" value="submit_variable_picker">';
                wp_nonce_field('variable_picker_submit', 'variable_picker_nonce');
                ?>
            </form>
            </div>
        </div>

        <!-- Add JavaScript to handle tab switching and toggle enqueue CSS -->
        <script>
            jQuery(document).ready(function ($) {
                $('.nav-tab-wrapper a').on('click', function (e) {
                    e.preventDefault();
                    $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active');
                    $('.tab-content').hide();
                    $($(this).attr('href')).show();
                });
            });
        </script>
    </div>
    <?php
}
// Handle form submission
// Handle form submission
function mangwp_handle_variable_picker_submission() {
    // Verify the nonce to ensure the request is legitimate
    if (isset($_POST['submit_variable_picker']) && wp_verify_nonce($_POST['variable_picker_nonce'], 'variable_picker_submit')) {
        $css_variables = sanitize_textarea_field($_POST['mangwp_variable_picker']);

        // Extract variable names from CSS variables
        $variable_names = array();
        preg_match_all('/--([^\s:]+)/', $css_variables, $matches);
        if (!empty($matches[1])) {
            $variable_names = $matches[1];
        }

        // Save the variable names into the database table
        global $wpdb;
        $table_name = $wpdb->prefix . 'mangwp_css_variables';

        foreach ($variable_names as $variable_name) {
            // Check if the variable name already exists in the database
            $existing_variable = $wpdb->get_var($wpdb->prepare("SELECT css_variables FROM $table_name WHERE css_variables = %s", 'var(--' . $variable_name . ')'));

            if (!$existing_variable) {
                $data = array(
                    'css_variables' => 'var(--' . trim($variable_name) . ')'
                );

                $wpdb->insert($table_name, $data);
            }
        }

        // Redirect back to the Bricks Utility page after submission
        wp_safe_redirect(admin_url('admin.php?page=mangwp-plugin-bricks'));
        exit;
    }
}
// Hook the form submission handler to the appropriate action
add_action('admin_post_submit_variable_picker', 'mangwp_handle_variable_picker_submission');
add_action('admin_post_nopriv_submit_variable_picker', 'mangwp_handle_variable_picker_submission');
function enqueue_core_css()
{
    if (function_exists("bricks_is_builder_iframe") && bricks_is_builder_iframe()) {
        wp_enqueue_style('core-framework-frontend', '/wp-content/plugins/core-framework/assets/public/css/core_framework.css');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_core_css');

//Check if COre Frame work css file esist then enqueue it
function mangwp_plugin_core_framework_page_callback()
{
    // Display the page content
}
function mangwp_enqueue_scripts() {
      if (function_exists("bricks_is_builder") && bricks_is_builder()) {
     wp_enqueue_style('mangwp-css', plugin_dir_url(__FILE__) . 'assets/mangwp-plugin.css');
    wp_enqueue_script('mangwp-variable-picker', plugin_dir_url(__FILE__) . 'assets/variable-picker.js', array(), '1.0', true);
            }
    wp_localize_script('mangwp-variable-picker', 'mangwp_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'mangwp_enqueue_scripts');

// AJAX callback function to retrieve CSS variables from the database
function mangwp_get_css_variables() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mangwp_css_variables';

    // Retrieve CSS variables from the database table
    $css_variables = $wpdb->get_results("SELECT css_variables FROM $table_name");

    // Prepare the response
    $response = array();
    foreach ($css_variables as $css_variable) {
        $response[] = array(
            'name' => $css_variable->css_variables
        );
    }

    // Send the response as JSON
    wp_send_json($response);
}
add_action('wp_ajax_get_css_variables', 'mangwp_get_css_variables');
add_action('wp_ajax_nopriv_get_css_variables', 'mangwp_get_css_variables');