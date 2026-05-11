<?php

/**
 * 求人HTML解析処理
 *
 * 役割:
 * - HTML文字列から求人情報を抜き出す
 * - 抜き出した情報を job-repository.php に渡しやすい配列へ変換する
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * サンプルHTMLから求人データを解析する
 *
 * @param string $html 解析対象のHTML文字列
 * @return array 求人データの配列
 */
function lgjh_parse_jobs_from_html($html)
{
    if (empty($html)) {
        return [];
    }

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);

    $xpath = new DOMXPath($dom);

    $job_nodes = $xpath->query('//div[contains(@class, "job-card")]');

    $jobs = [];

    foreach ($job_nodes as $job_node) {
        $title_node   = $xpath->query('.//h2[contains(@class, "job-title")]', $job_node)->item(0);
        $company_node = $xpath->query('.//p[contains(@class, "company-name")]', $job_node)->item(0);
        $location_node = $xpath->query('.//p[contains(@class, "location")]', $job_node)->item(0);
        $url_node     = $xpath->query('.//a[contains(@class, "job-link")]', $job_node)->item(0);

        $job_title = $title_node ? trim($title_node->textContent) : '';
        $company_name = $company_node ? trim($company_node->textContent) : '';
        $location = $location_node ? trim($location_node->textContent) : '';
        $job_url = '';

        if ($url_node instanceof DOMElement) {
            $job_url = trim($url_node->getAttribute('href'));
        }


        if (empty($job_title) || empty($job_url)) {
            continue;
        }

        $jobs[] = [
            'job_title'    => $job_title,
            'company_name' => $company_name,
            'location'     => $location,
            'job_url'      => $job_url,
            'status'       => 'not_applied',
        ];
    }

    libxml_clear_errors();

    return $jobs;
}

/**
 * ハローワーク詳細ページHTMLから求人データを解析する
 *
 * @param string $html 詳細ページHTML
 * @param string $source_url 詳細ページURL
 * @return array 求人データ
 */
function lgjh_parse_hellowork_detail_html($html, $source_url = '')
{
    if (empty($html)) {
        return [];
    }

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);

    $xpath = new DOMXPath($dom);

    $job_title       = lgjh_get_text_by_id($xpath, 'ID_sksu');
    $company_name    = lgjh_get_text_by_id($xpath, 'ID_jgshMei');
    $location        = lgjh_get_text_by_id($xpath, 'ID_shgBsJusho');
    $job_number      = lgjh_get_text_by_id($xpath, 'ID_kjNo');
    $description     = lgjh_get_text_by_id($xpath, 'ID_shigotoNy');
    $salary          = lgjh_get_text_by_id($xpath, 'ID_chgn');
    $employment_type = lgjh_get_text_by_id($xpath, 'ID_koyoKeitai');

    libxml_clear_errors();

    if (empty($job_title)) {
        return [];
    }

    return [
        'job_title'       => $job_title,
        'company_name'    => $company_name,
        'location'        => $location,
        'job_url'         => esc_url_raw($source_url),
        'job_number'      => $job_number,
        'description'     => $description,
        'salary'          => $salary,
        'employment_type' => $employment_type,
        'status'          => 'not_applied',
    ];
}

/**
 * 指定したid属性を持つ要素のテキストを取得する
 *
 * 注意:
 * ハローワークHTMLでは同じidが複数出る可能性があるため、
 * MVPでは最初に見つかった要素を使う。
 *
 * @param DOMXPath $xpath DOMXPathインスタンス
 * @param string $id 取得したい要素ID
 * @return string 取得したテキスト
 */
function lgjh_get_text_by_id($xpath, $id)
{
    $nodes = $xpath->query('//*[@id="' . $id . '"]');

    if (!$nodes || $nodes->length === 0) {
        return '';
    }

    $node = $nodes->item(0);

    if (!$node) {
        return '';
    }

    return lgjh_clean_text($node->textContent);
}

/**
 * 取得したテキストを保存しやすい形に整える
 *
 * @param string $text 元テキスト
 * @return string 整形後テキスト
 */
function lgjh_clean_text($text)
{
    $text = trim($text);

    // 連続する空白・改行を1つのスペースにまとめる
    $text = preg_replace('/\s+/u', ' ', $text);

    return $text;
}
