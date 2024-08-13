<?php
/**
 * Plugin Name: Custom Core Navigation Block
 */

/**
 * Render the core/navigation block with custom SVG code
 *
 * @param string $block_content
 * @param array $block
 *
 * @return array|string|string[]|null
 */
function custom_core_navigation_block (string $block_content, array $block)
{
	$new_svg = '<!-- Custom SVG Code -->';

	if (
		$block['blockName'] === 'core/navigation' &&
		!is_admin() &&
		!wp_is_json_request()
	) {
		return preg_replace('/\<svg width(.*?)\<\/svg\>/', $new_svg, $block_content);
	}

	return $block_content;
}

add_filter('render_block', 'custom_core_navigation_block', 10, 2);