<?php

/**
 * テスト用求人投入ページ
 *
 * 役割:
 * - 管理画面からハローワーク詳細ページURLを入力する
 * - URLからHTMLを取得する
 * - ハローワーク詳細ページparserで求人データを抽出する
 * - 抽出した求人データをカスタム投稿として保存する
 * - 開発用検索結果HTMLから詳細URL一覧を抽出する
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 求人情報メニュー配下にテスト投入ページを追加
 */
function lgjh_add_test_import_page()
{
    add_submenu_page(
        'edit.php?post_type=lg_job',
        'テスト投入',
        'テスト投入',
        'manage_options',
        'lgjh-test-import',
        'lgjh_render_test_import_page'
    );
}
add_action('admin_menu', 'lgjh_add_test_import_page');

/**
 * テスト投入ページを表示
 */
function lgjh_render_test_import_page()
{
    $message = '';
    $input_url = '';
    $extracted_urls = [];

    if (
        isset($_POST['lgjh_test_import_nonce']) &&
        wp_verify_nonce($_POST['lgjh_test_import_nonce'], 'lgjh_test_import')
    ) {
        $test_action = isset($_POST['lgjh_test_action'])
            ? sanitize_text_field(wp_unslash($_POST['lgjh_test_action']))
            : '';

        if ($test_action === 'import_detail_url') {
            $input_url = isset($_POST['lgjh_detail_url'])
                ? esc_url_raw(wp_unslash($_POST['lgjh_detail_url']))
                : '';

            if (empty($input_url)) {
                $message = '<div class="notice notice-error"><p>詳細ページURLを入力してください。</p></div>';
            } else {
                $html = lgjh_fetch_html($input_url);

                if (is_wp_error($html)) {
                    $message = '<div class="notice notice-error"><p>'
                        . esc_html($html->get_error_message())
                        . '</p></div>';
                } else {
                    $job_data = lgjh_parse_hellowork_detail_html($html, $input_url);

                    if (empty($job_data)) {
                        $message = '<div class="notice notice-error"><p>求人データを解析できませんでした。</p></div>';
                    } else {
                        $result = lgjh_save_job_from_data($job_data);

                        if (is_wp_error($result)) {
                            $message = '<div class="notice notice-error"><p>'
                                . esc_html($result->get_error_message())
                                . '</p></div>';
                        } else {
                            $message = '<div class="notice notice-success"><p>'
                                . 'ハローワーク詳細URLから求人を保存しました。投稿ID: '
                                . esc_html($result)
                                . '</p></div>';
                        }
                    }
                }
            }
        }

        if ($test_action === 'extract_search_result_urls') {
            $sample_file_path = LG_JOB_HUNTER_PATH . 'dev-samples/search-result.html';

            if (!file_exists($sample_file_path)) {
                $message = '<div class="notice notice-error"><p>検索結果サンプルHTMLが見つかりません。</p></div>';
            } else {
                $html = file_get_contents($sample_file_path);

                if ($html === false || empty($html)) {
                    $message = '<div class="notice notice-error"><p>検索結果サンプルHTMLを読み込めませんでした。</p></div>';
                } else {
                    $extracted_urls = lgjh_parse_hellowork_search_result_urls($html);

                    $message = '<div class="notice notice-success"><p>'
                        . '検索結果HTMLから詳細URLを '
                        . esc_html(count($extracted_urls))
                        . ' 件抽出しました。'
                        . '</p></div>';
                }
            }
        }

        if ($test_action === 'import_first_search_result_job') {
            $sample_file_path = LG_JOB_HUNTER_PATH . 'dev-samples/search-result.html';

            if (!file_exists($sample_file_path)) {
                $message = '<div class="notice notice-error"><p>検索結果サンプルHTMLが見つかりません。</p></div>';
            } else {
                $search_result_html = file_get_contents($sample_file_path);

                if ($search_result_html === false || empty($search_result_html)) {
                    $message = '<div class="notice notice-error"><p>検索結果サンプルHTMLを読み込めませんでした。</p></div>';
                } else {
                    $detail_urls = lgjh_parse_hellowork_search_result_urls($search_result_html);

                    if (empty($detail_urls)) {
                        $message = '<div class="notice notice-error"><p>詳細URLを抽出できませんでした。</p></div>';
                    } else {
                        $first_detail_url = $detail_urls[0];

                        $detail_html = lgjh_fetch_html($first_detail_url);

                        if (is_wp_error($detail_html)) {
                            $message = '<div class="notice notice-error"><p>'
                                . esc_html($detail_html->get_error_message())
                                . '</p></div>';
                        } else {
                            $job_data = lgjh_parse_hellowork_detail_html($detail_html, $first_detail_url);

                            if (empty($job_data)) {
                                $message = '<div class="notice notice-error"><p>求人データを解析できませんでした。</p></div>';
                            } else {
                                $result = lgjh_save_job_from_data($job_data);

                                if (is_wp_error($result)) {
                                    $message = '<div class="notice notice-error"><p>'
                                        . esc_html($result->get_error_message())
                                        . '</p></div>';
                                } else {
                                    $message = '<div class="notice notice-success"><p>'
                                        . '検索結果HTMLの先頭1件を保存しました。投稿ID: '
                                        . esc_html($result)
                                        . '</p><p>取得URL: '
                                        . esc_html($first_detail_url)
                                        . '</p></div>';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
?>

    <div class="wrap">
        <h1>テスト投入</h1>

        <?php echo $message; ?>

        <p>
            このページは、スクレイピング前に取得・解析・保存の流れを確認するためのテストページです。
        </p>

        <hr>

        <h2>1. 詳細ページURLから求人を1件保存</h2>

        <p>
            ハローワーク詳細ページURLを入力すると、
            <code>fetcher.php</code> でHTMLを取得し、
            <code>parser.php</code> で求人情報を抽出し、
            <code>job-repository.php</code> でカスタム投稿へ保存します。
        </p>

        <form method="post">
            <?php wp_nonce_field('lgjh_test_import', 'lgjh_test_import_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="lgjh_detail_url">詳細ページURL</label>
                    </th>
                    <td>
                        <input
                            type="url"
                            id="lgjh_detail_url"
                            name="lgjh_detail_url"
                            value="<?php echo esc_attr($input_url); ?>"
                            class="regular-text"
                            placeholder="https://www.hellowork.mhlw.go.jp/kensaku/GECA110010.do?..."
                            style="width: 100%; max-width: 900px;">
                        <p class="description">
                            まずはハローワークの求人詳細ページURLを1件だけ貼り付けてください。
                        </p>
                    </td>
                </tr>
            </table>

            <p>
                <button
                    type="submit"
                    name="lgjh_test_action"
                    value="import_detail_url"
                    class="button button-primary">
                    URLから求人を1件追加する
                </button>
            </p>
        </form>

        <hr>

        <h2>2. 検索結果HTMLから詳細URLを抽出</h2>

        <p>
            <code>dev-samples/search-result.html</code> を読み込み、
            検索結果ページ内の「詳細を表示」リンクを抽出します。
        </p>

        <form method="post">
            <?php wp_nonce_field('lgjh_test_import', 'lgjh_test_import_nonce'); ?>

            <p>
                <button
                    type="submit"
                    name="lgjh_test_action"
                    value="extract_search_result_urls"
                    class="button">
                    検索結果HTMLから詳細URLを抽出する
                </button>
            </p>
        </form>

        <?php if (!empty($extracted_urls)) : ?>
            <h3>抽出結果</h3>

            <p>
                まずは確認用として、最大10件だけ表示しています。
            </p>

            <ol>
                <?php foreach (array_slice($extracted_urls, 0, 10) as $url) : ?>
                    <li>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html($url); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <hr>

        <h2>3. 検索結果HTMLの先頭1件を保存</h2>

        <p>
            <code>dev-samples/search-result.html</code> から詳細URLを抽出し、
            先頭1件だけ本番詳細ページを取得して保存します。
        </p>

        <form method="post">
            <?php wp_nonce_field('lgjh_test_import', 'lgjh_test_import_nonce'); ?>

            <p>
                <button
                    type="submit"
                    name="lgjh_test_action"
                    value="import_first_search_result_job"
                    class="button button-primary">
                    検索結果HTMLの先頭1件を保存する
                </button>
            </p>
        </form>
    </div>

<?php
}
