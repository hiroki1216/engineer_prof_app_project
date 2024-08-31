<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * クラスのデータモデルビルダー（通知オブジェクト）を作成します。
 * 作成されるファイルは以下の2つです。
 * 1. データモデルビルダークラス（App/Packages/Notification/Class/ディレクトリに作成）
 * 2. データモデルビルダーインターフェース（App/Packages/Notification/Interface/ディレクトリに作成）.
 *
 * 使い方)php artisan make:dmbuilder User{クラス名}
 */
class MakeDataModelBuilder extends Command
{
    protected $signature = 'make:dmbuilder {class_file_name}';
    protected $description = 'クラスのデータモデルビルダー（通知オブジェクト）を作成します。例)php artisan make:dmbuilder User';

    public function handle()
    {
        $class_name = $this->argument('class_file_name');
        $data_model_builder_name = $class_name.'DataModelBuilder';
        $data_model_builder_interface_name = $class_name.'NotifierInterface';
        $class_file_path = app_path("Packages/Domain/Entities/{$class_name}.php");

        if (! file_exists($class_file_path)) {
            $this->error("クラスファイルが存在しません。\nパス：{$class_file_path}");

            return;
        }

        $class_file_content = $this->readClassFile($class_file_path);
        $fields = $this->getFieldVariables($class_file_content);
        $param_docs = $this->getParamDocs($class_file_content);

        if (empty($fields)) {
            $this->error('フィールド変数が存在しません。');

            return;
        }

        $use_statements = $this->extractUseStatements($class_file_content);

        // インターフェースのuse文を追加
        $interface_use_statements = array_merge($use_statements, ["App\\Packages\\Notification\\Class\\{$data_model_builder_name}"]);
        $interface_content = $this->createInterface($data_model_builder_interface_name, $fields, $interface_use_statements, $param_docs);

        // クラスのuse文を追加
        $use_statements[] = "App\\Packages\\Notification\\Interface\\{$data_model_builder_interface_name}";
        $data_model_builder_content = $this->createDataModelBuilder($data_model_builder_name, $data_model_builder_interface_name, $fields, $use_statements, $param_docs);

        $this->writeToFile($data_model_builder_content, app_path('Packages/Notification/Class/'), "{$data_model_builder_name}.php");
        $this->writeToFile($interface_content, app_path('Packages/Notification/Interface/'), "{$data_model_builder_interface_name}.php");

        // クラスファイルにnotifyメソッドを追加
        $this->addNotifyMethod($class_file_path, $class_file_content, $data_model_builder_interface_name);
    }

    /**
     * クラスファイルを読み込みます。
     */
    private function readClassFile(string $class_file_path): string
    {
        return file_get_contents($class_file_path);
    }

    /**
     * クラスファイルからフィールド変数を取得します。
     */
    private function getFieldVariables(string $class_file_content): array
    {
        preg_match_all('/(public|protected|private)\s+(\?\w+|\w+)\s+\$(\w+)/', $class_file_content, $matches);

        return array_combine($matches[3], $matches[2]);
    }

    /**
     * クラスファイルから@paramの内容を取得します。
     */
    private function getParamDocs(string $class_file_content): array
    {
        preg_match_all('/@param\s+([a-zA-Z0-9_\\\\\[\]\|]+)\s+\$(\w+)/', $class_file_content, $matches);

        return array_combine($matches[2], $matches[1]);
    }

    /**
     * クラスファイルからuse文を抽出します。
     */
    private function extractUseStatements(string $class_file_content): array
    {
        preg_match_all('/^use\s+([a-zA-Z0-9_\\\\]+);/m', $class_file_content, $matches);
        $use_statements = array_filter($matches[1], fn ($use) => 0 !== strpos($use, 'App\\Packages\\Notification\\Interface\\'));

        preg_match('/^namespace\s+([a-zA-Z0-9_\\\\]+);/m', $class_file_content, $namespace_matches);
        $namespace = $namespace_matches[1] ?? '';

        $existing_use_types = array_map(fn ($use) => end(explode('\\', $use)), $use_statements);

        preg_match_all('/@param\s+([a-zA-Z0-9_\\\\\[\]\|]+)\s+\$/m', $class_file_content, $doc_matches);
        foreach ($doc_matches[1] as $doc_type) {
            foreach (explode('|', $doc_type) as $type) {
                $type = str_replace('[]', '', $type);
                if (! in_array($type, ['int', 'float', 'string', 'bool', 'array', 'object', 'null', 'mixed']) && false === strpos($type, '\\') && ! in_array($type, $existing_use_types)) {
                    $use_statements[] = "{$namespace}\\{$type}";
                }
            }
        }

        return array_unique($use_statements);
    }

