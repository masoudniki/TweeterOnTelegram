<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_chat_id')->index()->unsigned();
            $table->foreign('user_chat_id')
                ->references('chat_id')
                ->on('users');

            $table->bigInteger('tweet_id')->index()->unsigned();
            $table->foreign('tweet_id')
                ->references('id')
                ->on('tweets');

            $table->boolean('action');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likes');
    }
}
