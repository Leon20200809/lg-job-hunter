# LG Job Hunter

WordPress管理画面上で求人情報を取得・保存・管理するための、求人収集支援プラグインです。

ハローワークインターネットサービスの検索結果HTMLから求人詳細ページを取得し、解析した求人情報をWordPressのカスタム投稿として保存します。

応募そのものは自動化せず、最終判断と送信は人間が行う **ヒューマン・イン・ザ・ループ** 設計です。

---

## 主な機能

- 求人情報カスタム投稿 `lg_job` の追加
- 求人メタ情報の保存
  - 会社名
  - 勤務地
  - 求人URL
  - 求人番号
  - 給与
  - 雇用形態
  - 仕事内容
  - 応募ステータス
- WordPress管理画面の求人一覧カラム拡張
- Gmail応募ボタンの生成
- 検索条件プリセット管理
- ハローワーク検索結果HTMLの取得
- 検索結果HTMLから求人詳細URLを抽出
- 求人詳細HTMLの取得・解析
- 求人URLによる重複判定
- WordPressカスタム投稿への保存
- Xserver cron による定期実行確認

---

## 現在の処理フロー

```text
検索条件プリセット選択
↓
検索結果HTML取得
↓
求人詳細URL抽出
↓
求人詳細HTML取得
↓
求人情報解析
↓
WordPressカスタム投稿へ保存
↓
重複求人はスキップ
↓
管理画面で確認
↓
Gmail応募文面作成
↓
Xserver cronで定期実行
```

---

## 技術構成

- PHP
- WordPress Plugin
- WordPress HTTP API
- Custom Post Type
- Post Meta
- Admin UI
- DOMDocument
- DOMXPath
- WP_Query
- WordPress Options API
- Gmail compose URL
- Xserver cron

---

## ディレクトリ構成

```text
lg-job-hunter/
├── assets/
├── includes/
│   ├── admin/
│   ├── crawler/
│   ├── logs/
│   ├── meta-boxes/
│   ├── post-types/
│   ├── runner/
│   ├── scheduler/
│   ├── settings/
│   └── storage/
├── templates/
├── .gitignore
├── README.md
├── lg-job-hunter.php
└── uninstall.php
```

---

## 設計方針

このプラグインでは、以下の責務を分離して実装しています。

- カスタム投稿登録
- メタボックス表示・保存
- 管理画面一覧カラム
- 応募ボタン生成
- HTML取得
- HTML解析
- 検索条件管理
- 求人保存
- インポート実行処理
- cron実行処理

取得・解析・保存を分けることで、取得先サイトの仕様変更や保存項目の追加に対応しやすい構成を目指しています。

また、関数docを重視し、各関数の役割・入力・戻り値を明確にする方針で開発しています。

---

## 動作確認

以下の動作を確認済みです。

- ローカルWordPress環境での手動実行
- 別WordPress環境での動作確認
- Xserver上のWordPressでの動作確認
- Xserver cronからの定期実行
- 重複求人の自動スキップ

cron実行例:

```text
total_urls: 30
target_urls: 30
created: 0
skipped: 30
errors: 0
```

---

## 今後追加したい機能

- 投稿後60日を経過した求人の自動整理
- 古い求人投稿のゴミ箱移動
- 検索結果ページのページネーション対応
- 最大取得ページ数の設定
- 複数ページ分の求人詳細URL抽出
- cron実行ログの管理画面保存
- 検索条件の自由入力対応
- CSVエクスポート
- 応募ステータス変更UIの改善
- 求人比較機能
- 取得先ごとのアダプター分離
- 実運用向けのアクセス頻度制御

---

## 注意事項

このリポジトリは、学習・ポートフォリオ・業務改善アイデアの検証を目的とした公開版です。

実運用する場合は、取得先サイトの利用規約、アクセス頻度、取得範囲、利用目的を確認してください。

また、求人情報の取得・保存・利用については、各サービスの規約や関係法令を確認したうえで利用してください。

本プラグインの利用によって発生したトラブルについて、作者は責任を負いません。

---

## Portfolio

このプラグインは、WordPress / PHP を使った業務改善ツール開発の実例です。

取得処理、解析処理、保存処理、管理画面表示を分離し、将来的な拡張や保守をしやすい構成を目指しています。

---

## Author

Leon.C

WordPress / PHP / Laravel / React / Next.js を中心に、業務改善につながるWebアプリケーションやWordPressプラグインを開発しています。

---

## License

MIT License
