<?php

/**
 * 求人情報一覧画面のカラム設定
 *
 * 役割:
 * - 管理画面の求人一覧に会社名・勤務地・応募ステータス・求人URLを表示する
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 求人情報一覧に表示するカラムを追加
 */
function lgjh_add_job_list_columns($columns)
{
    $new_columns = [];

    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = '求人タイトル';
    $new_columns['job_number'] = '求人番号';
    $new_columns['company_name'] = '会社名';
    $new_columns['location'] = '勤務地';
    $new_columns['salary'] = '給与';
    $new_columns['status'] = '応募ステータス';
    $new_columns['job_url'] = '求人URL';
    $new_columns['apply'] = '応募';
    $new_columns['contact_person'] = '担当者';
    $new_columns['contact_email'] = 'メール';
    $new_columns['private_memo'] = '俺メモ';
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}
add_filter('manage_lg_job_posts_columns', 'lgjh_add_job_list_columns');

/**
 * 追加したカラムに値を表示
 */
function lgjh_render_job_list_columns($column_name, $post_id)
{
    // 求人番号カラム
    if ($column_name === 'job_number') {
        echo esc_html(get_post_meta($post_id, '_lgjh_job_number', true));
    }

    // 会社名カラム
    if ($column_name === 'company_name') {
        echo esc_html(get_post_meta($post_id, '_lgjh_company_name', true));
    }

    // 勤務地カラム
    if ($column_name === 'location') {
        echo esc_html(get_post_meta($post_id, '_lgjh_location', true));
    }

    // 給与カラム
    if ($column_name === 'salary') {
        echo esc_html(get_post_meta($post_id, '_lgjh_salary', true));
    }

    // 応募ステータスカラム
    if ($column_name === 'status') {
        $status = get_post_meta($post_id, '_lgjh_status', true);

        $status_labels = [
            'not_applied' => '未応募',
            'considering' => '検討中',
            'applied' => '応募済み',
            'rejected' => '見送り',
            'closed' => '募集終了',
        ];

        echo esc_html($status_labels[$status] ?? '未設定');
    }

    // 求人URLカラム
    if ($column_name === 'job_url') {
        $job_url = get_post_meta($post_id, '_lgjh_job_url', true);

        if (!empty($job_url)) {
            echo '<a href="' . esc_url($job_url) . '" target="_blank" rel="noopener noreferrer">開く</a>';
        }
    }

    // 応募ボタンカラム
    if ($column_name === 'apply') {
        echo lgjh_get_apply_button_html($post_id);
    }

    // 担当者カラム
    if ($column_name === 'contact_person') {
        echo esc_html(get_post_meta($post_id, '_lgjh_contact_person', true));
    }

    // メールカラム
    if ($column_name === 'contact_email') {
        $contact_email = get_post_meta($post_id, '_lgjh_contact_email', true);

        if (empty($contact_email)) {
            echo '未掲載';
            return;
        }

        echo esc_html($contact_email);
    }

    // 俺用一行メモカラム
    if ($column_name === 'private_memo') {
        echo esc_html(get_post_meta($post_id, '_lgjh_private_memo', true));
    }
}
add_action('manage_lg_job_posts_custom_column', 'lgjh_render_job_list_columns', 10, 2);

/**
 * 求人一覧に応募ステータス絞り込みセレクトを追加
 */
function lgjh_add_status_filter_to_job_list()
{
    global $typenow;

    if ($typenow !== 'lg_job') {
        return;
    }

    $selected_status = $_GET['lgjh_status_filter'] ?? '';

    $status_labels = [
        'not_applied' => '未応募',
        'considering'  => '検討中',
        'applied'      => '応募済み',
        'rejected'     => '見送り',
        'closed'       => '募集終了',
    ];
?>
    <select name="lgjh_status_filter">
        <option value="">すべての応募ステータス</option>

        <?php foreach ($status_labels as $status_value => $status_label) : ?>
            <option value="<?php echo esc_attr($status_value); ?>" <?php selected($selected_status, $status_value); ?>>
                <?php echo esc_html($status_label); ?>
            </option>
        <?php endforeach; ?>
    </select>
<?php
}
// restrict_manage_posts（リストリクト） 一覧画面上部の「絞り込み」UIを追加するフック。
add_action('restrict_manage_posts', 'lgjh_add_status_filter_to_job_list');

/**
 * 応募ステータス絞り込み条件を一覧クエリに反映
 */
function lgjh_filter_job_list_by_status($query)
{
    global $pagenow;

    if (!is_admin()) {
        return;
    }

    // 投稿一覧（edit.php）以外の時は、何もしない
    if ($pagenow !== 'edit.php') {
        return;
    }

    if (!$query->is_main_query()) {
        return;
    }

    $post_type = $_GET['post_type'] ?? '';

    if ($post_type !== 'lg_job') {
        return;
    }

    $selected_status = $_GET['lgjh_status_filter'] ?? '';

    if (empty($selected_status)) {
        return;
    }

    $allowed_statuses = [
        'not_applied',
        'considering',
        'applied',
        'rejected',
        'closed',
    ];

    if (!in_array($selected_status, $allowed_statuses, true)) {
        return;
    }

    $query->set('meta_query', [
        [
            'key'   => '_lgjh_status',
            'value' => sanitize_text_field($selected_status),
        ],
    ]);
}
// pre_get_posts  WordPressが投稿一覧を取ってくる直前に、検索条件を差し込むフック。
add_action('pre_get_posts', 'lgjh_filter_job_list_by_status');
