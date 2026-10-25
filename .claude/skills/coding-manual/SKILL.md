---
name: coding-manual
description: kenkou-app の業務マニュアル。migration・Controller・config・User.php・Blade を新規作成・変更するときに使う。既存コードに合わせた書き方と、作業後の報告義務を定める。
---
# 業務マニュアル（コーディング規約）

既存コードの書き方に合わせる。セキュリティは CLAUDE.md の「セキュリティルール」に従う。

## 1. migration（`database/migrations/`）
- ユーザーの設定・状態は `users` テーブルにカラムを追加する（新しいテーブルは原則作らない）。
- 既存テーブルへの追加は `add_<内容>_columns_to_users_table` という名前の新しい migration で行う。既存の migration ファイルは書き換えない。
- `up()` / `down()` の両方で `Schema::hasColumn()` を使い、カラムがある／ないときに二重に実行されないようにする。
- 回数や分数は `unsignedSmallInteger`、初期値が必要なものは `->default()`、未設定がありうるものは `->nullable()` にする。
- カラムの意味は日本語コメントで書く。

## 2. Controller（`app/Http/Controllers/`）
- 画面ごとに 1 つのコントローラーにする。メソッドは、GET が `show()` / `create()`、POST が `submit()` / `store()` / `〇〇Update()`。
- 戻り値の型を必ず書く：画面は `View`、フォーム送信は `RedirectResponse`、JS の `fetch` 用は `JsonResponse`。
- バリデーションは `$request->validate([...], [...])` でメソッド内に書き、エラーメッセージは日本語で第 2 引数に指定する。
- 選択肢から選ぶ値は `Rule::in(array_keys(config('〇〇.options')))` で検証する。
- 保存は `auth()->user()->update($validated)` で行う。パスワードは `Hash::make()` を使う。
- 送信後は `redirect()->route('ルート名')` で画面を移動し、メッセージを出すときは `->with('success', '…')` を付ける。
- ルートは `routes/web.php` の `auth` ミドルウェアグループ内に追加し、名前は `画面名.動作`（例：`home.sound-enabled`）にする。

## 3. config（`config/`）
- 画面で選ぶ選択肢は DB ではなく config ファイルに書く。
- 選択肢は `'options' => [保存する値 => '表示ラベル']` の形にする（例：`workcountdown.php`）。
- 表示内容のリストは `'items' => [...]` の形にする（例：`stretchtasks.php`、`operationguide.php`）。
- ファイルの先頭に、何に使う設定かを日本語コメントで書く。

## 4. User.php（`app/Models/User.php`）
カラムを追加したら、次の 3 か所を必ずそろえる。
1. クラス上の `@property` に型付きで追記する
2. `#[Fillable([...])]` に追加する（画面に出さない値は `#[Hidden([...])]` にも追加する）
3. `json` や `boolean` のカラムは `casts()` に追加する

存在しないクラスを参照するリレーションは追加しない。

## 5. Blade（`resources/views/`）
- ログイン後の画面は `@extends('layouts.app-with-header')` + `@section('main')` で作る。
- 画面の部品は `partials/` に分けて、`@include('partials.〇〇')` で読み込む。
- 背景画像は既存画面（`home` / `operation` / `dashboard`）と同じ `<img ... class="absolute inset-0 ... -z-10">` の形にする。
- UI は Flux コンポーネント（`<flux:*>`）と Tailwind のクラスで作る。
- JS に値を渡すときは `data-*` 属性を使う（スカラー値は `{{ }}`、配列は `@json()`）。JS 側では `dataset.*` で読む。
- フォームは `<form method="POST" action="{{ route('…') }}">` の直後に `@csrf` を書く。
- エラー表示は `@error('項目名') … {{ $message }} … @enderror` で行う。

## 6. 作業後の報告義務（必須）
ファイルを作成・変更したら、作業の最後に必ず次を報告する。省略しない。

1. **変更したファイルの一覧**：ファイルパスと、新規作成か変更か
2. **書いた内容**：ファイルごとに、追加・変更したコードをそのまま示す（新規ファイルは全文、変更したファイルは変更した部分）
3. **変更の理由**：なぜその変更が必要か
4. **関連ファイルとのつながり**：migration・User.php・Controller・ルート・config・Blade・JS のうち、あわせて変更したもの。変更が必要なのにしていないものがあれば、それも書く
5. **確認したこと**：実行したコマンド（`composer test` など）と結果。実行していない場合は「未実行」と書く
