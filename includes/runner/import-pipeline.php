<?php
/**
 * 求人取得パイプラインを実行する
 *
 * 役割:
 * - 検索結果HTMLを取得する
 * - 検索結果HTMLから詳細URL一覧を抽出する
 * - 指定件数分だけ求人詳細を取得・解析・保存する
 * - 管理画面やcronで使える処理結果配列を返す
 *
 * @param int $limit 保存する求人件数
 * @return array|WP_Error 処理結果。失敗時は WP_Error
 */
function lgjh_run_import_pipeline($limit = 30)
{
    $html = lgjh_fetch_hellowork_search_result_html();

    if (is_wp_error($html)) {
        return $html;
    }

    if (empty($html)) {
        return new WP_Error(
            'lgjh_empty_search_result_html',
            '検索結果HTMLが空でした。'
        );
    }

    $detail_urls = lgjh_parse_hellowork_search_result_urls($html);

    if (empty($detail_urls)) {
        return new WP_Error(
            'lgjh_empty_detail_urls',
            '検索結果HTMLから詳細URLを抽出できませんでした。'
        );
    }

    lgjh_save_debug_detail_urls($detail_urls);

    $target_urls = array_slice($detail_urls, 0, $limit);

    $result = [
        'total_urls'  => count($detail_urls),
        'target_urls' => count($target_urls),
        'created'     => 0,
        'skipped'     => 0,
        'errors'      => 0,
        'items'       => [],
    ];

    foreach ($target_urls as $detail_url) {
        $import_result = lgjh_import_job_from_detail_url($detail_url);

        if (is_wp_error($import_result)) {
            $result['errors']++;

            $result['items'][] = [
                'status'  => 'error',
                'post_id' => 0,
                'url'     => $detail_url,
                'message' => $import_result->get_error_message(),
            ];

            continue;
        }

        $status = $import_result['status'] ?? '';
        $post_id = $import_result['post_id'] ?? 0;

        if ($status === 'created') {
            $result['created']++;
        }

        if ($status === 'skipped') {
            $result['skipped']++;
        }

        $result['items'][] = [
            'status'  => $status,
            'post_id' => $post_id,
            'url'     => $detail_url,
            'message' => $status === 'created'
                ? '求人を新規保存しました。'
                : '重複のためスキップしました。',
        ];
    }

    return $result;
}