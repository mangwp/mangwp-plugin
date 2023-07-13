<?php
/*
Plugin Name: Mangwp Plugin
Description: Colection of custom function of mine. Made by love by mangwp.com
Version: 1.0
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
    // add_submenu_page(
    //     'mangwp-plugin',
    //     'Core Framework Utility',
    //     'Core Framework Utility',
    //     'manage_options',
    //     'mangwp-plugin-core-framework',
    //     'mangwp_plugin_core_framework_page_callback'
    // );
}

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

function enqueue_core_css(){
    if (function_exists("bricks_is_builder_iframe") && bricks_is_builder_iframe()) {
        wp_enqueue_style('core-framework-frontend', '/wp-content/plugins/core-framework/assets/public/css/core_framework.css');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_core_css');

//Check if COre Frame work css file esist then enqueue it
function mangwp_plugin_core_framework_page_callback(){
    ?>
    <div class="wrap">
    <h1>Core Framework Utility</h1>
    <form method="POST">
        <p>
            <input type="hidden" name="custom_function_toggle" value="0">
            <input type="checkbox" name="custom_function_toggle" id="custom-function-toggle" value="1" <?php checked($is_custom_function_enabled, 1); ?>>
            <label for="custom-function-toggle">Enable Custom Function</label>
        </p>
        <p><input type="submit" class="button button-primary" value="Save Changes"></p>
    </form>
</div>
<?php
    $is_custom_function_enabled = get_option('mangwp_custom_function_enabled');

    // Handle form submission
    if (isset($_POST['custom_function_toggle'])) {
        $is_custom_function_enabled = $_POST['custom_function_toggle'] === '1' ? 1 : 0;
        update_option('mangwp_custom_function_enabled', $is_custom_function_enabled);
    }
    if ($is_custom_function_enabled) {
        add_action('wp_enqueue_scripts', 'enqueue_core_css');
    }
    // Display the page content
}
