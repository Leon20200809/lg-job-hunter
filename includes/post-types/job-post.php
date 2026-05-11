<?php
/**
 * 求人情報カスタム投稿タイプを登録するファイル
 *
 * 役割:
 * - WordPress管理画面に「求人情報」を追加する
 * - 求人データを投稿として保存できるようにする
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 求人情報カスタム投稿タイプを登録
 */
function lgjh_register_job_post_type()
{
    $labels = [
        'name'          => '求人情報',
        'singular_name' => '求人情報',
        'menu_name'     => '求人情報',
        'add_new'       => '新規追加',
        'add_new_item'  => '求人情報を追加',
        'edit_item'     => '求人情報を編集',
        'new_item'      => '新しい求人情報',
        'view_item'     => '求人情報を表示',
        'search_items'  => '求人情報を検索',
        'not_found'     => '求人情報が見つかりません',
    ];

    $args = [
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => true,
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-id',
        'supports'      => ['title'],
        'show_in_rest'  => true,
        'rewrite'       => [
            'slug' => 'jobs',
        ],
    ];

    register_post_type('lg_job', $args);
}

add_action('init', 'lgjh_register_job_post_type');