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
          $rgb = hslToRgb($value);
        }
        // Check if the value is RGB or RGBA
        elseif (preg_match('/^rgb\(/i', $value) || preg_match('/^rgba\(/i', $value)) {
            // Use the RGB value as is
            $rgb = $value;
            // Convert RGB to HEX
            $hex = rgbToHex($value);
         $hsl = rgbToHsl($value);
        }
        // Check if the value is HEX
        elseif (preg_match('/^#[a-f0-9]/i', $value)) {
            // Use the HEX value as is
            $hex = $value;
            // Convert HEX to HSL
            $hsl = hexToHsl($value);
         $rgb = hexToRgb($value);
            // Convert HEX to RGB
        };

        $parsed_variables[] = array(
            'hex' => $hex ?? null,
            'hsl' => $hsl ?? null,
            'rgb' => $rgb ?? null,
            'raw' => 'var(' . $name . ')',
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
    $json_string = json_encode($json_data, JSON_PRETTY_PRINT);

    // Generate a random filename for the JSON file
    $json_filename = 'color_pallet_' . uniqid() . '.json';

    // Save the JSON data to the uploads directory
    $json_path = $upload_dir['basedir'] . '/mangwp/' . $json_filename;
    file_put_contents($json_path, $json_string);
}
//HSL TO HEX//
function hslToHex($hsl) {
    $hsl_parts = explode(',', str_replace(['hsl(', 'hsla(', ')', '%'], '', $hsl));
    $h = intval($hsl_parts[0]);
    $s = intval($hsl_parts[1]);
    $l = intval($hsl_parts[2]);

    // Convert HSL to HEX
    $hex = hslToHexadecimal($h, $s, $l);

    return $hex;
}

function hslToHexadecimal($h, $s, $l) {
    $h = $h / 360;
    $s = $s / 100;
    $l = $l / 100;

    $r = $g = $b = 0;

    if ($s == 0) {
        $r = $g = $b = $l;
    } else {
        $hue2rgb = function ($p, $q, $t) {
            if ($t < 0) $t += 1;
            if ($t > 1) $t -= 1;
            if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
            if ($t < 1/2) return $q;
            if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
            return $p;
        };

        $q = ($l < 0.5) ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $r = $hue2rgb($p, $q, $h + 1/3);
        $g = $hue2rgb($p, $q, $h);
        $b = $hue2rgb($p, $q, $h - 1/3);
    }

    $rgb = [
        round($r * 255),
        round($g * 255),
        round($b * 255)
    ];

    // Convert RGB to HEX
    $hex = rgbToHexadecimal($rgb);

    return $hex;
}

function rgbToHexadecimal($rgb) {
    $hex = '#';
    foreach ($rgb as $color) {
        $hex .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT);
    }
    return $hex;
}

//HSL TO RGB//
function hslToRgb($hsl) {
    $hsl_parts = explode(',', str_replace(['hsl(', 'hsla(', ')', '%'], '', $hsl));
    $h = intval($hsl_parts[0]);
    $s = intval($hsl_parts[1]);
    $l = intval($hsl_parts[2]);

    // Check if HSLA value is provided
    $a = isset($hsl_parts[3]) ? floatval($hsl_parts[3]) : 1.0;

    // Convert HSL to RGB
    $r = $g = $b = 0;

    $h = $h / 360;
    $s = $s / 100;
    $l = $l / 100;

    if ($s == 0) {
        $r = $g = $b = $l;
    } else {
        $hue2rgb = function ($p, $q, $t) {
            if ($t < 0) $t += 1;
            if ($t > 1) $t -= 1;
            if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
            if ($t < 1/2) return $q;
            if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
            return $p;
        };

        $q = ($l < 0.5) ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $r = $hue2rgb($p, $q, $h + 1/3);
        $g = $hue2rgb($p, $q, $h);
        $b = $hue2rgb($p, $q, $h - 1/3);
    }

    $rgb = [
        round($r * 255),
        round($g * 255),
        round($b * 255),
        $a
    ];

    // Format RGB values
    $formatted_rgb = implode(', ', $rgb);

    // Check if alpha is provided
    if ($a !== 1.0) {
        return "rgba($formatted_rgb)";
    } else {
        return "rgb($formatted_rgb)";
    }
}


