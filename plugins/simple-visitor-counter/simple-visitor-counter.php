<?php
/**
 * Plugin Name: Simple Visitor Counter
 * Description: Display the number of current visitors on your site.
 * Version: 1.0
 * Author: Your Name
 * Text Domain: simple-visitor-counter
 */

/**
 * データベーステーブルを作成
 */
function svc_create_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'visitor_counter';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        session_id varchar(255) NOT NULL,
        user_ip varchar(100) NOT NULL,
        last_activity datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY session_id (session_id),
        KEY user_ip (user_ip)
    ) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta($sql);
}
register_activation_hook(__FILE__, 'svc_create_table');

/**
 * データベーステーブルを削除
 */
function svc_drop_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'visitor_counter';
	$sql = "DROP TABLE IF EXISTS $table_name;";
	$wpdb->query($sql);
}
register_deactivation_hook(__FILE__, 'svc_drop_table');

/**
 * 訪問者のセッションを記録
 */
function svc_record_visitor() {
	// 管理画面へのアクセスを除外
	if (is_admin()) {
		return;
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'visitor_counter';

	if (!session_id()) {
		session_start();
	}

	$session_id    = session_id();
	$user_ip       = $_SERVER['REMOTE_ADDR'];
	$current_time  = current_time('mysql');
	$expiration_time = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime($current_time)));

	// 古いセッションを削除
	$wpdb->query($wpdb->prepare(
		"DELETE FROM $table_name WHERE last_activity < %s",
		$expiration_time
	));

	// 現在のセッションを更新または挿入
	$wpdb->replace(
		$table_name,
		array(
			'session_id'    => $session_id,
			'user_ip'       => $user_ip,
			'last_activity' => $current_time,
		),
		array(
			'%s',
			'%s',
			'%s',
		)
	);

	// セッションハイジャック防止のため、セッションIDを再生成
	session_regenerate_id(true);
}
add_action('init', 'svc_record_visitor');

/**
 * 現在のアクセス数を取得
 *
 * @return int 現在の訪問者数
 */
function svc_get_current_visitors() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'visitor_counter';

	$current_time = current_time('mysql');
	$expiration_time = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime($current_time)));

	// 現在のアクセス数をカウント
	$current_visitors = $wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(DISTINCT user_ip) FROM $table_name WHERE last_activity >= %s",
		$expiration_time
	));

	return $current_visitors;
}

/**
 * ショートコードを作成
 *
 * @return string 現在の訪問者数を表示する文字列
 */
function svc_display_visitor_count() {
	$current_visitors = intval(svc_get_current_visitors());
	return esc_html__('Current visitors: ', 'simple-visitor-counter') . esc_html($current_visitors);
}
add_shortcode('visitor_count', 'svc_display_visitor_count');