<?php
/**
 * 検索条件のデフォルト値を取得する。
 *
 * @return array 検索条件のデフォルト値。
 */
function lgjh_get_default_search_conditions()
{
    return [
        'prefecture_code' => '27',
        'prefecture_label' => '大阪府',
        'keyword' => 'PHP JavaScript Laravel WordPress',
        'keyword_mode' => 'or',
        'job_type' => '1',
        'job_type_label' => '一般求人',
        'job_category_large' => '11',
        'job_category_small' => '1100',
        'job_category_label' => 'IT・Web・エンジニア',
    ];
}

/**
 * 保存済みの検索条件を取得する。
 *
 * WordPress options に保存済みの検索条件があればそれを使い、
 * なければデフォルト値を返す。
 *
 * @return array 検索条件。
 */
function lgjh_get_search_conditions()
{
    $default_conditions = lgjh_get_default_search_conditions();

    $saved_conditions = get_option('lgjh_search_conditions', []);

    if (!is_array($saved_conditions)) {
        return $default_conditions;
    }

    return array_merge($default_conditions, $saved_conditions);
}

/**
 * 管理画面から送信された検索条件を保存する。
 *
 * @return string 管理画面に表示するメッセージHTML。
 */
function lgjh_handle_save_search_conditions()
{
    $keyword = isset($_POST['lgjh_keyword'])
        ? sanitize_text_field(wp_unslash($_POST['lgjh_keyword']))
        : '';

    $keyword_mode = isset($_POST['lgjh_keyword_mode'])
        ? sanitize_text_field(wp_unslash($_POST['lgjh_keyword_mode']))
        : 'or';

    if (!in_array($keyword_mode, ['or', 'and'], true)) {
        $keyword_mode = 'or';
    }

    if (empty($keyword)) {
        return '<div class="notice notice-error"><p>'
            . 'フリーワードを入力してください。'
            . '</p></div>';
    }

    $conditions = lgjh_get_search_conditions();

    $conditions['keyword'] = $keyword;
    $conditions['keyword_mode'] = $keyword_mode;

    update_option('lgjh_search_conditions', $conditions);

    return '<div class="notice notice-success"><p>'
        . '検索条件を保存しました。'
        . '</p></div>';
}