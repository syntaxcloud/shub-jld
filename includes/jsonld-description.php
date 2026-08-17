<?php
if (!defined('ABSPATH')) exit;

/**
 * WebPage スキーマ用の JSON-LD データを取得
 * @return array|null WebPage スキーマの配列、出力対象外の場合は null
 */
function syntaxhub_jld_get_webpage_data() {
    $url = '';
    $name = '';
    $description = '';

    if (is_singular() || is_page()) {
        $url = get_permalink();
        $name = syntaxhub_jld_sanitize_schema_text(get_the_title());
        // 設定を取得して description を構築
        $description = syntaxhub_jld_get_description_for_post();
    } elseif (is_category()) {
        $current_category = get_queried_object();
        $url = get_category_link($current_category->term_id);
        $name = syntaxhub_jld_sanitize_schema_text($current_category->name);
        $description = !empty($current_category->description)
            ? $current_category->description
            : __('このページは、情報を提供しています。詳細は本文をご確認ください。', 'syntaxhub-jld');
    } else {
        return null;
    }

    return [
        '@type' => 'WebPage',
        'url' => $url,
        'name' => $name,
        'description' => $description,
        'mainEntityOfPage' => $url,
        'headline' => $name,
        'inLanguage' => 'ja',
        'isPartOf' => [
            '@type' => 'WebSite',
            'url' => home_url()
        ]
    ];
}

/**
 * JSON-LD のテキスト値から HTML タグと余分な空白を除去
 * @param string $text
 * @return string
 */
function syntaxhub_jld_sanitize_schema_text($text) {
    $text = (string) $text;
    $text = preg_replace('/<\s*br\s*\/?>/i', ' ', $text);
    $text = wp_strip_all_tags($text, true);
    $text = preg_replace('/\s+/u', ' ', trim($text));

    return $text;
}

/**
 * 投稿・ページ用の description を取得
 * @return string
 */
function syntaxhub_jld_get_description_for_post() {
    $description_method = get_option('syntaxhub_jld_description_method', 'aioseo');
    $auto_extract = get_option('syntaxhub_jld_auto_extract', 1);

    $description = syntaxhub_jld_get_seo_plugin_description();

    if (empty($description) && (is_singular() || is_page())) {
        global $post;

        switch ($description_method) {
            case 'auto':
                $description = syntaxhub_jld_get_auto_extracted_description($post);
                break;

            case 'custom':
                $custom_description = syntaxhub_jld_get_custom_description($post->ID);
                if (!empty($custom_description)) {
                    $description = $custom_description;
                } else {
                    $description = syntaxhub_jld_get_auto_extracted_description($post);
                }
                break;

            case 'aioseo':
            default:
                if ($auto_extract) {
                    $description = syntaxhub_jld_get_auto_extracted_description($post);
                }
                break;
        }
    }

    if (empty($description)) {
        $description = __('このページは、情報を提供しています。詳細は本文をご確認ください。', 'syntaxhub-jld');
    }

    return $description;
}

/**
 * インストール済み SEO プラグイン（AIOSEO / Yoast SEO）から description を取得
 *
 * @param WP_Post|null $post
 * @return string
 */
function syntaxhub_jld_get_seo_plugin_description($post = null) {
    if (!$post) {
        global $post;
    }

    $description = syntaxhub_jld_get_aioseo_description($post);
    if (!empty($description)) {
        return $description;
    }

    return syntaxhub_jld_get_yoast_description($post);
}

/**
 * AIOSEO から description を取得
 *
 * @param WP_Post|null $post
 * @return string
 */
function syntaxhub_jld_get_aioseo_description($post = null) {
    if (!function_exists('aioseo')) {
        return '';
    }

    $description = aioseo()->meta->description->getDescription();

    if (empty($description) && (is_singular() || is_page())) {
        if (!$post) {
            global $post;
        }

        $meta_data = aioseo()->meta->metaData->getMetaData($post);
        if (is_object($meta_data) && property_exists($meta_data, 'description') && !empty($meta_data->description)) {
            $description = $meta_data->description;
        }
    }

    if (empty($description) && $post instanceof WP_Post) {
        $description = get_post_meta($post->ID, '_aioseo_description', true);
    }

    return is_string($description) ? $description : '';
}

/**
 * Yoast SEO から description を取得
 *
 * @param WP_Post|null $post
 * @return string
 */
function syntaxhub_jld_get_yoast_description($post = null) {
    if (!defined('WPSEO_VERSION')) {
        return '';
    }

    if (!$post) {
        global $post;
    }

    if (!($post instanceof WP_Post)) {
        return '';
    }

    if (function_exists('YoastSEO')) {
        $meta = YoastSEO()->meta->for_post($post->ID);
        if ($meta && !empty($meta->description)) {
            return $meta->description;
        }
    }

    $description = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);

    return is_string($description) ? $description : '';
}

/**
 * 自動抽出によるdescription取得
 */
