<?php
if (!defined('ABSPATH')) exit;

/**
 * All in One SEO の JSON-LD 出力をフック／フィルターで抑制
 */
function syntaxhub_jld_disable_aioseo_jsonld() {
    if (!function_exists('aioseo')) {
        return;
    }

    add_filter('aioseo_schema_disable', 'syntaxhub_jld_aioseo_schema_disable', 100);
    add_filter('aioseo_schema_graphs', 'syntaxhub_jld_aioseo_schema_graphs', 100);
    add_filter('aioseo_schema_output', 'syntaxhub_jld_aioseo_schema_output', 100);
    add_filter('aioseo_meta_views', 'syntaxhub_jld_aioseo_remove_schema_view', 100);
}

/**
 * AIOSEO のスキーマ出力を無効化
 *
 * @param bool $disabled
 * @return bool
 */
function syntaxhub_jld_aioseo_schema_disable($disabled) {
    return true;
}

/**
 * AIOSEO のスキーマグラフ一覧を空にする
 *
 * @param array $graphs
 * @return array
 */
function syntaxhub_jld_aioseo_schema_graphs($graphs) {
    return array();
}

/**
 * AIOSEO の @graph 出力を空にする
 *
 * @param array $graph
 * @return array
 */
function syntaxhub_jld_aioseo_schema_output($graph) {
    return array();
}

/**
 * AIOSEO の wp_head 出力から schema ビューを除外
 *
 * @param array $views
 * @return array
 */
function syntaxhub_jld_aioseo_remove_schema_view($views) {
    unset($views['schema']);

    return $views;
}

/**
 * Yoast SEO の JSON-LD 出力をフック／フィルターで抑制
 */
function syntaxhub_jld_disable_yoast_jsonld() {
    if (!defined('WPSEO_VERSION')) {
        return;
    }

    add_filter('wpseo_json_ld_output', 'syntaxhub_jld_yoast_disable_json_ld_output', 100);
    add_filter('wpseo_schema_graph_pieces', 'syntaxhub_jld_yoast_schema_graph_pieces', 100);
    add_filter('wpseo_schema_graph', 'syntaxhub_jld_yoast_schema_graph', 100);
    add_filter('wpseo_frontend_presenter_classes', 'syntaxhub_jld_yoast_remove_schema_presenter_class', 100, 2);
    add_filter('wpseo_frontend_presenters', 'syntaxhub_jld_yoast_remove_schema_presenter', 100, 2);

    $yoast_schema_needs_filters = array(
        'wpseo_schema_needs_breadcrumb',
        'wpseo_schema_needs_webpage',
        'wpseo_schema_needs_article',
        'wpseo_schema_needs_organization',
        'wpseo_schema_needs_website',
        'wpseo_schema_needs_person',
        'wpseo_schema_needs_main_image',
        'wpseo_schema_needs_author',
    );

    foreach ($yoast_schema_needs_filters as $filter_name) {
        add_filter($filter_name, '__return_false', 100);
    }
}

/**
 * Yoast SEO の JSON-LD 出力を無効化
 *
 * @param mixed $data
 * @return false
 */
function syntaxhub_jld_yoast_disable_json_ld_output($data) {
    return false;
}

/**
 * Yoast SEO のスキーマ graph pieces を空にする
 *
 * @param array $pieces
 * @param mixed $context
 * @return array
 */
function syntaxhub_jld_yoast_schema_graph_pieces($pieces, $context) {
    return array();
}

/**
 * Yoast SEO の @graph 出力を空にする
 *
 * @param array $graph
 * @param mixed $context
 * @return array
 */
function syntaxhub_jld_yoast_schema_graph($graph, $context) {
    return array();
}

/**
 * Yoast SEO の Schema presenter クラスを除外
 *
 * @param array  $presenters
 * @param string $page_type
 * @return array
 */
function syntaxhub_jld_yoast_remove_schema_presenter_class($presenters, $page_type) {
    return array_values(array_diff($presenters, array('Schema')));
}

/**
 * Yoast SEO の Schema presenter インスタンスを除外
 *
 * @param array $presenters
 * @param mixed $context
 * @return array
 */
function syntaxhub_jld_yoast_remove_schema_presenter($presenters, $context) {
    return array_values(array_filter($presenters, function ($presenter) {
        return !is_a($presenter, 'Yoast\\WP\\SEO\\Presenters\\Schema_Presenter');
    }));
}

add_action('plugins_loaded', 'syntaxhub_jld_disable_aioseo_jsonld', 20);
add_action('plugins_loaded', 'syntaxhub_jld_disable_yoast_jsonld', 20);

/**
 * wp_head からの shortlink 出力を抑制する。
 *
 * ファイル読み込み時に無条件で remove_action するのではなく init フックで実行し、
 * `syntaxhub_jld_disable_shortlink` フィルターで個別に無効化できるようにする。
 */
function syntaxhub_jld_maybe_remove_shortlink() {
    /**
     * shortlink の出力抑制を行うかどうか。
     *
     * @param bool $disable デフォルト true（抑制する）
     */
    if (apply_filters('syntaxhub_jld_disable_shortlink', true)) {
        remove_action('wp_head', 'wp_shortlink_wp_head');
    }
}
add_action('init', 'syntaxhub_jld_maybe_remove_shortlink');
