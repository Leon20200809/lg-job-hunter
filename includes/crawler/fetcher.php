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

/**
 * ハローワーク検索ページを初期化してCookieを取得
 *
 * @return array|WP_Error Cookie配列
 */
function lgjh_fetch_hellowork_init_cookies()
{
    $init_url = 'https://www.hellowork.mhlw.go.jp/kensaku/GECA110010.do?screenId=GECA110010&action=searchBtn&initDisp=&searchClear=1';

    $response = wp_remote_get($init_url, [
        'timeout' => 20,
        'headers' => [
            'User-Agent' => 'LG Job Hunter/0.1.0; WordPress Plugin',
        ],
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $html = wp_remote_retrieve_body($response);

    file_put_contents(
        LG_JOB_HUNTER_PATH . '/dev-samples/debug-hellowork-init.html',
        $html
    );

    return wp_remote_retrieve_cookies($response);
}

/**
 * ハローワーク検索結果HTMLを固定条件で取得する
 *
 * 役割:
 * - ハローワークの検索フォーム送信を wp_remote_post() で再現する
 * - 検索結果HTMLを取得する
 *
 * @return string|WP_Error 検索結果HTML。失敗時は WP_Error
 */
function lgjh_fetch_hellowork_search_result_html()
{
    $conditions = lgjh_get_search_conditions();
    $keyword_for_hellowork = lgjh_convert_keyword_for_hellowork(
        $conditions['keyword'] ?? ''
    );

    $search_url = 'https://www.hellowork.mhlw.go.jp/kensaku/GECA110010.do';

    $cookies = lgjh_fetch_hellowork_init_cookies();

    if (is_wp_error($cookies)) {
        return $cookies;
    }

    $body = lgjh_build_hellowork_search_body($conditions);

    $response = wp_remote_post($search_url, [
        'timeout' => 20,
        'cookies' => $cookies,
        'headers' => [
            'User-Agent' => 'LG Job Hunter/0.1.0; WordPress Plugin',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'body' => $body,
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code !== 200) {
        return new WP_Error(
            'lgjh_search_invalid_status_code',
            '検索結果HTML取得に失敗しました。HTTPステータス: ' . $status_code
        );
    }

    $html = wp_remote_retrieve_body($response);

    file_put_contents(
        LG_JOB_HUNTER_PATH . '/dev-samples/debug-hellowork-search-result.html',
        $html
    );

    if (empty($html)) {
        return new WP_Error(
            'lgjh_search_empty_response_body',
            '取得した検索結果HTMLが空です。'
        );
    }

    return $html;
}
