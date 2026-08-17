<?php
/*
Plugin Name: sHub-jld
Plugin URI: https://syn-c.jp/service/plugin/syntaxhub-jld/
Description: Optimize your site's structured data and meta output with sHub-jld.
Version: 1.0.11
Author: SyntaxCloud LLC
Author URI: https://syn-c.jp/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: syntaxhub-jld
*/


if (!defined('ABSPATH')) exit;

// 各機能の読み込み
require_once plugin_dir_path(__FILE__) . 'includes/jsonld-output.php';
require_once plugin_dir_path(__FILE__) . 'includes/jsonld-control.php';
require_once plugin_dir_path(__FILE__) . 'includes/custom-fields-description.php';
require_once plugin_dir_path(__FILE__) . 'includes/jsonld-description.php';
require_once plugin_dir_path(__FILE__) . 'includes/jsonld-breadcrumbs.php';
require_once plugin_dir_path(__FILE__) . 'includes/jsonld-unified.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';

// ユーザー登録リンクを追加する
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'syntaxhub_jld_add_custom_plugin_link');
function syntaxhub_jld_add_custom_plugin_link($links) {
    $custom_link = '<a href="//syn-c.jp/sign-up/syntaxhub-jld/" target="_blank">' . esc_html__('Signup/ユーザー登録', 'syntaxhub-jld') . '</a>';
    array_unshift($links, $custom_link); // 先頭に追加
    return $links;
}
