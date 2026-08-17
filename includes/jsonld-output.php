<?php
if (!defined('ABSPATH')) exit;

/**
 * JSON-LD 用の script タグ HTML を安全に生成する。
 *
 * WP 7.0 以降は wp_get_inline_script_tag() の利用が推奨されるが、
 * 現時点では application/ld+json を認識しないため、空文字が返る。
 * その場合は wp_json_encode() によるエスケープ付きのフォールバック出力を行う。
 *
 * @param array $data JSON-LD データ
 * @return string script タグ HTML。生成失敗時は空文字
 */
function syntaxhub_jld_get_json_ld_script_tag($data) {
    $json = wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    if (false === $json) {
        return '';
    }

    if (function_exists('wp_get_inline_script_tag')) {
        $tag = wp_get_inline_script_tag($json, array('type' => 'application/ld+json'));
        if ('' !== $tag) {
            return $tag;
        }
    }

    return sprintf('<script type="application/ld+json">%s</script>', $json);
}

/**
 * JSON-LD 用の script タグを出力する。
 *
 * @param array $data JSON-LD データ
 */
function syntaxhub_jld_print_json_ld_script($data) {
    $tag = syntaxhub_jld_get_json_ld_script_tag($data);
    if ('' !== $tag) {
        echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in syntaxhub_jld_get_json_ld_script_tag().
    }
}
