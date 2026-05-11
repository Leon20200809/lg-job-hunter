<?php

/**
 * 求人情報の保存処理
 *
 * 役割:
 * - 配列で受け取った求人データを lg_job 投稿として保存する
 * - 求人URLを使って重複登録を防ぐ
 * - 投稿本体とメタ情報の保存を担当する
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 求人URLから既存の求人投稿IDを探す
 *
 * @param string $job_url 求人URL
 * @return int 既存投稿ID。なければ 0
 */
function lgjh_find_job_by_url($job_url)
{
    if (empty($job_url)) {
        return 0;
    }

    $query = new WP_Query([
        'post_type'      => 'lg_job',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'   => '_lgjh_job_url',
                'value' => esc_url_raw($job_url),
            ],
        ],
    ]);

    if (!empty($query->posts)) {
        return (int) $query->posts[0];
    }

    return 0;
}

/**
 * 求人データを保存する
 *
 * @param array $job_data 求人データ
 * @return int|WP_Error 保存した投稿ID。失敗時は WP_Error
 */
function lgjh_save_job_from_data($job_data)
{
    $job_title       = $job_data['job_title'] ?? '';
    $company_name    = $job_data['company_name'] ?? '';
    $location        = $job_data['location'] ?? '';
    $job_url         = $job_data['job_url'] ?? '';
    $job_number      = $job_data['job_number'] ?? '';
    $description     = $job_data['description'] ?? '';
    $salary          = $job_data['salary'] ?? '';
    $employment_type = $job_data['employment_type'] ?? '';
    $status          = $job_data['status'] ?? 'not_applied';

    if (empty($job_title)) {
        return new WP_Error('lgjh_empty_job_title', '求人タイトルが空です。');
    }

    if (empty($job_url)) {
        return new WP_Error('lgjh_empty_job_url', '求人URLが空です。');
    }

    $allowed_statuses = [
        'not_applied',
        'considering',
        'applied',
        'rejected',
        'closed',
    ];

    if (!in_array($status, $allowed_statuses, true)) {
        $status = 'not_applied';
    }

    // 重複チェック
    $existing_post_id = lgjh_find_job_by_url($job_url);

    if ($existing_post_id) {
        return $existing_post_id;
    }

    $post_id = wp_insert_post([
        'post_type'   => 'lg_job',
        'post_status' => 'publish',
        'post_title'  => sanitize_text_field($job_title),
    ], true);

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    update_post_meta($post_id, '_lgjh_company_name', sanitize_text_field($company_name));
    update_post_meta($post_id, '_lgjh_location', sanitize_text_field($location));
    update_post_meta($post_id, '_lgjh_job_url', esc_url_raw($job_url));
    update_post_meta($post_id, '_lgjh_job_number', sanitize_text_field($job_number));
    update_post_meta($post_id, '_lgjh_description', sanitize_textarea_field($description));
    update_post_meta($post_id, '_lgjh_salary', sanitize_text_field($salary));
    update_post_meta($post_id, '_lgjh_employment_type', sanitize_text_field($employment_type));
    update_post_meta($post_id, '_lgjh_status', sanitize_text_field($status));

    return $post_id;
}
