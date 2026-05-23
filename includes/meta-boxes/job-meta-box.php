<?php

/**
 * 求人情報メタボックス
 *
 * 役割:
 * - 求人情報カスタム投稿に専用入力欄を追加する
 * - 入力された求人メタ情報を保存する
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 求人情報用のメタボックスを追加
 */
function lgjh_add_job_meta_box()
{
    add_meta_box(
        'lgjh_job_meta_box',
        '求人詳細情報',
        'lgjh_render_job_meta_box',
        'lg_job',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'lgjh_add_job_meta_box');

/**
 * メタボックスの中身を表示
 */
function lgjh_render_job_meta_box($post)
{
    $company_name    = get_post_meta($post->ID, '_lgjh_company_name', true);
    $location        = get_post_meta($post->ID, '_lgjh_location', true);
    $job_url         = get_post_meta($post->ID, '_lgjh_job_url', true);
    $job_number      = get_post_meta($post->ID, '_lgjh_job_number', true);
    $description     = get_post_meta($post->ID, '_lgjh_description', true);
    $salary          = get_post_meta($post->ID, '_lgjh_salary', true);
    $employment_type = get_post_meta($post->ID, '_lgjh_employment_type', true);
    $status          = get_post_meta($post->ID, '_lgjh_status', true);
    $contact_person  = get_post_meta($post->ID, '_lgjh_contact_person', true);
    $contact_email   = get_post_meta($post->ID, '_lgjh_contact_email', true);
    $private_memo    = get_post_meta($post->ID, '_lgjh_private_memo', true);

    wp_nonce_field('lgjh_save_job_meta', 'lgjh_job_meta_nonce');
?>

    <p>
        <label for="lgjh_company_name">会社名</label><br>
        <input
            type="text"
            id="lgjh_company_name"
            name="lgjh_company_name"
            value="<?php echo esc_attr($company_name); ?>"
            style="width: 100%;">
    </p>

    <p>
        <label for="lgjh_location">勤務地</label><br>
        <input
            type="text"
            id="lgjh_location"
            name="lgjh_location"
            value="<?php echo esc_attr($location); ?>"
            style="width: 100%;">
    </p>

    <p>
        <label for="lgjh_job_url">求人URL</label><br>
        <input
            type="url"
            id="lgjh_job_url"
            name="lgjh_job_url"
            value="<?php echo esc_url($job_url); ?>"
            style="width: 100%;">
    </p>

    <p>
        <label for="lgjh_job_number">求人番号</label><br>
        <input
            type="text"
            id="lgjh_job_number"
            name="lgjh_job_number"
            value="<?php echo esc_attr($job_number); ?>"
            style="width: 100%;">
    </p>

    <p>
        <label for="lgjh_salary">給与</label><br>
        <input
            type="text"
            id="lgjh_salary"
            name="lgjh_salary"
            value="<?php echo esc_attr($salary); ?>"
            style="width: 100%;">
    </p>

    <p>
        <label for="lgjh_employment_type">雇用形態</label><br>
        <input
            type="text"
            id="lgjh_employment_type"
            name="lgjh_employment_type"
            value="<?php echo esc_attr($employment_type); ?>"
            style="width: 100%;">
    </p>

    <p>
        <label for="lgjh_description">仕事内容</label><br>
        <textarea
            id="lgjh_description"
            name="lgjh_description"
            rows="6"
            style="width: 100%;"><?php echo esc_textarea($description); ?></textarea>
    </p>

    <p>
        <label for="lgjh_status">応募ステータス</label><br>
        <select id="lgjh_status" name="lgjh_status">
            <option value="not_applied" <?php selected($status, 'not_applied'); ?>>未応募</option>
            <option value="considering" <?php selected($status, 'considering'); ?>>検討中</option>
            <option value="applied" <?php selected($status, 'applied'); ?>>応募済み</option>
            <option value="rejected" <?php selected($status, 'rejected'); ?>>見送り</option>
            <option value="closed" <?php selected($status, 'closed'); ?>>募集終了</option>
        </select>
    </p>

    <p>
        <label for="lgjh_contact_person">担当者名</label><br>
        <input
            type="text"
            id="lgjh_contact_person"
            name="lgjh_contact_person"
            value="<?php echo esc_attr($contact_person); ?>"
            style="width: 100%;">
    </p>

    <p>
        <label for="lgjh_contact_email">メールアドレス</label><br>
        <input
            type="email"
            id="lgjh_contact_email"
            name="lgjh_contact_email"
            value="<?php echo esc_attr($contact_email); ?>"
            style="width: 100%;">
    </p>

    <p>
        <label for="lgjh_private_memo">俺用一行メモ</label><br>
        <input
            type="text"
            id="lgjh_private_memo"
            name="lgjh_private_memo"
            value="<?php echo esc_attr($private_memo); ?>"
            style="width: 100%;">
    </p>

<?php
}

/**
 * 求人メタ情報を保存
 */
function lgjh_save_job_meta($post_id)
{
    if (!isset($_POST['lgjh_job_meta_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['lgjh_job_meta_nonce'], 'lgjh_save_job_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['lgjh_company_name'])) {
        update_post_meta(
            $post_id,
            '_lgjh_company_name',
            sanitize_text_field($_POST['lgjh_company_name'])
        );
    }

    if (isset($_POST['lgjh_location'])) {
        update_post_meta(
            $post_id,
            '_lgjh_location',
            sanitize_text_field($_POST['lgjh_location'])
        );
    }

    if (isset($_POST['lgjh_job_url'])) {
        update_post_meta(
            $post_id,
            '_lgjh_job_url',
            esc_url_raw($_POST['lgjh_job_url'])
        );
    }

    if (isset($_POST['lgjh_job_number'])) {
        update_post_meta(
            $post_id,
            '_lgjh_job_number',
            sanitize_text_field($_POST['lgjh_job_number'])
        );
    }

    if (isset($_POST['lgjh_salary'])) {
        update_post_meta(
            $post_id,
            '_lgjh_salary',
            sanitize_text_field($_POST['lgjh_salary'])
        );
    }

    if (isset($_POST['lgjh_employment_type'])) {
        update_post_meta(
            $post_id,
            '_lgjh_employment_type',
            sanitize_text_field($_POST['lgjh_employment_type'])
        );
    }

    if (isset($_POST['lgjh_description'])) {
        update_post_meta(
            $post_id,
            '_lgjh_description',
            sanitize_textarea_field($_POST['lgjh_description'])
        );
    }

    if (isset($_POST['lgjh_status'])) {
        update_post_meta(
            $post_id,
            '_lgjh_status',
            sanitize_text_field($_POST['lgjh_status'])
        );
    }

    if (isset($_POST['lgjh_contact_person'])) {
        update_post_meta(
            $post_id,
            '_lgjh_contact_person',
            sanitize_text_field($_POST['lgjh_contact_person'])
        );
    }

    if (isset($_POST['lgjh_contact_email'])) {
        update_post_meta(
            $post_id,
            '_lgjh_contact_email',
            sanitize_email($_POST['lgjh_contact_email'])
        );
    }

    if (isset($_POST['lgjh_private_memo'])) {
        update_post_meta(
            $post_id,
            '_lgjh_private_memo',
            sanitize_text_field($_POST['lgjh_private_memo'])
        );
    }
}
add_action('save_post_lg_job', 'lgjh_save_job_meta');
