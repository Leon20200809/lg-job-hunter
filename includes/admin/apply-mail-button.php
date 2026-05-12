<?php

/**
 * 応募用Gmail作成ボタン関連
 *
 * 求人一覧の「応募する」ボタンHTMLと、
 * Gmail作成画面を開くためのURL・本文を生成する。
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 応募用のGmail作成ボタンHTMLを生成
 *
 * @param int $post_id 求人投稿ID
 * @return string 応募ボタンHTML
 */
function lgjh_get_apply_button_html($post_id)
{
    $gmail_url = lgjh_build_gmail_apply_url($post_id);

    return '<a class="button button-primary" href="' . esc_attr($gmail_url) . '" target="_blank" rel="noopener noreferrer">応募する</a>';
}

/**
 * Gmail作成画面を開くURLを生成
 *
 * @param int $post_id 求人投稿ID
 * @return string Gmail作成URL
 */
function lgjh_build_gmail_apply_url($post_id)
{
    $company_name = get_post_meta($post_id, '_lgjh_company_name', true);
    $job_url      = get_post_meta($post_id, '_lgjh_job_url', true);
    $job_title    = get_the_title($post_id);

    $to = '';

    $subject = '求人応募についてのご連絡' . $company_name;
    $body    = lgjh_build_apply_mail_body($company_name, $job_title, $job_url);

    $gmail_url = 'https://mail.google.com/mail/?view=cm&fs=1'
        . '&to=' . rawurlencode($to)
        . '&su=' . rawurlencode($subject)
        . '&body=' . rawurlencode($body);

    return $gmail_url;
}

/**
 * 応募メール本文を生成
 *
 * @param string $company_name 会社名
 * @param string $job_title 求人タイトル
 * @param string $job_url 求人URL
 * @return string 応募メール本文
 */
function lgjh_build_apply_mail_body($company_name, $job_title, $job_url)
{
    $lines = [
        "{$company_name} 採用ご担当者様",
        "",
        "お世話になっております。",
        "求人情報を拝見し、応募させていただきたくご連絡いたしました。",
        "",
        "【応募求人】",
        $job_title,
        "",
        "【求人URL】",
        $job_url,
        "",
        "何卒よろしくお願いいたします。",
    ];

    return implode("\r\n", $lines);
}