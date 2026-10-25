# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

健康管理アプリ (kenkou-app)：ストレッチを習慣化するためのアプリ。ユーザーが作業タイマーを設定し、終了したらストレッチタスクを行い、日々の実績をグラフで表示する。画面の文言・バリデーションメッセージ・コミットメッセージは日本語。

技術スタック：Laravel 13 / PHP 8.4、Livewire 4 + Flux Pro（UI コンポーネントのみ使用）、Tailwind 4 + Vite、PostgreSQL 18（Laravel Sail / Docker）。本番は Fly.io（アプリ名 `kenkou-app`、リージョン `nrt`）。

## 作業の流れ

コードを変更したら、**実装 → reviewer（レビュー）→ tester（テスト）** の順に進める。
- 実装は `coding-manual` スキル（業務マニュアル）に従い、最後に報告義務の形式で報告する。
- サブエージェントは作業中にユーザーへ質問できない。読ませたいファイルやレビュー対象は、呼び出すときの依頼文で指定する。

## コマンド

ローカル開発は Docker（Sail のコンテナ）上で動かす（`.env` が `DB_HOST=pgsql`）。
**Windows では `./vendor/bin/sail` が使えず（Unsupported operating system）、ローカルの PHP（8.4.0）も依存パッケージの要件（8.4.1 以上）を満たさない**ため、artisan は `docker compose exec laravel.test` 経由で実行する。

```bash
# Docker を起動する（実行前にユーザーに確認する。settings.json で ask にしている）
docker compose up -d --build

# テスト（コンテナ起動中のみ）
docker compose exec laravel.test php artisan test
docker compose exec laravel.test php artisan test --filter=test_create_account_success   # 1件だけ
docker compose exec laravel.test php artisan test tests/Feature/UserTest.php             # ファイル単位
docker compose exec laravel.test composer test   # pint --test → phpstan → test（CI と同じ）

npm run dev / npm run build      # Vite（ホスト側で実行できる）
composer lint                    # Pint で自動整形
```

テストは `RefreshDatabase` を使い、Sail の初期化スクリプトが作成する `testing` データベース（PostgreSQL）で実行される。
`compose.yaml` の `WWWGROUP` / `WWWUSER` は、未設定のとき `1000` を使う（Windows で空になりビルドが失敗するのを防ぐため）。

## Claude Code の設定（`.claude/settings.json`）

次のルールは settings.json で強制している。CLAUDE.md の文章ではなく、設定を変更して調整する。
- `.env` / `.env.*` は読まない（`Read` と `cat` を deny）。
- `git push` と `docker compose up` は毎回ユーザーに確認する（ask）。強制 push（`--force` / `-f`）は deny。
- Edit / Write の後、編集した PHP ファイルだけを Pint で整形する（`.claude/hooks/pint-edited-file.php`）。Blade と JS は対象外。整形に失敗したときは、その内容をユーザーに報告する。

## CI / デプロイ

- `.github/workflows/tests.yml`：`main` への push と PR で `composer setup` → `composer ci:check`（= `composer test`）を実行。Pint や PHPStan のエラーでも CI は失敗する。
- `.github/workflows/fly-deploy.yml`：**`main` または `master` に push すると本番に自動デプロイされる**（`flyctl deploy --remote-only`）。`master` の変更も push した時点で本番に反映されるため、push は必ずユーザーの確認を取る。

## アーキテクチャ

Livewire starter kit から作られているが、実際の構成は **通常のコントローラー + Blade + 素の JavaScript**。Livewire コンポーネントクラスは存在せず、Livewire / Flux は `<flux:*>` の UI コンポーネントと `@livewireStyles/Scripts` のためだけに使っている。Fortify もインストールされている（`app/Actions/Fortify`）が、ログイン・アカウント作成は `routes/web.php` の独自の `LoginController` / `CreateAccountController` で処理している。

画面遷移：ログイン（`/`）→ `total_goals` が未設定なら `/creategoals` → `/operation`（操作ガイド）→ `/home`（タイマー・ストレッチタスク・グラフ）。`/dashboard` は設定画面。

**ユーザーごとの状態はすべて `users` テーブルのカラムに保存している**（専用テーブルはない）：`total_goals`、`daily_tasks`、`countdown_minutes`、`completed_tasks`、`last_reset_date`、`goal_reward`、`sound_enabled` など。`HomeController::show` は `last_reset_date` が今日でなければ `completed_tasks` を 0 にリセットする。

**選択肢の一覧は DB ではなく config ファイルで定義している**。バリデーションは `Rule::in(array_keys(config(...)))` の形で行う。
- `config/stretchtasks.php`：ストレッチタスクの定義（id、name、countdown_seconds、part、effect）
- `config/workcountdown.php`：作業タイマーの分数の選択肢
- `config/goals.php`、`config/dailytaskcontdown.php`：目標期間・1日のタスク回数の選択肢
- `config/operationguide.php`：操作ガイドの内容

**フロントエンド**：Vite のエントリは `resources/js/home.js` の1つだけ。ここから `workcount.js`（作業タイマー・サウンド切り替え）、`taskcount.js`（ストレッチタスクのカウントダウン）、`dailyreport.js`（canvas のグラフ）を初期化する。`resources/views/partials/` の Blade パーシャルが `data-*` 属性でサーバーの値を JS に渡し、JS は `fetch` で `/home/completed-tasks` と `/home/sound-enabled` に POST して保存する（レスポンスは JSON）。日別グラフの履歴は DB ではなく、ユーザー ID ごとのキーでブラウザの `localStorage` に保存している。

## セキュリティルール

### XSS（クロスサイトスクリプティング）対策

- Blade での出力は必ず `{{ }}` を使う（自動で HTML エスケープされる）。`{!! !!}` は使わない。どうしても必要な場合は、ユーザー入力を含まないことを確認し、理由をコメントに書く。
- ユーザーが入力できる値（`email`、`goal_reward` など）は、表示する場所に関わらずエスケープされた状態で出力する。
- PHP の値を `data-*` 属性で JS に渡すときは、スカラー値は `{{ }}`、配列は `@json()` を使う。`@json()` に独自のフラグを渡さない（デフォルトで `<` `>` `'` `"` `&` がエスケープされる）。
- JS で DOM に文字を入れるときは `textContent` を使う。`innerHTML`・`insertAdjacentHTML`・`outerHTML`・`document.write`・`eval` は使わない。
- URL をユーザー入力から組み立てて `href` / `src` に入れない（`javascript:` スキームが入る恐れがある）。

### CSRF（クロスサイトリクエストフォージェリ）対策

- 状態を変更するルート（作成・更新・削除・ログアウト）は必ず POST 以上のメソッドにする。GET で DB を更新しない。
- `<form method="POST">` には、**`<form>` タグの内側に** `@csrf` を書く。`<form>` の外にある `@csrf` は送信されない。
- JS の `fetch` で POST するときは、`layouts/app.blade.php` の `<meta name="csrf-token">` から値を読み、`X-CSRF-TOKEN` ヘッダーに付ける（`dailyreport.js` の `persistCompletedTasks` が手本）。
- `bootstrap/app.php` で `validateCsrfTokens(except: ...)` を使って CSRF チェックを除外しない。
- セッション Cookie の `same_site` は `lax` 以上を維持する（`config/session.php`）。

