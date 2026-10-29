<?php

// ストレッチタスクの設定。
// items：アカウント作成時に、ユーザーの tasks テーブルへコピーする最初のタスク
// options：ユーザーがタスクを作るときに選べる秒数
return [
    'items' => [
        ['name' => '頭を左右に倒す', 'countdown_seconds' => 10, 'part' => '首', 'effect' => '血流の促進・頭痛の軽減'],
        ['name' => '頭を前に倒す', 'countdown_seconds' => 10, 'part' => '首', 'effect' => '血流の促進・頭痛の軽減'],
        ['name' => '腕を伸ばす', 'countdown_seconds' => 10, 'part' => '肩・背中', 'effect' => '姿勢の改善・肩こりの軽減'],
        ['name' => '頭を360度回転させる', 'countdown_seconds' => 30, 'part' => '首', 'effect' => '血流の促進・頭痛の解消'],
        ['name' => '両手を肩に添えて、肘をゆっくり時計回りに回す', 'countdown_seconds' => 30, 'part' => '肩', 'effect' => '肩こり軽減'],
        ['name' => '両手を肩に添えて、肘をゆっくり反時計回りに回す', 'countdown_seconds' => 30, 'part' => '肩', 'effect' => '肩こり軽減'],
        ['name' => '深呼吸を行う', 'countdown_seconds' => 30, 'part' => '全身', 'effect' => '集中力の向上'],
    ],

    'options' => [
        10 => '10秒',
        15 => '15秒',
        20 => '20秒',
        30 => '30秒',
        40 => '40秒',
        60 => '60秒',
        90 => '90秒',
    ],
];
