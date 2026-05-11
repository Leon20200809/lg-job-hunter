<?php
/**
 * テスト用求人投入ページ
 *
 * 役割:
 * - 管理画面からハローワーク詳細ページURLを入力する
 * - URLからHTMLを取得する
 * - ハローワーク詳細ページparserで求人データを抽出する
 * - 抽出した求人データをカスタム投稿として保存する
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

    if (
        isset($_POST['lgjh_test_import_nonce']) &&
        wp_verify_nonce($_POST['lgjh_test_import_nonce'], 'lgjh_test_import')
    ) {
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
    ?>

    <div class="wrap">
        <h1>テスト投入</h1>

        <?php echo $message; ?>

        <p>
            このページは、スクレイピング前に取得・解析・保存の流れを確認するためのテストページです。
        </p>

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
                            style="width: 100%; max-width: 900px;"
                        >
                        <p class="description">
                            まずはハローワークの求人詳細ページURLを1件だけ貼り付けてください。
                        </p>
                    </td>
                </tr>
            </table>

            <p>
                <button type="submit" class="button button-primary">
                    URLから求人を1件追加する
                </button>
            </p>
        </form>
    </div>

    <?php
}