<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaseTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('directories', function (Blueprint $table) {
            $table->increments('id');
            $table->text('path');
        });

        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('directory_id');
            $table->text('path');
            $table->unsignedInteger('filesize')->default(0);
            $table->string('filename', 255)->default('');
            $table->integer('duration')->default(0);

            $table->index('directory_id');
            $table->foreign('directory_id')->references('id')->on('directories')->onDelete('cascade');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('');
        });

        Schema::create('files_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('file_id')->nullable();
            $table->unsignedInteger('tag_id')->nullable();
            $table->integer('start_time')->default(0);
            $table->integer('duration')->default(0);

            $table->index('file_id');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');

            $table->index('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files_tags');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('files');
        Schema::dropIfExists('directories');
    }
}
