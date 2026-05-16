<?php

/**
 * 検索条件プリセット一覧を取得する。
 *
 * 役割:
 * - 検索条件をコード上のプリセットとして管理する
 * - 管理画面ではプリセットIDだけを切り替える
 * - 他のWordPress環境では、この配列を編集・追加すれば検索条件を変えられる
 *
 * @return array 検索条件プリセット一覧
 */
function lgjh_get_search_condition_presets()
{
    return [
        /*
         * 1. PHP WordPress エンジニア向け（一般求人）
         */
        'engineer_general' => [
            'preset_id'    => 'engineer_general',
            'preset_label' => 'PHP WordPress エンジニア向け（一般求人）',

            'job_type'       => '1',
            'job_type_label' => '一般求人',

            'prefecture_code'  => '27',
            'prefecture_label' => '大阪府',

            'keyword'      => 'PHP',
            'keyword_mode' => 'or',

            'exclude_keyword' => '',

            'job_categories' => [],

            'condition_code' => '',

            'remote_work'       => '',
            'remote_work_label' => '指定なし',

            'display_count' => '30',
            'sort_order'    => '1',
            'page'          => '1',
            'left_page'     => '1',

            'job_category_label' => '指定なし',
        ],

        /*
         * 2. 事務作業・軽作業（障害者求人）
         */
        'disability_office_light' => [
            'preset_id'    => 'disability_office_light',
            'preset_label' => '事務作業・軽作業（障害者求人）',

            'job_type'       => '5',
            'job_type_label' => '障害者求人',

            'prefecture_code'  => '27',
            'prefecture_label' => '大阪府',

            'keyword'      => '',
            'keyword_mode' => 'or',

            'exclude_keyword' => '',

            'job_categories' => [
                [
                    'large' => '04',
                    'small' => '0400',
                    'label' => '事務的職業',
                ],
                [
                    'large' => '13',
                    'small' => '1300',
                    'label' => '情報処理・通信技術者',
                ],
            ],

            'condition_code' => '20260101001',

            'remote_work'       => '1',
            'remote_work_label' => '在宅勤務を含む',

            'display_count' => '30',
            'sort_order'    => '1',
            'page'          => '1',
            'left_page'     => '1',

            'job_category_label' => '事務的職業 / 情報処理・通信技術者',
        ],
    ];
}

/**
 * 初期検索プリセットIDを取得する。
 *
 * @return string 初期プリセットID
 */
function lgjh_get_default_search_preset_id()
{
    return 'engineer_general';
}

/**
 * 現在選択中の検索プリセットIDを取得する。
 *
 * @return string 現在選択中のプリセットID
 */
function lgjh_get_active_search_preset_id()
{
    $default_preset_id = lgjh_get_default_search_preset_id();

    $active_preset_id = get_option(
        'lgjh_active_search_preset_id',
        $default_preset_id
    );

    $presets = lgjh_get_search_condition_presets();

    if (!isset($presets[$active_preset_id])) {
        return $default_preset_id;
    }

    return $active_preset_id;
}

/**
 * 現在選択中の検索条件を取得する。
 *
 * @return array 検索条件
 */
function lgjh_get_search_conditions()
{
    $presets = lgjh_get_search_condition_presets();

    $active_preset_id = lgjh_get_active_search_preset_id();

    if (!isset($presets[$active_preset_id])) {
        $active_preset_id = lgjh_get_default_search_preset_id();
    }

    return $presets[$active_preset_id];
}

/**
 * 管理画面から送信された検索プリセットIDを保存する。
 *
 * 役割:
 * - 管理画面のselectで選ばれたプリセットIDを受け取る
 * - 存在するプリセットIDか確認する
 * - lgjh_active_search_preset_id option に保存する
 *
 * @return string 管理画面に表示するメッセージHTML
 */
function lgjh_handle_save_search_preset()
{
    $preset_id = isset($_POST['lgjh_search_preset_id'])
        ? sanitize_text_field(wp_unslash($_POST['lgjh_search_preset_id']))
        : '';

    $presets = lgjh_get_search_condition_presets();

    if (!isset($presets[$preset_id])) {
        return '<div class="notice notice-error"><p>'
            . '存在しない検索プリセットです。'
            . '</p></div>';
    }

    update_option('lgjh_active_search_preset_id', $preset_id);

    return '<div class="notice notice-success"><p>'
        . '検索プリセットを保存しました：'
        . esc_html($presets[$preset_id]['preset_label'])
        . '</p></div>';
}

/**
 * 検索条件のデフォルト値を取得する。
 *
 * 役割:
 * - 他のWordPress環境に入れた直後でも動く初期検索条件を持つ
 * - 管理画面で保存済みの検索条件がない場合の初期値として使う
 * - fetcher.php のPOST body生成で参照する
 *
 * @return array 検索条件のデフォルト値
 */
