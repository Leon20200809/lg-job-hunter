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
    $url = 'https://www.hellowork.mhlw.go.jp/kensaku/GECA110010.do';

    $body = [
        // 求職番号
        'kSNoJo' => '',
        'kSNoGe' => '',

        // 求人区分
        'kjKbnRadioBtn' => '1',

        // 就業場所：大阪府
        'todohukenHidden' => '27',
        'ensenHidden' => '',
        'roudousijyoHidden' => '',

        // フリーワード
        'freeWordInput' => 'ＰＨＰ',
        'freeWordRadioBtn' => '0',
        'nOTKNSKFreeWordInput' => '',

        // 職種：IT・Web・エンジニア
        'daiEasyShokusyuBox' => '11',
        'easyShokusyuBox' => '1100',

        // 検索ボタン
        'searchBtn' => '検索する',

        // 表示・並び順
        'fwListNaviSort' => '1',
        'fwListNaviDisp' => '30',

        // 画面制御
        'screenId' => 'GECA110010',
        'action' => '',
        'searchClear' => '0',
        'summaryDisp' => 'true',
        'searchInitDisp' => '1',
        'preCheckFlg' => 'false',
    ];

    $response = wp_remote_post($url, [
        'timeout' => 20,
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

    if (empty($html)) {
        return new WP_Error(
            'lgjh_search_empty_response_body',
            '取得した検索結果HTMLが空です。'
        );
    }

    return $html;
}