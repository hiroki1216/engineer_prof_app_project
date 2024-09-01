<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Mock\MockMakeDataModelBuilder;
use Tests\TestCase;

class MakeDataModelBuilderTest extends TestCase
{
    public function test_handle_コマンドの引数で受け取ったクラスファイルをもとに通知オブジェクトを作成すること(): void
    {
        // クラス用のディレクトリとファイル名を設定
        $expected_directory_class = '/Users/hs/Documents/engineer_prof_app_project/src/app/Packages/Notification/Class/';
        $expected_file_name_class = 'TestEntityDataModelBuilder.php';

        // インターフェース用のディレクトリとファイル名を設定
        $expected_directory_interface = '/Users/hs/Documents/engineer_prof_app_project/src/app/Packages/Notification/Interface/';
        $expected_file_name_interface = 'TestEntityNotifierInterface.php';

        // クラス用の書き込み処理が呼び出されることを確認
        Log::shouldReceive('info')
            ->once()
            ->with(['directory' => $expected_directory_class, 'file_name' => $expected_file_name_class]);

        // インターフェース用の書き込み処理が呼び出されることを確認
        Log::shouldReceive('info')
            ->once()
            ->with(['directory' => $expected_directory_interface, 'file_name' => $expected_file_name_interface]);

        // モッククラスをインスタンス化
        $command = new MockMakeDataModelBuilder();
        $command->setLaravel($this->app); // Laravelアプリケーションインスタンスを設定

        // コマンドテスターを使用して引数を設定
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'class_file_name' => 'TestEntity',
        ]);
    }
}