function lgjh_get_default_search_conditions()
{
    return [
        /*
         * 求人区分
         *
         * kjKbnRadioBtn に対応
         * 1: 一般求人
         * 5: 障害者求人など、ハローワーク側の値に合わせる
         */
        'job_type'       => '5',
        'job_type_label' => '障害者求人',

        /*
         * 就業場所
         *
         * todohukenHidden に対応
         * 27: 大阪府
         */
        'prefecture_code'  => '27',
        'prefecture_label' => '大阪府',

        /*
         * フリーワード検索
         *
         * freeWordInput / freeWordRadioBtn に対応
         * keyword_mode:
         * - or  => freeWordRadioBtn = 0
         * - and => freeWordRadioBtn = 1
         */
        'keyword'      => '',
        'keyword_mode' => 'or',

        /*
         * 含めないキーワード
         *
         * nOTKNSKFreeWordInput に対応
         */
        'exclude_keyword' => '',

        /*
         * 職種条件
         *
         * daiEasyShokusyuBox / easyShokusyuBox に対応
         *
         * 注意:
         * ハローワークPOSTでは同じキーが複数回出ることがある。
         * PHPの連想配列で同じキーを複数書くと後勝ちになるため、
         * 内部データでは配列で持つ。
         */
        'job_categories' => [
            [
                'large' => '04',
                'small' => '0400',
                'label' => '事務的職業',
            ],
            [
                'large' => '13',
                'small' => '1300',
                'label' => '情報処理・通信技術者',
            ],
        ],

        /*
         * 詳細条件コード
         *
         * jyoukenBox に対応
         * 例: 20260101001
         */
        'condition_code' => '20260101001',

        /*
         * 在宅勤務
         *
         * tatZngyCKBox に対応
         * 1: 在宅勤務を含む
         */
        'remote_work'       => '1',
        'remote_work_label' => '在宅勤務を含む',

        /*
         * 表示件数
         *
         * fwListNaviDispTop
         * fwListNaviDispBtm
         * fwListNaviDisp
         * に対応
         */
        'display_count' => '30',

        /*
         * 並び順
         *
         * fwListNaviSortTop
         * fwListNaviSortBtm
         * fwListNaviSort
         * に対応
         */
        'sort_order' => '1',

        /*
         * ページ番号
         *
         * fwListNowPage / fwListLeftPage に対応
         */
        'page'      => '1',
        'left_page' => '1',

        /*
         * 将来の管理画面表示用ラベル
         *
         * 今は固定表示・確認用。
         */
        'job_category_label' => '事務的職業 / 情報処理・通信技術者',
    ];
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

    // フリーワードなし検索も許可するため、空文字のまま保存する

    $conditions = lgjh_get_search_conditions();

    $conditions['keyword'] = $keyword;
    $conditions['keyword_mode'] = $keyword_mode;

    update_option('lgjh_search_conditions', $conditions);

    return '<div class="notice notice-success"><p>'
        . '検索条件を保存しました。'
        . '</p></div>';
}

/**
 * ハローワーク検索POST用のbodyを作成する。
 *
 * 役割:
 * - lgjh_get_search_conditions() で取得した検索条件を
 *   ハローワークに送信できるPOST bodyへ変換する
 * - 複数職種のように同じキーを複数回送る項目にも対応する
 *
 * 注意:
 * - daiEasyShokusyuBox / easyShokusyuBox は複数回出る可能性がある
 * - PHPの連想配列では同じキーを複数持てないため、最終的に文字列で返す
 *
 * @param array $conditions 検索条件
 * @return string application/x-www-form-urlencoded 形式のPOST body
 */