    /**
     * データモデルビルダークラスを作成します。
     */
    private function createDataModelBuilder(string $data_model_builder_name, string $interface_name, array $fields, array $use_statements, array $param_docs): string
    {
        $use_statements_str = implode(PHP_EOL, array_map(fn ($use) => "use {$use};", $use_statements));

        $data_model_builder = <<<PHP
            <?php

            namespace App\Packages\Notification\Class;

            {$use_statements_str}

            /**
             * {$data_model_builder_name}
             * このクラスはエンティティのプロパティをリポジトリへ通知するためのオブジェクトです。
             * エンティティクラスでは、このクラスにプロパティの情報を通知するためのメソッド(notify())が用意されています。
             * このクラスのインスタンスをエンティティクラスのプロパティ値で初期化し、リポジトリのメソッドの引数として渡します。
             */
            class {$data_model_builder_name} implements {$interface_name}
            {
                // フィールド変数
            PHP;

        foreach ($fields as $field => $type) {
            $data_model_builder .= "    private {$type} \${$field};".PHP_EOL;
        }

        $data_model_builder .= PHP_EOL.'    // セッターメソッド'.PHP_EOL;

        foreach ($fields as $field => $type) {
            $camelCaseField = ucfirst(Str::camel($field));
            $param_doc = $param_docs[$field] ?? $type;
            $data_model_builder .= <<<PHP
                    /**
                     * @param {$param_doc} \${$field}
                     */
                    public function set{$camelCaseField}({$type} \${$field}): void
                    {
                        \$this->{$field} = \${$field};
                    }

                PHP;
        }

        $data_model_builder .= <<<PHP
                // ビルドメソッド
                public function build(): {$data_model_builder_name}
                {
                    \$builder = new {$data_model_builder_name}();
            PHP;

        foreach ($fields as $field => $type) {
            $data_model_builder .= '        $builder->set'.ucfirst(Str::camel($field))."(\$this->{$field});".PHP_EOL;
        }

        $data_model_builder .= <<<PHP
                    return \$builder;
                }
            }
            PHP;

        return $data_model_builder;
    }

    /**
     * データモデルビルダーインターフェースを作成します。
     */
    private function createInterface(string $interface_name, array $fields, array $use_statements, array $param_docs): string
    {
        $use_statements_str = implode(PHP_EOL, array_map(fn ($use) => "use {$use};", $use_statements));

        $interface = <<<PHP
            <?php

            namespace App\Packages\Notification\Interface;

            {$use_statements_str}

            interface {$interface_name}
            {
                // セッターメソッド
            PHP;

        foreach ($fields as $field => $type) {
            $camelCaseField = ucfirst(Str::camel($field));
            $param_doc = $param_docs[$field] ?? $type;
            $interface .= <<<PHP
                    /**
                     * @param {$param_doc} \${$field}
                     */
                    public function set{$camelCaseField}({$type} \${$field}): void;

                PHP;
        }

        $interface .= <<<PHP
                // ビルドメソッド
                public function build(): {$interface_name};
            }
            PHP;

        return $interface;
    }

    /**
     * ファイルに書き込みます。
     */
    private function writeToFile(string $content, string $directory, string $file_name): void
    {
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0o755, true);
        }

        file_put_contents("{$directory}{$file_name}", $content);
    }

    /**
     * クラスファイルにnotifyメソッドを追加します。
     */
    private function addNotifyMethod(string $class_file_path, string $class_file_content, string $data_model_builder_interface_name): void
    {
        if (false === strpos($class_file_content, 'public function notify(')) {
            $class_namespace = "App\\Packages\\Notification\\Interface\\{$data_model_builder_interface_name}";
            if (false === strpos($class_file_content, "use {$class_namespace};")) {
                $class_file_content = preg_replace('/namespace\s+[^;]+;/', '$0'.PHP_EOL."use {$class_namespace};", $class_file_content);
            }

            $notify_method = <<<PHP

                    /**
                     * 通知オブジェクトを受け取り、エンティティのプロパティを通知します。
                     * 目的としては、エンティティクラスのプロパティ値をprivateにしたまま、他のクラスにプロパティ値を渡すためです。
                     *
                     * @param {$data_model_builder_interface_name} \$note
                     * @return void
                     */
                    public function notify({$data_model_builder_interface_name} \$note): void
                    {
                PHP;

            foreach ($this->getFieldVariables($class_file_content) as $field => $type) {
                $camelCaseField = ucfirst(Str::camel($field));
                $notify_method .= "        \$note->set{$camelCaseField}(\$this->{$field});".PHP_EOL;
            }

            $notify_method .= <<<PHP
                    }
                }
                PHP;

            $class_file_content = preg_replace('/\}\s*$/', $notify_method, $class_file_content);
            file_put_contents($class_file_path, $class_file_content);
        }
    }
}
