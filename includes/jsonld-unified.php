<?php
if (!defined('ABSPATH')) exit;

/**
 * BreadcrumbList と WebPage を @graph で統合した JSON-LD を1つの script タグで出力
 */
add_action('wp_head', function () {
    $graph = array();

    $breadcrumb_data = syntaxhub_jld_get_breadcrumb_data();
    if ($breadcrumb_data) {
        $graph[] = $breadcrumb_data;
    }

    $webpage_data = syntaxhub_jld_get_webpage_data();
    if ($webpage_data) {
        $graph[] = $webpage_data;
    }

    if (empty($graph)) {
        return;
    }

    syntaxhub_jld_print_json_ld_script(array(
        '@context' => 'https://schema.org',
        '@graph'   => $graph,
    ));
});
