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
 * 求人情報メニュー配下に LG Job Hunter Dashboard を追加する。
 *
 * add_submenu_page() の引数:
 *
 * 第1引数 $parent_slug:
 * - 親メニューのスラッグ
 * - 今回はカスタム投稿タイプ lg_job の管理メニュー配下に追加する
 *
 * 第2引数 $page_title:
 * - ブラウザのタイトルや管理画面のページタイトルに使われる名前
 *
 * 第3引数 $menu_title:
 * - 左メニューに表示される名前
 *
 * 第4引数 $capability:
 * - このページを表示できる権限
 * - manage_options は管理者向け
 *
 * 第5引数 $menu_slug:
 * - このサブメニューページ固有の識別子
 *
 * 第6引数 $callback:
 * - ページ表示時に実行される関数
 */
function lgjh_add_dashboard_page()
{
    add_submenu_page(
        'edit.php?post_type=lg_job',
        'LG Job Hunter Dashboard',
        'LG Job Hunter Dashboard',
        'manage_options',
        'lgjh-dashboard',
        'lgjh_render_test_import_page'
    );
}
add_action('admin_menu', 'lgjh_add_dashboard_page');

/**
 * テスト投入ページを表示
 */
function lgjh_render_test_import_page()
{
    $message = '';
    $input_url = '';
    $limit = 30;

    if (
        isset($_POST['lgjh_test_import_nonce']) &&
        wp_verify_nonce($_POST['lgjh_test_import_nonce'], 'lgjh_test_import')
    ) {
        $test_action = isset($_POST['lgjh_test_action'])
            ? sanitize_text_field(wp_unslash($_POST['lgjh_test_action']))
            : '';

        $message = match ($test_action) {
            // ボタン処理 1. 検索条件を保存
            'save_search_conditions' => lgjh_handle_save_search_conditions(),

            // 検索プリセットを保存
            'save_search_preset' => lgjh_handle_save_search_preset(),

            // ボタン処理 2. 固定条件で検索結果HTMLを取得・保存
            'fetch_and_save_search_result_html' => lgjh_handle_fetch_and_save_search_result_html(),

            // ボタン処理 3. 保存済み検索結果HTMLから詳細URLを抽出
            'extract_detail_urls_from_saved_html' => lgjh_handle_extract_detail_urls_from_saved_html(),

            // ボタン処理 4. 保存済み詳細URLの先頭3件を求人として保存
            'import_first_limitval_saved_detail_url_jobs' => lgjh_handle_import_saved_detail_url_jobs($limit),

            // ボタン処理 5. 1～4の処理一気通貫ボタン（自動実行用）
            'run_import_pipeline' => lgjh_handle_run_import_pipeline($limit),


            // 旧：詳細URLを直接入力して求人を1件保存
            'import_detail_url' => lgjh_handle_import_detail_url(),

            default => '',
        };
    }

?>

    <div class="wrap">
        <h1>LG Job Hunter Dashboard</h1>

        <?php echo $message; ?>

        <p>
            このページは、スクレイピング前に取得・解析・保存の流れを確認するためのテストページです。
        </p>

        <hr>

        <!-- 1 -->
        <hr>

        <?php $search_conditions = lgjh_get_search_conditions(); ?>
        <?php
        $search_presets = lgjh_get_search_condition_presets();
        $active_preset_id = lgjh_get_active_search_preset_id();
        ?>
        <h2>1. 検索プリセットの選択</h2>

        <p>
            使用する検索条件プリセットを選択します。
            プリセットの中身は <code>search-conditions.php</code> の配列で管理します。
        </p>

        <form method="post">
            <?php wp_nonce_field('lgjh_test_import', 'lgjh_test_import_nonce'); ?>

            <table class="form-table" style="max-width: 900px;">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="lgjh_search_preset_id">検索プリセット</label>
                        </th>
                        <td>
                            <select
                                id="lgjh_search_preset_id"
                                name="lgjh_search_preset_id"
                                class="regular-text"
                                style="width: 100%; max-width: 600px;">
                                <?php foreach ($search_presets as $preset_id => $preset) : ?>
                                    <option
                                        value="<?php echo esc_attr($preset_id); ?>"
                                        <?php selected($active_preset_id, $preset_id); ?>>
                                        <?php echo esc_html($preset['preset_label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <p class="description">
                                選択中のプリセットが、一括実行・cron実行時の検索条件として使われます。
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p>
                <button
                    type="submit"
                    name="lgjh_test_action"
                    value="save_search_preset"
                    class="button button-primary">
                    検索プリセットを保存する
                </button>
            </p>
        </form>

        <hr>

        <hr>

        <!-- 2 -->
        <!-- lgjh_handle_fetch_and_save_search_result_html() で動く -->
        <h2>2. 固定条件で検索結果HTMLを取得・保存</h2>

        <p>
            現在の固定検索条件を使って、ハローワークの検索結果HTMLを取得し、
            <code>dev-samples/debug-hellowork-search-result.html</code> に保存します。
        </p>

        <p>
            この段階では、詳細URLの抽出や求人情報の保存は行いません。
            まずは検索結果HTMLを正しく取得できるかを確認します。
        </p>

        <form method="post">
            <?php wp_nonce_field('lgjh_test_import', 'lgjh_test_import_nonce'); ?>

            <p>
                <button
                    type="submit"
                    name="lgjh_test_action"
                    value="fetch_and_save_search_result_html"
                    class="button button-primary">
                    固定条件で検索結果HTMLを取得・保存する
                </button>
            </p>
        </form>

        <hr>

        <!-- 3 -->
        <!-- lgjh_handle_extract_detail_urls_from_saved_html() -->
        <h2>3. 保存済み検索結果HTMLから詳細URLを抽出</h2>

        <p>
            <code>dev-samples/debug-hellowork-search-result.html</code> を読み込み、
            検索結果ページ内の「詳細を表示」リンクだけを抽出します。
        </p>

        <p>
            抽出できた詳細URLの総数を表示し、
            画面には確認用として先頭10件だけ表示します。
            1ページあたり最大30件前後になる想定です。
        </p>

        <form method="post">
            <?php wp_nonce_field('lgjh_test_import', 'lgjh_test_import_nonce'); ?>

            <p>
                <button
                    type="submit"
                    name="lgjh_test_action"
                    value="extract_detail_urls_from_saved_html"
                    class="button button-primary">
                    保存済み検索結果HTMLから詳細URLを抽出する
                </button>
            </p>
        </form>

        <hr>

        <!-- 4 -->
        <h2>4. 保存済み詳細URLの先頭30件を求人として保存</h2>

        <p>
            <code>dev-samples/debug-hellowork-detail-urls.txt</code> を読み込み、
            保存済みの詳細URL一覧から先頭30件を取得します。
        </p>

        <p>
            その詳細URLのページHTMLを取得し、
            求人データを解析して、WordPressの求人投稿として保存します。
        </p>

        <form method="post">
            <?php wp_nonce_field('lgjh_test_import', 'lgjh_test_import_nonce'); ?>

            <p>
                <button
                    type="submit"
                    name="lgjh_test_action"
                    value="import_first_limitval_saved_detail_url_jobs"
                    class="button button-primary">
                    保存済み詳細URL一覧を求人として保存する
                </button>
            </p>
        </form>

        <hr>
        <!-- 5 -->
        <h2>5. 自動実行ボタン</h2>
        <p>
            1～4の処理を一気通貫実行する
        </p>

        <form method="post">
            <?php wp_nonce_field('lgjh_test_import', 'lgjh_test_import_nonce'); ?>

            <p>
                <button
                    type="submit"
                    name="lgjh_test_action"
                    value="run_import_pipeline"
                    class="button button-primary">
                    1～4の処理を一気通貫実行する
                </button>
            </p>
        </form>

        <hr>
        <!-- 開発用 -->
        <h2>開発用. 詳細ページURLから求人を1件保存</h2>

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

    </div>

<?php
}

/**
 * 固定条件で検索結果HTMLを取得し、開発用ファイルとして保存する。
 *
 * この関数では詳細URLの抽出や求人保存は行わない。
 * あくまで「検索結果HTMLを取得して保存できるか」だけを確認する。
 *
 * @return string 管理画面に表示するメッセージHTML。
 */
function lgjh_handle_fetch_and_save_search_result_html()
{
    // 1. 固定条件で検索結果HTMLを取得
    $html = lgjh_fetch_hellowork_search_result_html();

    if (is_wp_error($html)) {
        return '<div class="notice notice-error"><p>'
            . esc_html($html->get_error_message())
            . '</p></div>';
    }

    if (empty($html)) {
        return '<div class="notice notice-error"><p>'
            . '検索結果HTMLが空でした。'
            . '</p></div>';
    }

    // 2. 保存先パスを作成
    $file_path = LG_JOB_HUNTER_PATH . '/dev-samples/debug-hellowork-search-result.html';

    // 3. 検索結果HTMLを保存
    $saved_result = file_put_contents($file_path, $html);

    if ($saved_result === false) {
        return '<div class="notice notice-error"><p>'
            . '検索結果HTMLの保存に失敗しました。'
            . '</p></div>';
    }

    // 4. 成功メッセージを返す
    return '<div class="notice notice-success"><p>'
        . '固定条件で検索結果HTMLを取得し、保存しました。'
        . '</p><p>保存先: '
        . esc_html($file_path)
        . '</p><p>保存サイズ: '
        . esc_html($saved_result)
        . ' bytes</p></div>';
}

/**
 * 保存済み検索結果HTMLから詳細URL一覧を抽出する。
 *
 * 第2面で保存した検索結果HTMLファイルを読み込み、
 * ハローワーク検索結果ページ内の詳細URLを抽出する。
 *
 * 抽出したURL一覧はデバッグ用テキストファイルにも保存する。
 *
 * @return string 管理画面に表示するメッセージHTML。
 */
function lgjh_handle_extract_detail_urls_from_saved_html()
{
    // 1. 保存済み検索結果HTMLのパスを作成
    $file_path = LG_JOB_HUNTER_PATH . '/dev-samples/debug-hellowork-search-result.html';

    // 2. ファイル存在チェック
    if (!file_exists($file_path)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み検索結果HTMLが見つかりません。先に「2. 固定条件で検索結果HTMLを取得・保存」を実行してください。'
            . '</p></div>';
    }

    // 3. 保存済み検索結果HTMLを読み込む
    $html = file_get_contents($file_path);

    if ($html === false || empty($html)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み検索結果HTMLを読み込めませんでした。'
            . '</p></div>';
    }

    // 4. 検索結果HTMLから詳細URL一覧を抽出
    $detail_urls = lgjh_parse_hellowork_search_result_urls($html);

    if (empty($detail_urls)) {
        return '<div class="notice notice-warning"><p>'
            . '保存済み検索結果HTMLは読み込めましたが、詳細URLを抽出できませんでした。'
            . '</p></div>';
    }

    // 5. 詳細URL一覧をデバッグ用テキストファイルに保存
    lgjh_save_debug_detail_urls($detail_urls);

    // 6. 画面表示用に先頭10件だけHTMLを作成
    $url_items = '';

    foreach (array_slice($detail_urls, 0, 10) as $detail_url) {
        $url_items .= '<li>'
            . '<a href="' . esc_url($detail_url) . '" target="_blank" rel="noopener noreferrer">'
            . esc_html($detail_url)
            . '</a>'
            . '</li>';
    }

    // 7. 成功メッセージを返す
    return '<div class="notice notice-success"><p>'
        . '詳細URLは全部で '
        . esc_html(count($detail_urls))
        . ' 件あります。'
        . '</p><p>'
        . '画面には先頭10件だけ表示しています。'
        . '</p>'
        . '<ol>'
        . $url_items
        . '</ol>'
        . '</div>';
}

/**
 * 抽出した詳細URL一覧をデバッグ用テキストファイルに保存
 *
 * @param array $detail_urls 詳細ページURL一覧
 * @return void
 */
function lgjh_save_debug_detail_urls($detail_urls)
{
    if (empty($detail_urls) || !is_array($detail_urls)) {
        return;
    }

    $file_path = LG_JOB_HUNTER_PATH . '/dev-samples/debug-hellowork-detail-urls.txt';

    $text = implode(PHP_EOL, $detail_urls);

    file_put_contents($file_path, $text);
}

/**
 * 保存済み詳細URL一覧から先頭1件を求人として保存する。
 *
 * 第3面で保存した詳細URL一覧テキストファイルを読み込み、
 * 現在は先頭1件だけを対象にして求人詳細ページを取得・解析・保存する。
 *
 * @return string 管理画面に表示するメッセージHTML。
 */
function lgjh_handle_import_first_saved_detail_url_job()
{
    // 1. 保存済み詳細URL一覧ファイルのパスを作成
    $file_path = LG_JOB_HUNTER_PATH . '/dev-samples/debug-hellowork-detail-urls.txt';

    // 2. ファイル存在チェック
    if (!file_exists($file_path)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み詳細URL一覧が見つかりません。先に「3. 保存済み検索結果HTMLから詳細URLを抽出」を実行してください。'
            . '</p></div>';
    }

    // 3. 詳細URL一覧ファイルを読み込む
    $text = file_get_contents($file_path);

    if ($text === false || empty($text)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み詳細URL一覧を読み込めませんでした。'
            . '</p></div>';
    }

    // 4. 改行区切りのテキストを配列に変換
    $detail_urls = array_filter(
        array_map(
            'trim',
            explode(PHP_EOL, $text)
        )
    );

    if (empty($detail_urls)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み詳細URL一覧に有効なURLがありませんでした。'
            . '</p></div>';
    }

    // 5. 現在は先頭1件だけ処理する
    $first_detail_url = $detail_urls[0];

    // 6. 詳細URLから求人を1件保存
    $result = lgjh_import_job_from_detail_url($first_detail_url);

    if (is_wp_error($result)) {
        return '<div class="notice notice-error"><p>'
            . esc_html($result->get_error_message())
            . '</p><p>対象URL: '
            . esc_html($first_detail_url)
            . '</p></div>';
    }

    // 7. 成功メッセージを返す
    return '<div class="notice notice-success"><p>'
        . '保存済み詳細URLの先頭1件を求人として保存しました。投稿ID: '
        . esc_html($result)
        . '</p><p>対象URL: '
        . esc_html($first_detail_url)
        . '</p><p>保存済み詳細URL数: '
        . esc_html(count($detail_urls))
        . ' 件</p></div>';
}

/**
 * 本番検索結果の先頭3件を取得・解析・保存する
 *
 * @return array|WP_Error 保存結果。失敗時は WP_Error。
 */
function lgjh_handle_import_first_3_saved_detail_url_jobs()
{
    // 1. 保存済み詳細URL一覧ファイルのパスを作成
    $file_path = LG_JOB_HUNTER_PATH . '/dev-samples/debug-hellowork-detail-urls.txt';

    // 2. ファイル存在チェック
    if (!file_exists($file_path)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み詳細URL一覧が見つかりません。先に「3. 保存済み検索結果HTMLから詳細URLを抽出」を実行してください。'
            . '</p></div>';
    }

    // 3. 詳細URL一覧ファイルを読み込む
    $text = file_get_contents($file_path);

    if ($text === false || empty($text)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み詳細URL一覧を読み込めませんでした。'
            . '</p></div>';
    }

    // 4. 改行区切りのテキストを配列に変換
    $detail_urls = array_filter(
        array_map(
            'trim',
            explode(PHP_EOL, $text)
        )
    );

    if (empty($detail_urls)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み詳細URL一覧に有効なURLがありませんでした。'
            . '</p></div>';
    }

    // 5. 先頭3件だけ処理する
    $target_urls = array_slice($detail_urls, 0, 3);
    $messages = []; // 全ての結果（成功・失敗）を格納する配列

    // 6. 詳細URLから求人を保存
    foreach ($target_urls as $detail_url) {
        $result = lgjh_import_job_from_detail_url($detail_url);


        if (is_wp_error($result)) {
            // エラー時のメッセージ
            $messages[] = '<div class="notice notice-error"><p>'
                . '【エラー】' . esc_html($result->get_error_message())
                . '</p><p>対象URL: ' . esc_html($detail_url) . '</p></div>';
            continue;
        }

        $status  = $result['status'] ?? '';
        $post_id = $result['post_id'] ?? 0;

        // 成功時のメッセージ
        if ($status === 'created') {
            $messages[] = '<div class="notice notice-success"><p>'
                . '求人を新規保存しました。投稿ID: ' . esc_html($post_id)
                . '</p><p>対象URL: ' . esc_html($detail_url) . '</p></div>';
        }

        if ($status === 'skipped') {
            $messages[] = '<div class="notice notice-warning"><p>'
                . '重複のためスキップしました。既存投稿ID: ' . esc_html($post_id)
                . '</p><p>対象URL: ' . esc_html($detail_url) . '</p></div>';
        }
    }

    // 7. 全てのメッセージを結合して返す
    $detail_url_count = count($detail_urls);
    $target_url_count = count($target_urls);
    $remaining_count = $detail_url_count - $target_url_count;

    $summary = '<div class="notice notice-info"><p>'
        . '詳細URL総数: ' . esc_html($detail_url_count) . ' 件 / '
        . '今回の処理対象: ' . esc_html($target_url_count) . ' 件 / '
        . '未処理残り: ' . esc_html($remaining_count) . ' 件'
        . '</p></div>';

    return implode('', $messages) . $summary;
}

/**
 * 保存済み詳細URL一覧から先頭指定件数分の求人を取得・解析・保存する。
 *
 * @param int $limit 保存する件数。
 * @return string 管理画面に表示するメッセージHTML。
 */
function lgjh_handle_import_saved_detail_url_jobs($limit)
{
    // 1. 保存済み詳細URL一覧ファイルのパスを作成
    $file_path = LG_JOB_HUNTER_PATH . '/dev-samples/debug-hellowork-detail-urls.txt';

    // 2. ファイル存在チェック
    if (!file_exists($file_path)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み詳細URL一覧が見つかりません。先に「3. 保存済み検索結果HTMLから詳細URLを抽出」を実行してください。'
            . '</p></div>';
    }

    // 3. 詳細URL一覧ファイルを読み込む
    $text = file_get_contents($file_path);

    if ($text === false || empty($text)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み詳細URL一覧を読み込めませんでした。'
            . '</p></div>';
    }

    // 4. 改行区切りのテキストを配列に変換
    $detail_urls = array_filter(
        array_map(
            'trim',
            explode(PHP_EOL, $text)
        )
    );

    if (empty($detail_urls)) {
        return '<div class="notice notice-error"><p>'
            . '保存済み詳細URL一覧に有効なURLがありませんでした。'
            . '</p></div>';
    }

    // 5. 先頭$limit件処理する
    $target_urls = array_slice($detail_urls, 0, $limit);
    $messages = []; // 全ての結果（成功・失敗）を格納する配列

    // 6. 詳細URLから求人を保存
    foreach ($target_urls as $detail_url) {
        $result = lgjh_import_job_from_detail_url($detail_url);


        if (is_wp_error($result)) {
            // エラー時のメッセージ
            $messages[] = '<div class="notice notice-error"><p>'
                . '【エラー】' . esc_html($result->get_error_message())
                . '</p><p>対象URL: ' . esc_html($detail_url) . '</p></div>';
            continue;
        }

        $status  = $result['status'] ?? '';
        $post_id = $result['post_id'] ?? 0;

        // 成功時のメッセージ
        if ($status === 'created') {
            $messages[] = '<div class="notice notice-success"><p>'
                . '求人を新規保存しました。投稿ID: ' . esc_html($post_id)
                . '</p><p>対象URL: ' . esc_html($detail_url) . '</p></div>';
        }

        if ($status === 'skipped') {
            $messages[] = '<div class="notice notice-warning"><p>'
                . '重複のためスキップしました。既存投稿ID: ' . esc_html($post_id)
                . '</p><p>対象URL: ' . esc_html($detail_url) . '</p></div>';
        }
    }

    // 7. 全てのメッセージを結合して返す
    $detail_url_count = count($detail_urls);
    $target_url_count = count($target_urls);
    $remaining_count = $detail_url_count - $target_url_count;

    $summary = '<div class="notice notice-info"><p>'
        . '詳細URL総数: ' . esc_html($detail_url_count) . ' 件 / '
        . '今回の処理対象: ' . esc_html($target_url_count) . ' 件 / '
        . '未処理残り: ' . esc_html($remaining_count) . ' 件'
        . '</p></div>';

    return implode('', $messages) . $summary;
}

/**
 * 開発用 本番検索結果の先頭1件を取得・解析・保存する
 *
 * @return string 管理画面に表示するメッセージHTML
 */
function lgjh_handle_import_first_search_result_job()
{
    $message = "";

    // 1. 固定条件で本番検索結果HTMLを取得
    $search_result_html = lgjh_fetch_hellowork_search_result_html();

    if (is_wp_error($search_result_html)) {
        $message = '<div class="notice notice-error"><p>'
            . esc_html($search_result_html->get_error_message())
            . '</p></div>';
    } else {
        // 2. 検索結果HTMLから詳細URL一覧を抽出
        $detail_urls = lgjh_parse_hellowork_search_result_urls($search_result_html);
        $detail_url_count = count($detail_urls);

        if (empty($detail_urls)) {
            $message = '<div class="notice notice-error"><p>詳細URLを抽出できませんでした。</p></div>';
        } else {
            // 3. 確認用に詳細URL一覧を保存
            lgjh_save_debug_detail_urls($detail_urls);

            // 4. まずは先頭1件だけ処理する
            $first_detail_url = $detail_urls[0];

            // 5. 詳細URLから求人を1件保存
            $result = lgjh_import_job_from_detail_url($first_detail_url);

            if (is_wp_error($result)) {
                $message = '<div class="notice notice-error"><p>'
                    . esc_html($result->get_error_message())
                    . '</p></div>';
            } else {
                $message = '<div class="notice notice-success"><p>'
                    . '本番検索結果の先頭1件を保存しました。投稿ID: '
                    . esc_html($result)
                    . '</p><p>取得URL: '
                    . esc_html($first_detail_url)
                    . '</p></div>';
            }
        }
    }
    return $message;
}

/**
 * 入力されたハローワーク詳細URLから求人を取得・解析・保存する
 *
 * @return string 管理画面に表示するメッセージHTML
 */
function lgjh_handle_import_detail_url()
{
    $input_url = isset($_POST['lgjh_detail_url'])
        ? esc_url_raw(wp_unslash($_POST['lgjh_detail_url']))
        : '';

    if (empty($input_url)) {
        return '<div class="notice notice-error"><p>詳細ページURLを入力してください。</p></div>';
    }

    $html = lgjh_fetch_html($input_url);

    if (is_wp_error($html)) {
        return '<div class="notice notice-error"><p>'
            . esc_html($html->get_error_message())
            . '</p></div>';
    }

    $job_data = lgjh_parse_hellowork_detail_html($html, $input_url);

    if (empty($job_data)) {
        return '<div class="notice notice-error"><p>求人データを解析できませんでした。</p></div>';
    }

    $result = lgjh_save_job_from_data($job_data);

    if (is_wp_error($result)) {
        return '<div class="notice notice-error"><p>'
            . esc_html($result->get_error_message())
            . '</p></div>';
    }

    return '<div class="notice notice-success"><p>'
        . 'ハローワーク詳細URLから求人を保存しました。投稿ID: '
        . esc_html($result)
        . '</p></div>';
}

/**
 * 詳細URLから求人情報を1件取得・解析・保存する。
 *
 * ハローワークの求人詳細URLを1つ受け取り、
 * 詳細ページHTMLの取得、求人データの解析、
 * カスタム投稿への保存までを担当する。
 *
 * 成功時は保存した投稿IDを返し、
 * 失敗時は WP_Error を返す。
 *
 * @param string $detail_url 求人詳細ページのURL。
 * @return array|WP_Error 保存成功時は投稿ID配列、失敗時は WP_Error。
 */
function lgjh_import_job_from_detail_url($detail_url)
{
    if (empty($detail_url)) {
        return new WP_Error(
            // エラーコード
            'empty_detail_url',
            // エラーメッセージ
            '詳細URLが空です。'
        );
    }

    // 1. 詳細ページHTMLを取得
    $detail_html = lgjh_fetch_html($detail_url);

    // is_wp_error() は、その変数が「エラーオブジェクトかどうか」を判定する専用の関数
    if (is_wp_error($detail_html)) {
        return $detail_html;
    }

    // 2. 取得した詳細HTMLをデバッグ保存
    file_put_contents(
        LG_JOB_HUNTER_PATH . '/dev-samples/debug-hellowork-detail.html',
        $detail_html
    );

    // 3. 詳細HTMLから求人データを解析
    $job_data = lgjh_parse_hellowork_detail_html($detail_html, $detail_url);

    if (empty($job_data)) {
        return new WP_Error(
            'empty_job_data',
            '求人データを解析できませんでした。'
        );
    }

    // 4. カスタム投稿として保存
    $result = lgjh_save_job_from_data($job_data);

    if (is_wp_error($result)) {
        return $result;
    }

    if (!is_array($result)) {
        return new WP_Error(
            'lgjh_invalid_save_result',
            '求人保存処理の返り値が不正です。'
        );
    }

    return $result;
}

/**
 * 求人取得パイプラインを実行し、管理画面表示用HTMLを返す。
 *
 * @param int $limit 保存する求人件数
 * @return string 管理画面に表示するメッセージHTML
 */
function lgjh_handle_run_import_pipeline($limit = 30)
{
    $result = lgjh_run_import_pipeline($limit);

    if (is_wp_error($result)) {
        return '<div class="notice notice-error"><p>'
            . esc_html($result->get_error_message())
            . '</p></div>';
    }

    $summary = '<div class="notice notice-info"><p>'
        . '詳細URL総数: ' . esc_html($result['total_urls'] ?? 0) . ' 件 / '
        . '今回の処理対象: ' . esc_html($result['target_urls'] ?? 0) . ' 件 / '
        . '新規保存: ' . esc_html($result['created'] ?? 0) . ' 件 / '
        . '重複スキップ: ' . esc_html($result['skipped'] ?? 0) . ' 件 / '
        . 'エラー: ' . esc_html($result['errors'] ?? 0) . ' 件'
        . '</p></div>';

    $items_html = '';

    foreach (($result['items'] ?? []) as $item) {
        $status  = $item['status'] ?? '';
        $post_id = $item['post_id'] ?? 0;
        $url     = $item['url'] ?? '';
        $message = $item['message'] ?? '';

        $notice_class = match ($status) {
            'created' => 'notice-success',
            'skipped' => 'notice-warning',
            'error'   => 'notice-error',
            default   => 'notice-info',
        };

        $items_html .= '<div class="notice ' . esc_attr($notice_class) . '"><p>'
            . esc_html($message);

        if (!empty($post_id)) {
            $items_html .= ' 投稿ID: ' . esc_html($post_id);
        }

        $items_html .= '</p>';

        if (!empty($url)) {
            $items_html .= '<p>対象URL: '
                . '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">'
                . esc_html($url)
                . '</a>'
                . '</p>';
        }

        $items_html .= '</div>';
    }

    return $summary . $items_html;
}

/**
 * 開発用 固定条件の検索結果HTMLから詳細URL一覧を抽出するテスト処理。
 *
 * @return string 管理画面に表示するメッセージHTML。
 */
function lgjh_handle_fetch_search_result_urls()
{
    // 1. 固定条件で検索結果HTMLを取得
    $html = lgjh_fetch_hellowork_search_result_html();

    if (is_wp_error($html)) {
        return '<div class="notice notice-error"><p>'
            . esc_html($html->get_error_message())
            . '</p></div>';
    }

    // 2. 検索結果HTMLから詳細URL一覧を抽出
    $extracted_urls = "";
    $extracted_urls = lgjh_parse_hellowork_search_result_urls($html);

    if (empty($extracted_urls)) {
        return '<div class="notice notice-warning"><p>'
            . '検索結果HTMLは取得できましたが、詳細URLを抽出できませんでした。'
            . '</p></div>';
    }

    // 3. 詳細URLの件数を取得
    $detail_url_count = count($extracted_urls);

    // 4. 確認用に詳細URL一覧を保存
    lgjh_save_debug_detail_urls($extracted_urls);

    // 5. 成功メッセージを返す
    return '<div class="notice notice-success"><p>'
        . '詳細URLは全部で '
        . esc_html($detail_url_count)
        . ' 件あります。'
        . '</p><p>先頭URL: '
        . esc_html($extracted_urls[0])
        . '</p></div>';
}
