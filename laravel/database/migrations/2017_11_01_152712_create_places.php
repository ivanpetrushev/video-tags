<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('places_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255)->default('');
            $table->string('icon', 255)->default('');
        });

        Schema::create('places', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('title', 255)->default('');
            $table->unsignedInteger('category_id')->nullable();
            $table->decimal('lat', 9, 6);
            $table->decimal('lon', 9, 6);
            $table->text('description');
            $table->boolean('is_visited')->default(0);

            $table->index('category_id');
            $table->foreign('category_id')->references('id')->on('places_categories')->onDelete('cascade');
        });

        Schema::create('places_links', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('place_id');
            $table->string('url', 255)->default('');
            $table->string('title', 255)->default('');
            $table->boolean('is_blog')->default(0);

            $table->index('place_id');
            $table->foreign('place_id')->references('id')->on('places')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('places_links');
        Schema::dropIfExists('places');
        Schema::dropIfExists('places_categories');
    }
}
