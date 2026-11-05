<?php
/** Optical sizing for editable partner Image blocks; original assets stay intact. */
if (!defined('ABSPATH')) {
    exit;
}

/** Find the visible mark inside a padded upload. No image file is changed. */
function yea_partner_logo_bounds($attachment_id) {
    if (!function_exists('imagecreatefromstring')) {
        return null;
    }
    $file = get_attached_file($attachment_id);
    if (!$file || !is_readable($file)) {
        return null;
    }
    $cache_key = 'yea_logo_bounds_' . $attachment_id . '_' . filemtime($file);
    $cached = get_transient($cache_key);
    if (false !== $cached) {
        return $cached ?: null;
    }
    $size = wp_getimagesize($file);
    // Bound memory and CPU; unusual uploads retain ordinary contain sizing.
    if (!$size || $size[0] * $size[1] > 4000000) {
        return null;
    }
    $image = imagecreatefromstring(file_get_contents($file));
    if (!$image) {
        return null;
    }
    $width = imagesx($image);
    $height = imagesy($image);
    $step = max(1, (int) floor(max($width, $height) / 240));
    $left = $width;
    $right = -1;
    $top = $height;
    $bottom = -1;
    for ($y = 0; $y < $height; $y += $step) {
        for ($x = 0; $x < $width; $x += $step) {
            $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
            if ($color['alpha'] >= 120 || min($color['red'], $color['green'], $color['blue']) > 245) {
                continue;
            }
            $left = min($left, $x);
            $right = max($right, $x);
            $top = min($top, $y);
            $bottom = max($bottom, $y);
        }
    }
    imagedestroy($image);
    $bounds = null;
    if ($right >= $left && $bottom >= $top) {
        $padding = max($step * 2, (int) ceil(max($right - $left, $bottom - $top) * 0.06));
        $left = max(0, $left - $padding);
        $top = max(0, $top - $padding);
        $right = min($width - 1, $right + $padding);
        $bottom = min($height - 1, $bottom + $padding);
        $bounds = array('x' => $left, 'y' => $top, 'width' => $right - $left + 1, 'height' => $bottom - $top + 1, 'source_width' => $width);
    }
    set_transient($cache_key, $bounds ?: array(), WEEK_IN_SECONDS);
    return $bounds;
}

/** Apply optical sizing only to images in the partner component, not site-wide. */
function yea_render_partner_logos($html, $block) {
    $classes = isset($block['attrs']['className']) ? explode(' ', $block['attrs']['className']) : array();
    if (!in_array('logo-wall-grid', $classes, true) || !class_exists('DOMDocument')) {
        return $html;
    }
    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?><div id="yea-logos-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) {
        return $html;
    }
    foreach ($dom->getElementsByTagName('img') as $image) {
        if (!preg_match('/\bwp-image-(\d+)\b/', $image->getAttribute('class'), $match)) {
            continue;
        }
        $bounds = yea_partner_logo_bounds((int) $match[1]);
        if (!$bounds || !$image->parentNode) {
            continue;
        }
        // Native sizes=auto applies size containment to lazy images. Inside an
        // optical window that makes height:auto use the 1500px placeholder
        // instead of the source ratio. Keep the explicit responsive sizes.
        $sizes = preg_replace('/^\s*auto\s*,?\s*/i', '', $image->getAttribute('sizes'));
        if ($sizes) {
            $image->setAttribute('sizes', $sizes);
        } else {
            $image->removeAttribute('sizes');
        }
        $image->setAttribute('class', trim($image->getAttribute('class') . ' yea-partner-logo-image'));
        $canvas = $dom->createElement('span');
        $canvas->setAttribute('class', 'yea-partner-logo-canvas');
        $canvas->setAttribute('style', sprintf(
            '--logo-ratio:%1$s;--logo-image-width:%2$s%%;--logo-offset-x:%3$s%%;--logo-offset-y:%4$s%%',
            number_format($bounds['width'] / $bounds['height'], 5, '.', ''),
            number_format(100 * $bounds['source_width'] / $bounds['width'], 5, '.', ''),
            number_format(-100 * $bounds['x'] / $bounds['width'], 5, '.', ''),
            number_format(-100 * $bounds['y'] / $bounds['height'], 5, '.', '')
        ));
        $image->parentNode->insertBefore($canvas, $image);
        $canvas->appendChild($image);
    }
    $root = $dom->getElementById('yea-logos-root');
    if (!$root) {
        return $html;
    }
    $output = '';
    foreach ($root->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }
    return $output ?: $html;
}
add_filter('render_block_core/group', 'yea_render_partner_logos', 20, 2);

/** Keep the logo exception after WordPress's later content-image optimization. */
function yea_partner_logo_explicit_sizes($image) {
    if (false === strpos($image, 'yea-partner-logo-image')) {
        return $image;
    }
    return preg_replace('/(\ssizes=([\"\']))\s*auto\s*,?\s*/i', '$1', $image);
}
add_filter('wp_content_img_tag', 'yea_partner_logo_explicit_sizes', 99);
