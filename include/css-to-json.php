<?php
// Check if a file has been uploaded
if (isset($_FILES['bricks_file'])) {
    $file = $_FILES['bricks_file'];

    // Verify file type
    $allowed_extension = 'css';
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);

    if (strtolower($file_extension) === $allowed_extension) {
        // Get the uploads directory
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];

        // Create the "mangwp" folder if it doesn't exist
        $mangwp_dir = $base_dir . '/mangwp';
        if (!file_exists($mangwp_dir)) {
            mkdir($mangwp_dir);
        }

        // Handle the uploaded file
        $target_dir = $mangwp_dir . '/';
        $target_file = $target_dir . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // File uploaded successfully
            echo '<div class="updated notice"><p>File uploaded successfully.</p></div>';

            // Read the CSS file
            $css_content = file_get_contents($target_file);

            // Extract class names
            $pattern = '/\.[\w.-]+\s*(?![^{]*\})/';
            preg_match_all($pattern, $css_content, $matches);
            $class_names = $matches[0];

            // Generate random unique IDs
            $ids = array_map(function () {
                return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
            }, $class_names);

            // Create JSON structure
            $json_data = array();
            $unique_class_names = array();
            foreach ($class_names as $index => $class_name) {
                if (strpos($class_name, ':') !== false) {
                    continue; // Skip class names with ":"
                }

                $last_dot_index = strrpos($class_name, '.');
                $class_name_without_dots = substr($class_name, $last_dot_index + 1);

                // Check if the class name already exists
                if (in_array($class_name_without_dots, $unique_class_names)) {
                    continue; // Skip duplicate class names
                }

                $unique_class_names[] = $class_name_without_dots;

                $json_item = array(
                    'id' => $ids[$index],
                    'name' => $class_name_without_dots,
                    'settings' => array()
                );
                $json_data[] = $json_item;
            }

            // Convert JSON to string
            $json_string = json_encode($json_data, JSON_PRETTY_PRINT);

            // Save JSON file with matching CSS file name
            $json_filename = pathinfo($target_file, PATHINFO_FILENAME) . '.json';
            $json_file = $target_dir . $json_filename;
            file_put_contents($json_file, $json_string);
        } else {
            // Error in file upload
            echo '<div class="error notice"><p>Error uploading file.</p></div>';
        }
    } else {
        // Invalid file type
        echo '<div class="error notice"><p>Invalid file type. Only .css files are allowed.</p></div>';
    }
}
// Get the list of uploaded JSON files
$upload_dir = wp_upload_dir();
$mangwp_dir = $upload_dir['basedir'] . '/mangwp';
$json_files = scandir($mangwp_dir);
$json_files = array_diff($json_files, array('.', '..'));

// Handle JSON file deletion
if (isset($_POST['delete_json']) && isset($_POST['json_file'])) {
    $json_file = $_POST['json_file'];
    $file_path = $mangwp_dir . '/' . $json_file;
    if (file_exists($file_path)) {
        unlink($file_path);
        echo '<div class="updated notice"><p>JSON file deleted successfully.</p></div>';
        echo '<script>window.location.href = window.location.href;</script>';

    }
}