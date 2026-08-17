<?php
if (!defined('ABSPATH')) exit;

/**
 * BreadcrumbList スキーマ用の JSON-LD データを取得
 * @return array|null BreadcrumbList スキーマの配列、出力対象外の場合は null
 */
function syntaxhub_jld_get_breadcrumb_data() {
    $breadcrumbs = [['name' => __('HOME', 'syntaxhub-jld'), 'url' => home_url('/')]];
    if (is_page()) {
        global $post;
        array_map(function($ancestor) use (&$breadcrumbs) {
            $breadcrumbs[] = ['name' => strip_tags(get_the_title($ancestor)), 'url' => get_permalink($ancestor)];
        }, array_reverse(get_post_ancestors($post->ID)));
        $breadcrumbs[] = ['name' => strip_tags(get_the_title($post))];
    } elseif (is_single()) {
        $category = get_the_category();
        $category ? $breadcrumbs[] = ['name' => strip_tags($category[0]->name), 'url' => get_category_link($category[0]->term_id)] : '';
        $breadcrumbs[] = ['name' => strip_tags(get_the_title())];
    } elseif (is_category()) {
        $current_category = get_queried_object();
        $breadcrumbs[] = ['name' => strip_tags($current_category->name)];
    }

    return [
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(function($crumb, $index) use ($breadcrumbs) {
            return ($index + 1 === count($breadcrumbs))
                ? ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $crumb['name']]
                : ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $crumb['name'], 'item' => $crumb['url']];
        }, $breadcrumbs, array_keys($breadcrumbs))
    ];
}
