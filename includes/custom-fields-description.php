<?php
/**
 * カスタムフィールドからdescriptionを取得する機能
 */

if (!defined('ABSPATH')) exit;

// カスタムフィールドのメタボックスを追加（設定が「custom」の場合のみ）
add_action('add_meta_boxes', 'syntaxhub_jld_add_custom_description_meta_box');
function syntaxhub_jld_add_custom_description_meta_box() {
    // 設定を確認
    $description_method = get_option('syntaxhub_jld_description_method', 'aioseo');

    // カスタムフィールドが選択されている場合のみメタボックスを表示
    if ($description_method === 'custom') {
        add_meta_box(
            'custom_description_meta_box',
            'sHub-jld Description',
            'syntaxhub_jld_custom_description_meta_box_callback',
            'page',
            'normal',
            'high'
        );
    }
}

// メタボックスの内容
function syntaxhub_jld_custom_description_meta_box_callback($post) {
    wp_nonce_field('custom_description_meta_box', 'custom_description_meta_box_nonce');

    $description = get_post_meta($post->ID, '_custom_description', true);
    ?>
    <p>
        <label for="custom_description">ページの説明文（JSON-LD用）:</label><br>
        <textarea id="custom_description" name="custom_description" rows="3" cols="50" style="width: 100%;"><?php echo esc_textarea($description); ?></textarea>
        <br><small>この説明文はJSON-LDのdescriptionとして使用されます。</small>
    </p>
    <?php
}

// カスタムフィールドの保存
add_action('save_post', 'syntaxhub_jld_save_custom_description_meta_box');
function syntaxhub_jld_save_custom_description_meta_box($post_id) {
    // 設定を確認
    $description_method = get_option('syntaxhub_jld_description_method', 'aioseo');

    // カスタムフィールドが選択されていない場合は保存しない
    if ($description_method !== 'custom') {
        return;
    }

    // セキュリティチェック（nonce は $_POST から取り出す前に unslash / sanitize する）
    if (!isset($_POST['custom_description_meta_box_nonce'])) {
        return;
    }

    $nonce = sanitize_text_field(wp_unslash($_POST['custom_description_meta_box_nonce']));
    if (!wp_verify_nonce($nonce, 'custom_description_meta_box')) {
        return;
    }

    // 自動保存の場合は保存しない
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // 権限チェック
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // カスタムフィールドの保存（unslash してから sanitize する）
    if (isset($_POST['custom_description'])) {
        $custom_description = sanitize_textarea_field(wp_unslash($_POST['custom_description']));
        update_post_meta($post_id, '_custom_description', $custom_description);
    }
}

/**
 * カスタムフィールドからdescriptionを取得
 */
function syntaxhub_jld_get_custom_description($post_id) {
    // 設定を確認
    $description_method = get_option('syntaxhub_jld_description_method', 'aioseo');

    // カスタムフィールドが選択されている場合のみ取得
    if ($description_method === 'custom') {
        return get_post_meta($post_id, '_custom_description', true);
    }

    return '';
}
