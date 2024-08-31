<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('engineer_id');
            $table->string('name');
            $table->date('start_on');
            $table->date('end_on')->nullable();
            $table->string('role');
            $table->integer('number_of_team_members');
            $table->text('overview_of_project');
            $table->text('overview_of_job_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
