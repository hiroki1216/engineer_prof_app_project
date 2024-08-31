<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/app') // 'app' ディレクトリを対象に設定
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true, // 最新の PER-CS 標準に従うルールセット。
        '@PHP83Migration' => true, // PHP 8.3 への移行をサポートするルールセット。
    ])
    ->setFinder($finder)
;