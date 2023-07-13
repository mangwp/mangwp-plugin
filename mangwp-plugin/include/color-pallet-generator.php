<?php
// Handle form submission
    if (isset($_POST['pallet_name']) && isset($_POST['css_variable'])) {
        $pallet_name = sanitize_text_field($_POST['pallet_name']);
        $css_variables = sanitize_textarea_field($_POST['css_variable']);

        // Parse CSS variables
        $parsed_variables = array();
        $css_lines = explode("\n", $css_variables);
        foreach ($css_lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, ':') === false) {
                continue;
            }

            $parts = explode(':', $line, 2);
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            // Check if the value is HSL
            if (preg_match('/^hsl\(/i', $value) || preg_match('/^hsla\(/i', $value)) {
                // Use the HSL value as is
                $hsl = $value;
                // Convert HSL to HEX
                $hex = hslToHex($value);
            } elseif (preg_match('/^#[a-f0-9]{3,6}$/i', $value)) {
                // Use the HEX value as is
                $hex = $value;
                // Convert HEX to HSL
                $hsl = hexToHsl($value);
            } else {
                // Ignore the invalid value
                continue;
            }

            $parsed_variables[] = array(
                'hsl' => $hsl,
                'hex' => $hex,
                'raw' => $name,
                'id' => substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6),
                'name' => 'Color #' . count($parsed_variables)
            );
        }

        // Prepare JSON data
        $json_data = array(
            'id' => substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6),
            'name' => $pallet_name,
            'colors' => $parsed_variables
        );

        // Convert JSON data to a string

            $parsed_variables[] = array(
                'hsl' => $hsl,
                'hex' => $hex,
                'raw' => 'var('.$name.')',
                'id' => substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6),
                'name' => 'Color #' . count($parsed_variables)
            );

        // Prepare JSON data
        $json_data = array(
            'id' => substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6),
            'name' => $pallet_name,
            'colors' => $parsed_variables
        );

        // Convert JSON data to a string
        $json_string = json_encode($json_data, JSON_PRETTY_PRINT);

        // Generate a random filename for the JSON file
        $json_filename = 'color_pallet_' . uniqid() . '.json';

        // Save the JSON data to the uploads directory
        $json_path = $upload_dir['basedir'] . '/mangwp/' . $json_filename;
        file_put_contents($json_path, $json_string);
    }
// Helper function to convert HSL to HEX
function hslToHex($hsl) {
    $hsl_parts = explode(',', str_replace(['hsl(', 'hsla(', ')', '%'], '', $hsl));
    $h = intval($hsl_parts[0]);
    $s = intval($hsl_parts[1]);
    $l = intval($hsl_parts[2]);

    // Convert HSL to RGB
    $rgb = hslToRgb($h, $s, $l);

    // Convert RGB to HEX
    $hex = rgbToHex($rgb);

    return $hex;
}
// Helper function to convert HSL to RGB
function hslToRgb($h, $s, $l) {
    $h /= 360;
    $s /= 100;
    $l /= 100;

    if ($s === 0) {
        $r = $g = $b = $l;
    } else {
        $var2 = $l < 0.5 ? $l * (1 + $s) : ($l + $s) - ($s * $l);
        $var1 = 2 * $l - $var2;

        $r = hueToRgb($var1, $var2, $h + (1 / 3));
        $g = hueToRgb($var1, $var2, $h);
        $b = hueToRgb($var1, $var2, $h - (1 / 3));
    }

    return array(round($r * 255), round($g * 255), round($b * 255));
}
// Helper function to convert HEX to HSL
function hexToHsl($hex) {
    // Convert HEX to RGB
    $rgb = hexToRgb($hex);

    // Convert RGB to HSL
    $hsl = rgbToHsl($rgb);

    return $hsl;
}
// Helper function to calculate RGB value from hue
function hueToRgb($v1, $v2, $vh) {
    if ($vh < 0) {
        $vh += 1;
    }
    if ($vh > 1) {
        $vh -= 1;
    }
    if ((6 * $vh) < 1) {
        return $v1 + ($v2 - $v1) * 6 * $vh;
    }
    if ((2 * $vh) < 1) {
        return $v2;
    }
    if ((3 * $vh) < 2) {
        return $v1 + ($v2 - $v1) * ((2 / 3) - $vh) * 6;
    }
    return $v1;
}
// Helper function to convert HEX to RGB
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    $length = strlen($hex);

    if ($length === 3) {
        $r = hexdec($hex[0] . $hex[0]);
        $g = hexdec($hex[1] . $hex[1]);
        $b = hexdec($hex[2] . $hex[2]);
    } elseif ($length === 6) {
        $r = hexdec($hex[0] . $hex[1]);
        $g = hexdec($hex[2] . $hex[3]);
        $b = hexdec($hex[4] . $hex[5]);
    } else {
        // Invalid HEX value
        return false;
    }

    return array($r, $g, $b);
}

// Helper function to convert RGB to HSL
function rgbToHsl($rgb) {
    $r = $rgb[0] / 255;
    $g = $rgb[1] / 255;
    $b = $rgb[2] / 255;

    $max = max($r, $g, $b);
    $min = min($r, $g, $b);

    $l = ($max + $min) / 2;

    if ($max === $min) {
        $h = $s = 0;
    } else {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        switch ($max) {
            case $r:
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                break;
            case $g:
                $h = ($b - $r) / $d + 2;
                break;
            case $b:
                $h = ($r - $g) / $d + 4;
                break;
        }

        $h /= 6;
    }

    $h = round($h * 360);
    $s = round($s * 100);
    $l = round($l * 100);

    return "hsl($h, $s%, $l%)";
}
function rgbToHex($rgb) {
    return sprintf("#%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]);
}