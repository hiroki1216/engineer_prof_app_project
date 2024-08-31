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
        $class_file_path = app_path().'/Packages/Domain/Entities/'.$class_name.'.php';

        if (! file_exists($class_file_path)) {
            $this->error('クラスファイルが存在しません。'.PHP_EOL.'パス：'.$class_file_path);

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
        $interface_use_statements = array_merge($use_statements, ['App\Packages\Notification\Class\\'.$data_model_builder_name]);
        $interface_content = $this->createInterface($class_name, $data_model_builder_interface_name, $fields, $interface_use_statements, $param_docs);

        // クラスのuse文を追加
        $use_statements[] = 'App\Packages\Notification\Interface\\'.$data_model_builder_interface_name;
        $data_model_builder_content = $this->createDataModelBuilder($data_model_builder_name, $data_model_builder_interface_name, $fields, $use_statements, $param_docs);

        $this->writeToFile($data_model_builder_content, app_path().'/Packages/Notification/Class/', $class_name.'DataModelBuilder.php');
        $this->writeToFile($interface_content, app_path().'/Packages/Notification/Interface/', $class_name.'NotifierInterface.php');
        // クラスファイルにnotifyメソッドを追加
        $this->addNotifyMethod($class_file_path, $class_file_content, $data_model_builder_interface_name);
    }

    /**
     * クラスファイルを読み込みます。
     *
     * @param string $class_file_path 入力クラスファイルのパス
     */
    private function readClassFile(string $class_file_path): string
    {
        return file_get_contents($class_file_path);
    }

    /**
     * クラスファイルからフィールド変数を抽出します。
     *
     * @param string $class_file_content クラスファイルの内容
     *
     * @return array フィールド変数の連想配列
     */
    private function getFieldVariables(string $class_file_content): array
    {
        $pattern = '/(public|protected|private)\s+(\?\w+|\w+)\s+\$(\w+)/';
        preg_match_all($pattern, $class_file_content, $matches);

        return array_combine($matches[3], $matches[2]);
    }

    /**
     * クラスファイルから@paramのドキュメントを抽出します。
     *
     * @param string $class_file_content クラスファイルの内容
     *
     * @return array パラメータのドキュメントの連想配列
     */
    private function getParamDocs(string $class_file_content): array
    {
        $pattern = '/@param\s+([a-zA-Z0-9_\\\\\[\]\|]+)\s+\$(\w+)/';
        preg_match_all($pattern, $class_file_content, $matches);

        return array_combine($matches[2], $matches[1]);
    }

    /**
     * クラスファイルからuse文を抽出します。
     * 抽出対象は、クラスファイルのuse文、namespace、docコメントの@paramの型です。
     *
     * @param string $class_file_content クラスファイルの内容
     *
     * @return array use文の配列
     */
    private function extractUseStatements(string $class_file_content): array
    {
        $use_statements = [];

        // use文を抽出
        $pattern = '/^use\s+([a-zA-Z0-9_\\\\]+);/m';
        preg_match_all($pattern, $class_file_content, $matches);
        $use_statements = array_filter($matches[1], function ($use_statement) {
            // App\Packages\Notification\Interface\のuse文は除外(複数回コマンドが実行され)
            return strpos($use_statement, 'App\\Packages\\Notification\\Interface\\') !== 0;
        });

        // namespaceを抽出
        $namespace_pattern = '/^namespace\s+([a-zA-Z0-9_\\\\]+);/m';
        preg_match($namespace_pattern, $class_file_content, $namespace_matches);
        $namespace = $namespace_matches[1] ?? '';

        // 既存のuse文の型名を取得
        $existing_use_types = array_map(function ($use_statement) {
            $parts = explode('\\', $use_statement);

            return end($parts);
        }, $use_statements);

        // docコメントから型情報を抽出
        $doc_comment_pattern = '/@param\s+([a-zA-Z0-9_\\\\\[\]\|]+)\s+\$/m';
        preg_match_all($doc_comment_pattern, $class_file_content, $doc_matches);
        foreach ($doc_matches[1] as $doc_type) {
            $types = explode('|', $doc_type);
            foreach ($types as $type) {
                // 配列の要素を表す型を抽出
                $type = str_replace('[]', '', $type);
                // プリミティブ型でない場合にnamespaceをつける
                if (! in_array($type, ['int', 'float', 'string', 'bool', 'array', 'object', 'null', 'mixed']) && false === strpos($type, '\\')) {
                    // 既存のuse文の型名と重複しない場合に追加
                    if (! in_array($type, $existing_use_types)) {
                        $use_statements[] = $namespace.'\\'.$type;
                    }
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
        $use_statements_str = implode(PHP_EOL, array_map(fn ($use) => 'use '.$use.';', $use_statements));

        $data_model_builder = '<?php'.PHP_EOL.PHP_EOL;
        $data_model_builder .= 'namespace App\Packages\Notification\Class;'.PHP_EOL.PHP_EOL;
        $data_model_builder .= $use_statements_str.PHP_EOL.PHP_EOL;
        $data_model_builder .= '/**'.PHP_EOL;
        $data_model_builder .= ' * '.$data_model_builder_name.PHP_EOL;
        $data_model_builder .= ' * このクラスはエンティティのプロパティをリポジトリへ通知するためのオブジェクトです。'.PHP_EOL;
        $data_model_builder .= ' * エンティティクラスでは、このクラスにプロパティの情報を通知するためのメソッド(notify())が用意されています。'.PHP_EOL;
        $data_model_builder .= ' * このクラスのインスタンスをエンティティクラスのプロパティ値で初期化し、リポジトリのメソッドの引数として渡します。'.PHP_EOL;
        $data_model_builder .= ' */'.PHP_EOL;
        $data_model_builder .= 'class '.$data_model_builder_name.' implements '.$interface_name.PHP_EOL;
        $data_model_builder .= '{'.PHP_EOL;
        $data_model_builder .= '    // フィールド変数'.PHP_EOL;

        foreach ($fields as $field => $type) {
            $data_model_builder .= '    private '.$type.' $'.$field.';'.PHP_EOL;
        }

        $data_model_builder .= PHP_EOL.'    // セッターメソッド'.PHP_EOL;

        foreach ($fields as $field => $type) {
            $camelCaseField = ucfirst(Str::camel($field));
            $param_doc = $param_docs[$field] ?? $type;
            $data_model_builder .= '    /**'.PHP_EOL;
            $data_model_builder .= '     * @param '.$param_doc.' $'.$field.PHP_EOL;
            $data_model_builder .= '     */'.PHP_EOL;
            $data_model_builder .= '    public function set'.$camelCaseField.'('.$type.' $'.$field.'): void'.PHP_EOL;
            $data_model_builder .= '    {'.PHP_EOL;
            $data_model_builder .= '        $this->'.$field.' = $'.$field.';'.PHP_EOL;
            $data_model_builder .= '    }'.PHP_EOL.PHP_EOL;
        }

        $data_model_builder .= '    // ビルドメソッド'.PHP_EOL;
        $data_model_builder .= '    public function build(): '.$data_model_builder_name.PHP_EOL;
        $data_model_builder .= '    {'.PHP_EOL;
        $data_model_builder .= '        $builder = new '.$data_model_builder_name.'();'.PHP_EOL;
        foreach ($fields as $field => $type) {
            $data_model_builder .= '        $builder->set'.ucfirst(Str::camel($field)).'($this->'.$field.');'.PHP_EOL;
        }
        $data_model_builder .= '        return $builder;'.PHP_EOL;
        $data_model_builder .= '    }'.PHP_EOL;
        $data_model_builder .= '}';

        return $data_model_builder;
    }

    /**
     * データモデルビルダーインターフェースを作成します。
     */
    private function createInterface(string $class_name, string $interface_name, array $fields, array $use_statements, array $param_docs): string
    {
        $use_statements_str = implode(PHP_EOL, array_map(fn ($use) => 'use '.$use.';', $use_statements));

        $interface = '<?php'.PHP_EOL.PHP_EOL;
        $interface .= 'namespace App\Packages\Notification\Interface;'.PHP_EOL.PHP_EOL;
        $interface .= $use_statements_str.PHP_EOL.PHP_EOL;
        $interface .= 'interface '.$interface_name.PHP_EOL;
        $interface .= '{'.PHP_EOL;
        $interface .= '    // セッターメソッド'.PHP_EOL;

        foreach ($fields as $field => $type) {
            $camelCaseField = ucfirst(Str::camel($field));
            $param_doc = $param_docs[$field] ?? $type;
            $interface .= '    /**'.PHP_EOL;
            $interface .= '     * @param '.$param_doc.' $'.$field.PHP_EOL;
            $interface .= '     */'.PHP_EOL;
            $interface .= '    public function set'.$camelCaseField.'('.$type.' $'.$field.'): void;'.PHP_EOL.PHP_EOL;
        }

        $interface .= '    // ビルドメソッド'.PHP_EOL;
        $interface .= '    public function build(): '.$class_name.'DataModelBuilder;'.PHP_EOL;
        $interface .= '}';

        return $interface;
    }

    /**
     * ファイルに書き込みます。
     *
     * @param string $content   書き込む内容
     * @param string $directory ディレクトリ
     * @param string $file_name ファイル名
     */
    private function writeToFile(string $content, string $directory, string $file_name): void
    {
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0o755, true);
        }

        $file_path = $directory.$file_name;
        file_put_contents($file_path, $content);
    }

    /**
     * クラスファイルにnotifyメソッドを追加します。
     *
     * @param string $class_file_path    クラスファイルのパス
     * @param string $class_file_content クラスファイルの内容
     * @param string $data_model_builder_interface_name インターフェース名
     */
    private function addNotifyMethod(string $class_file_path, string $class_file_content, string $data_model_builder_interface_name): void
    {
        if (false === strpos($class_file_content, 'public function notify(')) {
            // EngineerNotifierInterfaceのuse文を追加
            $class_namespace = 'App\\Packages\\Notification\\Interface\\'.$data_model_builder_interface_name;
            if (false === strpos($class_file_content, 'use '.$class_namespace.';')) {
                $class_file_content = preg_replace(
                    '/namespace\s+[^;]+;/',
                    '$0'.PHP_EOL.'use '.$class_namespace.';',
                    $class_file_content
                );
            }

            $notify_method = PHP_EOL.'    /**'.PHP_EOL;
            $notify_method .= '     * 通知オブジェクトを受け取り、エンティティのプロパティを通知します。'.PHP_EOL;
            $notify_method .= '     * 目的としては、エンティティクラスのプロパティ値をprivateにしたまま、他のクラスにプロパティ値を渡すためです。'.PHP_EOL;
            $notify_method .= '     *'.PHP_EOL;
            $notify_method .= '     * @param '.$data_model_builder_interface_name.' $note'.PHP_EOL;
            $notify_method .= '     * @return void'.PHP_EOL;
            $notify_method .= '     */'.PHP_EOL;
            $notify_method .= '    public function notify('.$data_model_builder_interface_name.' $note): void'.PHP_EOL;
            $notify_method .= '    {'.PHP_EOL;
            foreach ($this->getFieldVariables($class_file_content) as $field => $type) {
                $camelCaseField = ucfirst(Str::camel($field));
                $notify_method .= '        $note->set'.$camelCaseField.'($this->'.$field.');'.PHP_EOL;
            }
            $notify_method .= '    }'.PHP_EOL;

            $class_file_content = preg_replace('/\}\s*$/', $notify_method.'}', $class_file_content);
            file_put_contents($class_file_path, $class_file_content);
        }
    }
}