function syntaxhub_jld_get_auto_extracted_description($post) {
    $description = '';

    // 方法1: excerpt を試す
    $description = get_the_excerpt($post);

    // 方法2: post_content から取得
    if (empty($description)) {
        $content = $post->post_content;
        if (!empty($content)) {
            $clean_content = wp_strip_all_tags($content);
            $clean_content = preg_replace('/\s+/', ' ', trim($clean_content));
            $description = wp_trim_words($clean_content, 30, '...');
        }
    }

    // 方法3: テーマファイルからコンテンツを取得
    if (empty($description)) {
        $description = syntaxhub_jld_get_theme_content_description($post);
    }

    // 方法4: post_contentを直接取得
    if (empty($description) && !empty($post->post_content)) {
        $content = $post->post_content;
        $clean_content = wp_strip_all_tags($content);
        $clean_content = preg_replace('/\s+/', ' ', trim($clean_content));
        if (!empty($clean_content)) {
            $description = wp_trim_words($clean_content, 30, '...');
        }
    }

    return $description;
}

/**
 * テーマファイル探索の許可ディレクトリ（テーマ／子テーマのルート）を取得
 *
 * @return string[] realpath 済みのディレクトリ一覧
 */
function syntaxhub_jld_get_allowed_theme_dirs() {
    $dirs = array(get_template_directory(), get_stylesheet_directory());
    $allowed = array();

    foreach (array_unique($dirs) as $dir) {
        $real = realpath($dir);
        if (false !== $real) {
            $allowed[] = $real;
        }
    }

    return $allowed;
}

/**
 * 対象ファイルが許可されたテーマディレクトリ配下にあり、安全に読めるか検証
 *
 * @param string $file_path
 * @return bool
 */
function syntaxhub_jld_is_safe_theme_file($file_path) {
    if (!is_file($file_path) || !is_readable($file_path)) {
        return false;
    }

    $real = realpath($file_path);
    if (false === $real) {
        return false;
    }

    foreach (syntaxhub_jld_get_allowed_theme_dirs() as $allowed_dir) {
        // ディレクトリ区切りを正規化したうえで配下判定（パストラバーサル対策）
        $prefix = rtrim($allowed_dir, '/\\') . DIRECTORY_SEPARATOR;
        if (0 === strpos($real, $prefix)) {
            return true;
        }
    }

    return false;
}

/**
 * テーマファイルからコンテンツを取得してdescriptionを生成
 */
function syntaxhub_jld_get_theme_content_description($post) {
    // スラッグは WordPress 側で sanitize 済みだが、念のため英数・ハイフン等に限定
    $page_slug = sanitize_file_name($post->post_name);
    $page_id   = (int) $post->ID;

    $template_dir   = get_template_directory();
    $stylesheet_dir = get_stylesheet_directory();

    $relative_patterns = array();
    foreach (array($page_slug, $page_id) as $key) {
        if ('' === (string) $key) {
            continue;
        }
        $relative_patterns[] = 'page-' . $key . '.php';
        $relative_patterns[] = 'template-parts/page-' . $key . '.php';
        $relative_patterns[] = 'parts/page-' . $key . '.php';
        $relative_patterns[] = 'templates/page-' . $key . '.php';
    }

    foreach (array_unique(array($template_dir, $stylesheet_dir)) as $base_dir) {
        foreach ($relative_patterns as $relative) {
            $file_path = $base_dir . '/' . $relative;
            if (syntaxhub_jld_is_safe_theme_file($file_path)) {
                $content = syntaxhub_jld_get_file_content_description($file_path);
                if (!empty($content)) {
                    return $content;
                }
            }
        }
    }

    return '';
}

/**
 * ファイルからコンテンツを抽出してdescriptionを生成（mtime ベースでキャッシュ）
 */
function syntaxhub_jld_get_file_content_description($file_path) {
    if (!syntaxhub_jld_is_safe_theme_file($file_path)) {
        return '';
    }

    // ファイルの更新時刻を含むキーでキャッシュ。更新時のみ再計算する。
    $mtime     = (int) filemtime($file_path);
    $cache_key = 'shub_jld_desc_' . md5($file_path . '|' . $mtime);

    $cached = get_transient($cache_key);
    if (false !== $cached) {
        return $cached;
    }

    $content = file_get_contents($file_path);

    if (empty($content)) {
        set_transient($cache_key, '', DAY_IN_SECONDS);
        return '';
    }

    // PHP コードを除去する。
    // 閉じタグの無いモダンな PHP ファイルでもソースが露出しないよう、
    // 閉じタグが見つからない場合は文字列末尾まで除去する。
    $content = preg_replace('/<\?(?:php|=)?.*?(?:\?>|$)/s', '', $content);

    // HTMLタグを除去
    $content = wp_strip_all_tags($content);

    // 改行や余分な空白を整理
    $content = preg_replace('/\s+/', ' ', trim($content));

    $description = '';

    // 日本語の文章を抽出（ひらがな、カタカナ、漢字を含む部分）
    preg_match_all('/[ぁ-んァ-ヶ一-龯]{10,}/u', $content, $matches);

    if (!empty($matches[0])) {
        $japanese_text = implode(' ', $matches[0]);
        $description   = wp_trim_words($japanese_text, 30, '...');
    } elseif (strlen($content) > 50) {
        // 日本語が見つからない場合は、全体から抽出
        $description = wp_trim_words($content, 30, '...');
    }

    set_transient($cache_key, $description, DAY_IN_SECONDS);

    return $description;
}
