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

    // Gmail本文の改行を保持したいので esc_attr() を使う
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
    $company_name    = get_post_meta($post_id, '_lgjh_company_name', true);
    $job_url         = get_post_meta($post_id, '_lgjh_job_url', true);
    $contact_email   = get_post_meta($post_id, '_lgjh_contact_email', true);
    $contact_person  = get_post_meta($post_id, '_lgjh_contact_person', true);
    $job_title       = get_the_title($post_id);
    $resume_url      = 'https://lazygenius-web-resume.vercel.app/';

    if (empty($contact_person)) {
        $contact_person = 'ご担当者様';
    }

    $to = sanitize_email($contact_email);

    $subject = '求人応募についてのご連絡：' . $company_name;
    $body    = lgjh_build_apply_mail_body($company_name, $job_title, $job_url, $resume_url, $contact_person);

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
 * @param string $resume_url Web履歴書URL
 * @param string $contact_person 担当者名
 * @return string 応募メール本文
 */
function lgjh_build_apply_mail_body($company_name, $job_title, $job_url, $resume_url, $contact_person)
{
    $contact_label = $contact_person;

    if (empty($contact_label)) {
        $contact_label = '採用ご担当者様';
    }

    if ($contact_label !== '採用ご担当者様') {
        $contact_label .= ' 様';
    }

    $lines = [
        $company_name,
        $contact_label,
        '',
        'はじめまして。',
        "「{$job_title}」の求人をハローワーク梅田より拝見し、応募いたします。",
        '',
        'PHP・WordPressを中心に学習・制作を進める中で、Web制作やシステム開発を通じてお客様の課題解決に関わりたいと考え、応募いたしました。',
        '',
        '今回の求人は、自作の求人管理プラグインの新着通知を通じて確認いたしました。',
        '職務経歴・制作物・GitHub等は、以下のWebレジュメにまとめております。',
        'Webレジュメ内では、A4形式の履歴書・職務経歴書もダウンロードできます。',
        '',
        'Webレジュメ：',
        $resume_url,
        '',
        '認証パスワード：',
        '※送信前に入力してください',
        '',
        '求人詳細URL：',
        $job_url,
        '',
        'ハローワーク紹介状を添付いたします。',
        'ご確認のほど、よろしくお願いいたします。',
        '',
        '熊谷 玲央',
    ];

    return implode("\r\n", $lines);
}
