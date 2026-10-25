<?php

// PostToolUse hook：Claude が Edit / Write したファイルだけを Pint で整形する。
// 標準入力に hook の JSON が渡されるので、tool_input.file_path から対象ファイルを取り出す。
// Blade（*.blade.php）と PHP 以外のファイルは Pint の対象外なので何もしない。

$input = json_decode(stream_get_contents(STDIN), true);
$file = $input['tool_input']['file_path'] ?? '';

if ($file === '' || ! str_ends_with($file, '.php') || str_ends_with($file, '.blade.php')) {
    exit(0);
}

$projectDir = dirname(__DIR__, 2);
chdir($projectDir);

passthru(PHP_BINARY.' vendor/bin/pint '.escapeshellarg($file), $exitCode);

// 整形に失敗したときは exit 2 で Claude にエラー内容を伝え、その都度報告させる
if ($exitCode !== 0) {
    fwrite(STDERR, "Pint の整形に失敗しました: {$file}\n");
    exit(2);
}