function lgjh_build_hellowork_search_body($conditions)
{
    $keyword_for_hellowork = lgjh_convert_keyword_for_hellowork(
        $conditions['keyword'] ?? ''
    );

    $keyword_mode = ($conditions['keyword_mode'] ?? 'or') === 'or'
        ? '0'
        : '1';

    $display_count = $conditions['display_count'] ?? '30';
    $sort_order    = $conditions['sort_order'] ?? '1';
    $page          = $conditions['page'] ?? '1';
    $left_page     = $conditions['left_page'] ?? '1';

    $base_body = [
        // 求職番号
        'kSNoJo' => '',
        'kSNoGe' => '',

        // 求人区分
        'kjKbnRadioBtn' => $conditions['job_type'] ?? '5',

        // 就業場所
        'todohukenHidden' => $conditions['prefecture_code'] ?? '27',
        'ensenHidden' => '',
        'roudousijyoHidden' => '',

        // フリーワード
        'freeWordInput' => $keyword_for_hellowork,
        'freeWordRadioBtn' => $keyword_mode,
        'nOTKNSKFreeWordInput' => $conditions['exclude_keyword'] ?? '',

        // 詳細条件
        'jyoukenBox' => $conditions['condition_code'] ?? '',

        // 在宅勤務
        'tatZngyCKBox' => $conditions['remote_work'] ?? '1',

        // その他条件
        'nenreiInput' => '',
        'menkyoSkku1In' => '',
        'menkyoSkku2In' => '',
        'menkyoSkku3In' => '',
        'shgJnStaJiCmbBoxHH' => '',
        'shgJnStaFunCmbBoxMM' => '',
        'shgJnEndJiCmbBoxHH' => '',
        'shgJnEndFunCmbBoxMM' => '',
        'jkgiRadioBtn' => '0',
        'tnseiRadioBtn' => '0',
        'tnseiCmbBox' => '',
        'jginSuRadioBtn' => '0',
        'kiboSuruSngBrui1In' => '',
        'kiboSuruSngBrui2In' => '',
        'kiboSuruSngBrui3In' => '',

        // 検索ボタン
        'searchBtn' => ' 検索する',

        // 求人番号検索欄
        'kJNoJo1' => '',
        'kJNoGe1' => '',
        'kJNoJo2' => '',
        'kJNoGe2' => '',
        'kJNoJo3' => '',
        'kJNoGe3' => '',
        'kJNoJo4' => '',
        'kJNoGe4' => '',
        'kJNoJo5' => '',
        'kJNoGe5' => '',

        // 事業所番号検索欄
        'jGSHNoJo' => '',
        'jGSHNoChuu' => '',
        'jGSHNoGe' => '',

        // 一覧表示設定
        'kyujinkensu' => '0',
        'fwListNaviSortTop' => $sort_order,
        'fwListNaviDispTop' => $display_count,
        'fwListNaviSortBtm' => $sort_order,
        'fwListNaviDispBtm' => $display_count,
        'fwListNowPage' => $page,
        'fwListLeftPage' => $left_page,
        'fwListNaviDisp' => $display_count,
        'fwListNaviSort' => $sort_order,

        // 団体ID
        'iNFTeikyoRiyoDantaiID' => '',

        // 画面制御
        'searchClear' => '0',
        'kiboSuruSKSU1Hidden' => '',
        'kiboSuruSKSU2Hidden' => '',
        'kiboSuruSKSU3Hidden' => '',
        'summaryDisp' => 'true',
        'searchInitDisp' => '1',
        'hiddenViewedKyujinList' => '',
        'CHECKEDKJNOLIST' => '',
        'screenId' => 'GECA110010',
        'action' => '',

        // 補助入力系
        'codeAssistType' => '',
        'codeAssistKind' => '',
        'codeAssistCode' => '',
        'codeAssistItemCode' => '',
        'codeAssistItemName' => '',
        'codeAssistDivide' => '',

        // ボタン許可リスト
        'maba_vrbs' => 'infTkRiyoDantaiBtn,searchShosaiBtn,searchBtn,searchNoBtn,searchClearBtn,searchNoClearBtn,searchNoClearBtn_mobile,dispDetailBtn,kyujinhyoBtn,checkedKyujinViewBtn,checkedKyujinhyoIppanBtn,checkedKyujinhyoDsBtn,changeSearchCond',

        // 事前チェック
        'preCheckFlg' => 'false',
    ];

    $body_parts = [];

    foreach ($base_body as $key => $value) {
        $body_parts[] = rawurlencode($key) . '=' . rawurlencode((string) $value);
    }

    $job_categories = $conditions['job_categories'] ?? [];

    if (is_array($job_categories)) {
        foreach ($job_categories as $category) {
            $large = $category['large'] ?? '';
            $small = $category['small'] ?? '';

            if ($large !== '') {
                $body_parts[] = 'daiEasyShokusyuBox=' . rawurlencode((string) $large);
            }

            if ($small !== '') {
                $body_parts[] = 'easyShokusyuBox=' . rawurlencode((string) $small);
            }
        }
    }

    return implode('&', $body_parts);
}

/**
 * ハローワーク送信用にフリーワードを変換する。
 *
 * 管理画面では半角英数字で入力できるようにし、
 * ハローワーク送信直前に全角英数字へ変換する。
 *
 * 例:
 * PHP JavaScript Laravel WordPress
 * ↓
 * ＰＨＰ　ＪａｖａＳｃｒｉｐｔ　Ｌａｒａｖｅｌ　ＷｏｒｄＰｒｅｓｓ
 *
 * @param string $keyword 管理画面で入力されたフリーワード。
 * @return string ハローワーク送信用に変換したフリーワード。
 */
function lgjh_convert_keyword_for_hellowork($keyword)
{
    if (empty($keyword)) {
        return '';
    }

    // 前後の空白を除去
    $keyword = trim($keyword);

    // 半角英数字・半角スペースを全角へ変換
    return mb_convert_kana($keyword, 'ASKV', 'UTF-8');
}
