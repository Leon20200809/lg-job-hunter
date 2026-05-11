<?php
/**
 * 外部HTML取得処理
 *
 * 役割:
 * - 指定されたURLからHTMLを取得する
 * - WordPress標準のHTTP APIである wp_remote_get() を使う
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 指定URLからHTMLを取得する
 *
 * @param string $url 取得対象URL
 * @return string|WP_Error HTML文字列。失敗時は WP_Error
 */
function lgjh_fetch_html($url)
{
    if (empty($url)) {
        return new WP_Error('lgjh_empty_url', 'URLが空です。');
    }

    $url = esc_url_raw($url);

    $response = wp_remote_get($url, [
        'timeout' => 15,
        'headers' => [
            'User-Agent' => 'LG Job Hunter/0.1.0; WordPress Plugin',
        ],
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code !== 200) {
        return new WP_Error(
            'lgjh_invalid_status_code',
            'HTML取得に失敗しました。HTTPステータス: ' . $status_code
        );
    }

    $html = wp_remote_retrieve_body($response);

    if (empty($html)) {
        return new WP_Error('lgjh_empty_response_body', '取得したHTMLが空です。');
    }

    return $html;
}