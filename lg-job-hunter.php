<?php
/**
 * Plugin Name: LG Job Hunter
 * Description: 求人情報を収集し、WordPressのカスタム投稿として管理するためのプラグインです。
 * Version: 0.1.0
 * Author: Leon.C
 * Text Domain: lg-job-hunter
 */

if (!defined('ABSPATH')) {
    exit;
}

define( 'LG_JOB_HUNTER_PATH', plugin_dir_path( __FILE__ ) );

// カスタム投稿を追加
require_once LG_JOB_HUNTER_PATH . 'includes/post-types/job-post.php';

// メタボックスを追加
require_once LG_JOB_HUNTER_PATH . 'includes/meta-boxes/job-meta-box.php';

// 管理画面に一覧表示
require_once LG_JOB_HUNTER_PATH . 'includes/admin/job-list-columns.php';

// 求人情報の保存処理群
require_once LG_JOB_HUNTER_PATH . 'includes/storage/job-repository.php';

//求人一覧の「応募する」ボタンHTML生成、Gmail作成画面を開くためのURL・本文を生成
require_once LG_JOB_HUNTER_PATH . 'includes/admin/apply-mail-button.php';

// 外部HTMLの取得処理
require_once LG_JOB_HUNTER_PATH . 'includes/crawler/fetcher.php';

// HTML解析担当
require_once LG_JOB_HUNTER_PATH . 'includes/crawler/parser.php';

// 検索条件管理担当
require_once LG_JOB_HUNTER_PATH . 'includes/settings/search-conditions.php';

// 自動実行関数担当
require_once LG_JOB_HUNTER_PATH . 'includes/runner/import-pipeline.php';

// テスト用の求人投入ページ
require_once LG_JOB_HUNTER_PATH . 'includes/admin/test-import-page.php';