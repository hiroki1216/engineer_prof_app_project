# engineer_prof_app_project
エンジニア用のプロフィールアプリケーション 

## 開発用DBのセットアップ

### .envの更新
~~~sh
DB_CONTAINER_NAME = "app_db"
DB_ROOT_PASSWORD = "root"
DB_NAME = "profile_app_db"
DB_VOLUME = "./mariaDB/db_data"
DB_PORT = "3306"
~~~

### colimaのインストール・起動(docker実行環境)
※dockerデスクトップでも可。
※パフォーマンスのや商用でも無料で使うなら、docker-engineとlinux環境はインストールするのが良さそう。

https://github.com/abiosoft/colima

~~~sh
brew install colima
~~~
~~~sh
colima start
~~~

### docker環境の構築
~~~sh
cd ./docker
sh setup.sh
~~~

### .env(laravel app)の設定
~~~sh
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=profile_app_db
DB_USERNAME=root
DB_PASSWORD=root
~~~

## php-cs-fixerの導入
https://github.com/PHP-CS-Fixer/PHP-CS-Fixer

~~~sh
cd src
mkdir -p tools/php-cs-fixer
composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer
~~~

### 設定ファイルの作成（ローカル用）
https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/config.rst
~~~sh
cd src
touch .php-cs-fixer.php
vi .php-cs-fixer.php
~~~

#### 設定内容
※.php-cs-fixer.dist.phpの内容に合わせる
~~~php
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
~~~

### php-cs-fixerの実行
~~~sh
cd src
composer cs:fix
~~~

## laravelの初期設定（パッケージのインストール）
~~~sh
cd src
composer install
~~~