//HEX TO HSLA//
function hexToHsl($hex, $alpha = false) {
    // Remove '#' if present
    $hex = str_replace('#', '', $hex);

    // Convert 3-digit hex to 6-digit hex
    if (strlen($hex) === 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) .
                str_repeat(substr($hex, 1, 1), 2) .
                str_repeat(substr($hex, 2, 1), 2);
    }

    // Convert hex to RGB
    $rgb = sscanf($hex, '%2x%2x%2x');
    [$r, $g, $b] = $rgb;

    // Convert RGB to HSL
    $r /= 255;
    $g /= 255;
    $b /= 255;

    $max = max($r, $g, $b);
    $min = min($r, $g, $b);

    $h = $s = $l = ($max + $min) / 2;

    if ($max == $min) {
        $h = $s = 0; // achromatic
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

    // Convert HSL to HSLA if alpha is provided
    if ($alpha !== false) {
        $alpha = max(0, min(1, $alpha)); // Clamp alpha value between 0 and 1
        return rtrim("hsla(" . round($h * 360) . ", " . round($s * 100) . "%, " . round($l * 100) . "%, " . $alpha . ")", ';');
    }

    // Convert RGB to HSLA if alpha is provided
    return rtrim("hsl(" . round($h * 360) . ", " . round($s * 100) . "%, " . round($l * 100) . "%)", ';');
}
//HEX TO RGB/RGBA//
function hexToRgb($hex, $alpha = false) {
    // Remove '#' if present
    $hex = str_replace('#', '', $hex);

    // Convert 3-digit hex to 6-digit hex
    if (strlen($hex) === 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) .
                str_repeat(substr($hex, 1, 1), 2) .
                str_repeat(substr($hex, 2, 1), 2);
    }

    // Convert hex to RGB
    $rgb = sscanf($hex, '%2x%2x%2x');
    [$r, $g, $b] = $rgb;

    // Format RGB values
    $formatted_rgb = implode(', ', [$r, $g, $b]);

    // Check if alpha is provided
    if ($alpha !== false) {
        $alpha = max(0, min(1, $alpha)); // Clamp alpha value between 0 and 1
        return "rgba($formatted_rgb, $alpha)";
    } else {
        return "rgb($formatted_rgb)";
    }
}
//RGB to Hex//
function rgbToHex($rgb) {
    // Remove 'rgb' or 'rgba' and parentheses
    $rgb = str_replace(['rgb', 'rgba', '(', ')'], '', $rgb);

    // Explode RGB values
    $rgb_parts = explode(',', $rgb);
    $r = intval(trim($rgb_parts[0]));
    $g = intval(trim($rgb_parts[1]));
    $b = intval(trim($rgb_parts[2]));

    // Convert RGB to hexadecimal
    $hex = sprintf("#%02x%02x%02x", $r, $g, $b);

    return $hex;
}

//RGB to HSL//
function rgbToHsl($rgb, $alpha = false) {
    // Remove 'rgb' or 'rgba' and parentheses
    $rgb = str_replace(['rgb', 'rgba', '(', ')'], '', $rgb);

    // Explode RGB values
    $rgb_parts = explode(',', $rgb);
    $r = intval(trim($rgb_parts[0]));
    $g = intval(trim($rgb_parts[1]));
    $b = intval(trim($rgb_parts[2]));

    // Normalize RGB values
    $r /= 255;
    $g /= 255;
    $b /= 255;

    // Calculate HSL components
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $h = $s = $l = ($max + $min) / 2;

    if ($max != $min) {
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

    // Convert HSL to HSLA if alpha is provided
    if ($alpha !== false) {
        $alpha = max(0, min(1, $alpha)); // Clamp alpha value between 0 and 1
        return "hsla(" . round($h * 360) . ", " . round($s * 100) . "%, " . round($l * 100) . "%, " . $alpha . ")";
    }

    return "hsl(" . round($h * 360) . ", " . round($s * 100) . "%, " . round($l * 100) . "%)";
}


