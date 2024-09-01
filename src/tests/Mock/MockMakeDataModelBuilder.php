<?php

namespace Tests\Mock;

use App\Console\Commands\MakeDataModelBuilder;
use Illuminate\Support\Facades\Log;

class MockMakeDataModelBuilder extends MakeDataModelBuilder
{
    protected function getClassFilePath(): string
    {
        return base_path('tests/Mock/test-data/'.$this->argument('class_file_name').'.php');
    }

    protected function writeToFile(string $content, string $directory, string $file_name): void
    {
        Log::info(['directory' => $directory, 'file_name' => $file_name]);
    }

}
