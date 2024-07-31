<?php
/**
 * Plugin Name: エディター最適化サンプル
 * Description: Sample plugin for editor curation.
 * Version: 1.0
 * Author: Koji Kuno
 * Author URI: https://olein-design.com
 * License: GPL2 or later
 */

/**
 * JavaScript ファイルをエンキューする
 * @return void
 */
function wp_labo_enqueue_editor_curation_js() {
	wp_enqueue_script(
		'wp-labo-editor-curation-sample',
		plugins_url( '/assets/js/editor-curation-sample.js', __FILE__ ),
		array( 'wp-blocks', 'wp-dom-ready' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/editor-curation-sample.js' ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'wp_labo_enqueue_editor_curation_js' );

/**
 * 特定のブロックを無効にする
 *
 * @link https://developer.wordpress.org/news/2024/01/29/how-to-disable-specific-blocks-in-wordpress/
 *
 * @param $allowed_block_types
 * @param $block_editor_context
 *
 * @return string[]|true
 */
function wp_labo_allowed_block_types_when_editing_posts( $allowed_block_types, $block_editor_context ) {

	if (
		'core/edit-post' === $block_editor_context->name &&
		isset( $block_editor_context->post ) &&
		'post' === $block_editor_context->post->post_type
	) {
		$allowed_block_types = array(
			'core/heading',
			'core/paragraph',
			'core/image',
			'core/list',
			'core/list-item',
			'core/missing'
		);

		return $allowed_block_types;
	}

	return true;
}
//add_filter( 'allowed_block_types_all', 'wp_labo_allowed_block_types_when_editing_posts', 10, 2 );

/**
 * ブロックサポートの無効化
 *
 * @link https://developer.wordpress.org/news/2023/05/24/curating-the-editor-experience-with-client-side-filters/
 *
 * @param $args
 * @param $block_type
 *
 * @return array
 */
function wp_labo_disable_block_support( $args, $block_type ) {
	$post_type = wp_labo_get_post_type();

	if ( 'post' === $post_type ) {
		$block_types_to_modify = [
			'core/paragraph',
			'core/heading',
			'core/list',
			'core/list-item',
		];

		if ( in_array( $block_type, $block_types_to_modify, true ) ) {
			$args['supports']['color'] = array(
				'text' => false,
				'background' => false,
				'link' => false,
			);
		}
	}

	return $args;
}
//add_filter( 'register_block_type_args', 'wp_labo_disable_block_support', 10, 2 );

/**
 * 投稿タイプを取得するためのユーティリティ関数
 *
 * register_block_type_args フィルターが実行されると、get_current_screen() などの関数が使用できなくなるため、この関数が必要になる
 *
 * @return false|string
 */
function wp_labo_get_post_type() {
	$post_type = '';

	if ( isset( $_GET['post'] ) ) {
		$post_type = get_post_type( absint( $_GET['post'] ) );
	} elseif ( isset( $_GET['post_type'] ) ) {
		$post_type = sanitize_key( $_GET['post_type'] );
	} elseif ( isset( $_POST['postType'] ) ) {
		$post_type = sanitize_key( $_POST['postType'] );
	}

	return $post_type;
}

/**
 * ブロックサポートを有効にする
 *
 * メディアとテキストブロック内の画像にデュオトーンサポートを追加する例
 *
 * @param $args
 * @param $block_type
 *
 * @return mixed
 */
function wp_labo_enable_duotone_to_media_text_blocks( $args, $block_type ) {
	if ( 'core/media-text' !== $block_type ) {
		return $args;
	}

	$args['supports'] ??= [];
	$args['supports']['filter'] ??= [];
	$args['supports']['filter']['duotone'] = true;

	$args['selectors'] ??= [];
	$args['selectors']['filter'] ??= [];
	$args['selectors']['filter']['duotone'] = '.wp-block-media-text .wp-block-media-text__media';

	return $args;
}
//add_filter( 'register_block_type_args', 'wp_labo_enable_duotone_to_media_text_blocks', 10, 2 );

/**
 * 管理者以外のユーザーはブロックをロックまたはロック解除できなくする
 *
 * エディターの設定可能項目を確認するにはブラウザのコンソールで `wp.data.select( 'core/editor' ).getEditorSettings();` を実行する
 *
 * @param $settings 現在のエディターの設定の配列
 * @param $context 現在のブロックエディターのコンテキスト
 *
 * @return mixed
 */
function wp_labo_restrict_block_locking_to_administrators( $settings, $context ) {
	$is_administrator = current_user_can( 'edit_theme_options' );

	if ( ! $is_administrator ) {
		$settings['canLockBlocks'] = false;
	}

	return $settings;
}
//add_filter( 'block_editor_settings_all', 'wp_labo_restrict_block_locking_to_administrators', 10, 2 );

/**
 * 管理者以外のユーザーはブロックをロック・ロック解除できなくし、コードエディターに切り替えられないようにする
 *
 * @param $settings
 * @param $context
 *
 * @return mixed
 */
function wp_labo_restrict_block_locking_and_code_editor_to_administrators( $settings, $context ) {
	$is_administrator = current_user_can( 'edit_theme_options' );

	if ( ! $is_administrator ) {
		$settings['canLockBlocks'] = false;
		$settings['codeEditingEnabled'] = false;
	}

	return $settings;
}
add_filter( 'block_editor_settings_all', 'wp_labo_restrict_block_locking_and_code_editor_to_administrators', 10, 2 );

/**
 * インスペクタータブを無効にする
 *
 * サンプルではボタンブロックのインスペクタータブを無効にしています
 *
 * @param $settings
 *
 * @return mixed
 */
function wp_labo_disable_inspector_tabs_for_specific_blocks( $settings ) {
	if ( ! isset( $settings['blockInspectorTabs'] ) ) {
		$settings['blockInspectorTabs'] = array();
	}

	$settings['blockInspectorTabs'] = array_merge(
		$settings['blockInspectorTabs'],
		array(
			'default' => true, // ここを false にすると全てのブロックでインスペクタータブが無効になります
			'core/button' => false,
		),
	);

	return $settings;
}
add_filter( 'block_editor_settings_all', 'wp_labo_disable_inspector_tabs_for_specific_blocks' );

/**
 * オープンバースを無効にする
 *
 * @param $settings
 * @param $context
 *
 * @return mixed
 */
function wp_labo_disable_openverse( $settings, $context ) {
	$settings['enableOpenverseMediaCategory'] = false;
	return $settings;
}
add_filter( 'block_editor_settings_all', 'wp_labo_disable_openverse', 10, 2 );

/**
 * 画像の標準サイズをフルサイズに変更する
 *
 * @param $settings
 * @param $context
 *
 * @return mixed
 */
function wp_labo_set_default_image_size( $settings, $context ) {
	if (
		! empty( $context->post ) &&
		'post' === $context->post->post_type
	) {
		$settings['imageDefaultSize'] = 'full'; // デフォルトをフルサイズに設定
	}

	return $settings;
}
add_filter( 'block_editor_settings_all', 'wp_labo_set_default_image_size', 10, 2 );

/**
 * ブロックエディターのディレクトリを無効にする
 */
remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );

/**
 * パターンディレクトリから提供されているパターンを読み込み無効にする
 */
add_filter( 'should_load_remote_block_patterns', '__return_false' );

/**
 * テンプレートを変更できなくする
 * ブロックエディターでもページ一覧でも対応
 *
 * @return void
 */
function wp_labo_disable_template_editor_for_posts() {
	$screen = get_current_screen();

	if ( 'post' === $screen->post_type ) {
		remove_theme_support( 'block-templates' );
	}
}
add_action( 'current_screen', 'wp_labo_disable_template_editor_for_posts' );