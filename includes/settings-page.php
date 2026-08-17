<?php
/**
 * sHub-jld 設定ページ
 */

if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'syntaxhub_jld_settings_menu');
add_action('admin_init', 'syntaxhub_jld_register_settings');

function syntaxhub_jld_settings_menu() {
    add_options_page(
        'sHub-jld Settings',
        'sHub-jld',
        'manage_options',
        'syntaxhub-jld-settings',
        'syntaxhub_jld_settings_page'
    );
}

function syntaxhub_jld_register_settings() {
    register_setting(
        'syntaxhub_jld_settings',
        'syntaxhub_jld_description_method',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'syntaxhub_jld_sanitize_description_method',
            'default'           => 'aioseo',
        )
    );

    register_setting(
        'syntaxhub_jld_settings',
        'syntaxhub_jld_auto_extract',
        array(
            'type'              => 'boolean',
            'sanitize_callback' => 'syntaxhub_jld_sanitize_auto_extract',
            'default'           => true,
        )
    );

    add_settings_section(
        'syntaxhub_jld_main_section',
        '',
        '__return_false',
        'syntaxhub-jld-settings'
    );

    add_settings_field(
        'syntaxhub_jld_description_method',
        'Description取得方法',
        'syntaxhub_jld_render_description_method_field',
        'syntaxhub-jld-settings',
        'syntaxhub_jld_main_section'
    );

    add_settings_field(
        'syntaxhub_jld_auto_extract',
        '自動抽出の詳細設定',
        'syntaxhub_jld_render_auto_extract_field',
        'syntaxhub-jld-settings',
        'syntaxhub_jld_main_section'
    );
}

function syntaxhub_jld_sanitize_description_method($value) {
    if ($value === 'seo_plugin') {
        $value = 'aioseo';
    }

    $allowed = array('aioseo', 'auto', 'custom');
    return in_array($value, $allowed, true) ? $value : 'aioseo';
}

function syntaxhub_jld_sanitize_auto_extract($value) {
    return !empty($value) ? 1 : 0;
}

function syntaxhub_jld_render_description_method_field() {
    $current_method = get_option('syntaxhub_jld_description_method', 'aioseo');
    ?>
    <fieldset>
        <label>
            <input type="radio" name="syntaxhub_jld_description_method" value="aioseo" <?php checked($current_method, 'aioseo'); ?>>
            SEOプラグインの設定を優先（推奨）
        </label><br>
        <label>
            <input type="radio" name="syntaxhub_jld_description_method" value="auto" <?php checked($current_method, 'auto'); ?>>
            テーマファイルから自動抽出
        </label><br>
        <label>
            <input type="radio" name="syntaxhub_jld_description_method" value="custom" <?php checked($current_method, 'custom'); ?>>
            カスタムフィールドを使用
        </label>
    </fieldset>
    <p class="description">
        <strong>SEOプラグインの設定を優先</strong>: AIOSEO や Yoast SEO などで設定した description を使用します。<br>
        <strong>テーマファイルから自動抽出</strong>: 固定ページのテーマファイルからコンテンツを自動抽出します。<br>
        <strong>カスタムフィールドを使用</strong>: 各ページで手動でdescriptionを設定します。
    </p>
    <?php
}

function syntaxhub_jld_render_auto_extract_field() {
    $auto_extract = get_option('syntaxhub_jld_auto_extract', 1);
    ?>
    <input type="hidden" name="syntaxhub_jld_auto_extract" value="0">
    <label>
        <input type="checkbox" name="syntaxhub_jld_auto_extract" value="1" <?php checked($auto_extract, 1); ?>>
        SEOプラグインの設定がない場合、自動抽出を試行する
    </label>
    <p class="description">
        このオプションを有効にすると、SEOプラグインで description が設定されていない場合に、テーマファイルからの自動抽出を試行します。
    </p>
    <?php
}

function syntaxhub_jld_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('syntaxhub_jld_settings');
            do_settings_sections('syntaxhub-jld-settings');
            submit_button();
            ?>
        </form>

        <h2>使用方法</h2>
        <div class="card">
            <h3>推奨設定</h3>
            <p><strong>「SEOプラグインの設定を優先」</strong>を選択し、各ページの SEO プラグイン設定で description を入力してください。</p>

            <h3>自動抽出について</h3>
            <p>テーマファイルから自動抽出する場合、以下のファイルパターンからコンテンツを抽出します：</p>
            <ul>
                <li><code>page-{スラッグ}.php</code></li>
                <li><code>page-{ID}.php</code></li>
                <li><code>template-parts/page-{スラッグ}.php</code></li>
                <li><code>parts/page-{スラッグ}.php</code></li>
            </ul>

            <h3>カスタムフィールドについて</h3>
            <p>カスタムフィールドを使用する場合、各固定ページの編集画面に「sHub-jld Description」の入力欄が表示されます。</p>
        </div>
    </div>
    <?php
}
