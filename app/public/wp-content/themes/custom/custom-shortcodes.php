<?php if (!defined('ABSPATH')) exit;

/**
 * Custom Ping
 * 
 * Just to test, if shortcodes are working.
 */
add_shortcode('custom_ping', function ($atts = [], $content = null) {
	if (
		strpos($_SERVER['REQUEST_URI'], '/post.php') !== false ||
		strpos($_SERVER['REQUEST_URI'], 'elementor') !== false
	) {
		return '&#x5B;custom_ping&#x5B;';
	} else {
		$atts = shortcode_atts([
			'echo' => 'Pong'
		], $atts);
		ob_clean();
		ob_start();
		echo $atts['echo'];
		return ob_get_clean();
	}
});

/**
 * Term Prop
 * 
 * Get a property from the current term
 * or from the term of the given slug or id.
 */
add_shortcode('term_prop', function ($atts = [], $content = null) {
    if (
        strpos($_SERVER['REQUEST_URI'], '/post.php') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'elementor') !== false
    ) {
        return '&#x5B;term_prop&#x5B;';
    } else {
        $atts = shortcode_atts(array(
            'id' => 0,
            'slug' => '',
            'taxonomy' => 'category',
            'prop' => ''
        ), $atts);
        ob_clean();
        ob_start();
        if (strlen($atts['prop'])) {
            $term = null;
            if ($atts['id']) {
                $term = get_term_by('id', $atts['id'], $atts['taxonomy']);
            } elseif (strlen($atts['slug'])) {
                $term = get_term_by('slug', $atts['slug'], $atts['taxonomy']);
            } else {
                // TODO: get current term
            }
            if ($term) {
                echo $term->{$atts['prop']};
            }
        }
        return ob_get_clean();
    }
});

/**
 * Term Meta
 * 
 * Get a meta value from the current term
 * or from the term of the given slug or id.
 */
add_shortcode('term_meta', function ($atts = [], $content = null) {
    if (
        strpos($_SERVER['REQUEST_URI'], '/post.php') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'elementor') !== false
    ) {
        return '&#x5B;term_meta&#x5B;';
    } else {
        $atts = shortcode_atts(array(
            'id' => 0,
            'slug' => '',
            'taxonomy' => 'category',
            'meta' => ''
        ), $atts);
        ob_clean();
        ob_start();
        if (strlen($atts['meta'])) {
            $id = $atts['id'] ? $atts['id'] :
                do_shortcode('[term_prop slug="' . $atts['slug'] . '" taxonomy="' . $atts['taxonomy'] . '" prop="term_id"]');
            if ($id) {
                echo get_term_meta($id, $atts['meta'], true);
            }
        }
        return ob_get_clean();
    }
});

/**
 * Term Thumbnail Image
 *
 * Get the thumbnail image HTML from the current term
 * or from the term of the given slug or id
 */
add_shortcode('term_attachment_image', function ($atts = [], $content = null) {
    if (
        strpos($_SERVER['REQUEST_URI'], '/post.php') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'elementor') !== false
    ) {
        return '&#x5B;term_attachment_image&#x5B;';
    } else {
        $atts = shortcode_atts(array(
            'id' => 0,
            'slug' => '',
            'taxonomy' => 'category',
        ), $atts);
        ob_clean();
        ob_start();
        $term = null;
        if ($atts['id']) {
            $term = get_term_by('id', $atts['id'], $atts['taxonomy']);
        } elseif (strlen($atts['slug'])) {
            $term = get_term_by('slug', $atts['slug'], $atts['taxonomy']);
        } else {
            // TODO: get current term
        }
        if ($term) {
            $src = wp_get_attachment_url(get_term_meta($term->term_id, 'thumbnail_id', true));
            if (strlen($src)) {
                echo '<img src="' . $src . '" alt="' . $term->name . '" />';
            }
        }
        return ob_get_clean();
    }
});

/**
 * Heavy
 * 
 * Lazy load heavy elements
 */
add_shortcode('heavy', function ($atts = [], $content = null) {
	if (
		strpos($_SERVER['REQUEST_URI'], '/post.php') !== false ||
		strpos($_SERVER['REQUEST_URI'], 'elementor') !== false
	) {
		return $content;
	} else {
		$atts = shortcode_atts([
			'offset' => '100',
			'onload' => ''
		], $atts);
		ob_clean();
		ob_start();
		$id = wp_generate_uuid4();
?>
		<div id="<?php echo $id; ?>" class="heavy-container" data-offset="<?php echo $atts['offset']; ?>" onload="<?php echo $atts['onload']; ?>"></div>
		<script type='text/javascript'>
			(function() {
				window.heavy = window.heavy || {};
				window.heavy["<?php echo $id; ?>"] = <?php echo json_encode(do_shortcode($content)); ?>;
			})();
		</script>
<?php
		return ob_get_clean();
	}
});
