<?php
/**
 * テスト用求人投入ページ
 *
 * 役割:
 * - 管理画面からテスト用求人データを追加する
 * - サンプルHTML解析 → 求人データ化 → カスタム投稿保存の流れを確認する
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

    if (
        isset($_POST['lgjh_test_import_nonce']) &&
        wp_verify_nonce($_POST['lgjh_test_import_nonce'], 'lgjh_test_import')
    ) {
        $sample_file_path = LG_JOB_HUNTER_PATH . 'dev-samples/hellowork-detail.html';

        if (!file_exists($sample_file_path)) {
            $message = '<div class="notice notice-error"><p>サンプルHTMLファイルが見つかりません。</p></div>';
        } else {
            $html = file_get_contents($sample_file_path);

            if ($html === false || empty($html)) {
                $message = '<div class="notice notice-error"><p>サンプルHTMLを読み込めませんでした。</p></div>';
            } else {
                $source_url = 'https://www.hellowork.mhlw.go.jp/kensaku/GECA110010.do';

                $job_data = lgjh_parse_hellowork_detail_html($html, $source_url);

                if (empty($job_data)) {
                    $message = '<div class="notice notice-error"><p>求人データを解析できませんでした。</p></div>';
                } else {
                    $result = lgjh_save_job_from_data($job_data);

                    if (is_wp_error($result)) {
                        $message = '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
                    } else {
                        $message = '<div class="notice notice-success"><p>ハローワーク詳細HTMLから求人を保存しました。投稿ID: ' . esc_html($result) . '</p></div>';
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
            このページは、スクレイピング前に保存処理を確認するためのテストページです。
        </p>

        <p>
            現在は <code>dev-samples/hellowork-detail.html</code> を読み込み、
            ハローワーク詳細ページparserで求人データを抽出して保存します。
        </p>

        <form method="post">
            <?php wp_nonce_field('lgjh_test_import', 'lgjh_test_import_nonce'); ?>

            <p>
                <button type="submit" class="button button-primary">
                    ハローワーク詳細HTMLから1件追加する
                </button>
            </p>
        </form>
    </div>

    <?php
}