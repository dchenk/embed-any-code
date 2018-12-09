<?php
/*
Plugin Name: Embed Any Code
Plugin URI: https://github.com/dchenk/embed-any-code
Description: Insert code after the content of any page or post.
Version: 0.11
Author: Wider Webs
*/

// Output the code.
function embed_any_code_display($content = '') {
	global $post;
	$code = get_post_meta($post->ID, '_embed_any_code', true);
	if ($code) {
		return $content . $code;
	}
	return $content;
}
add_filter('the_content', 'embed_any_code_display');
add_filter('the_excerpt', 'embed_any_code_display');

// Display a box with input fields for code to go before and after the content.
function embed_any_code_meta($post) {
	wp_nonce_field(plugin_basename(__FILE__), 'embed_any_code_nonce');
	$postMeta = get_post_meta($post->ID, '_embed_any_code', true); ?>
	<label for="embed_any_code">Code after content</label><br>
	<textarea id="embed_any_code" name="embed_any_code" style="width:100%; height:80px;"><?php echo $postMeta; ?></textarea><?php
}

// Add the box defined above to post and page edit screens.
function embed_any_code_meta_box() {
	add_meta_box('embed_any_code', 'Embed Code', 'embed_any_code_meta', 'post', 'side');
	add_meta_box('embed_any_code', 'Embed Code', 'embed_any_code_meta', 'page', 'side');
}
add_action('admin_menu', 'embed_any_code_meta_box');

// Update the meta for a post upon save.
function embed_any_code_save($pID) {
	// If the function is called by the WP auto-save feature, nothing must be saved.
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// Verify this came from our screen and with proper authorization, since 'save_post' can be triggered
	// at other times.
	if (!wp_verify_nonce($_POST['embed_any_code_nonce'], plugin_basename(__FILE__))) {
		return;
	}

	// Check permissions.
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $pID)) {
			return;
		}
	} else {
		if (!current_user_can('edit_post', $pID)) {
			return;
		}
	}

	// We're authenticated. Find and save the data.
	$text = $_POST['embed_any_code'] ?? '';
	update_post_meta($pID, '_embed_any_code', $text);
}
add_action('save_post', 'embed_any_code_save');
