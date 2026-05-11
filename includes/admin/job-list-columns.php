<?php

/**
 * 求人情報一覧画面のカラム設定
 *
 * 役割:
 * - 管理画面の求人一覧に会社名・勤務地・応募ステータス・求人URLを表示する
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 求人情報一覧に表示するカラムを追加
 */
function lgjh_add_job_list_columns($columns)
{
    $new_columns = [];

    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = '求人タイトル';
    $new_columns['job_number'] = '求人番号';
    $new_columns['company_name'] = '会社名';
    $new_columns['location'] = '勤務地';
    $new_columns['salary'] = '給与';
    $new_columns['status'] = '応募ステータス';
    $new_columns['job_url'] = '求人URL';
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}
add_filter('manage_lg_job_posts_columns', 'lgjh_add_job_list_columns');

/**
 * 追加したカラムに値を表示
 */
function lgjh_render_job_list_columns($column_name, $post_id)
{
    if ($column_name === 'job_number') {
        echo esc_html(get_post_meta($post_id, '_lgjh_job_number', true));
    }

    if ($column_name === 'company_name') {
        echo esc_html(get_post_meta($post_id, '_lgjh_company_name', true));
    }

    if ($column_name === 'location') {
        echo esc_html(get_post_meta($post_id, '_lgjh_location', true));
    }

    if ($column_name === 'salary') {
        echo esc_html(get_post_meta($post_id, '_lgjh_salary', true));
    }

    if ($column_name === 'status') {
        $status = get_post_meta($post_id, '_lgjh_status', true);

        $status_labels = [
            'not_applied' => '未応募',
            'considering' => '検討中',
            'applied' => '応募済み',
            'rejected' => '見送り',
            'closed' => '募集終了',
        ];

        echo esc_html($status_labels[$status] ?? '未設定');
    }

    if ($column_name === 'job_url') {
        $job_url = get_post_meta($post_id, '_lgjh_job_url', true);

        if (!empty($job_url)) {
            echo '<a href="' . esc_url($job_url) . '" target="_blank" rel="noopener noreferrer">開く</a>';
        }
    }
}
add_action('manage_lg_job_posts_custom_column', 'lgjh_render_job_list_columns', 10, 2);
