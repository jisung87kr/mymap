<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('comments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
//            $table->dropForeign(['user_id']);
//            $table->dropForeign(['parent_id']);
//            $this->dropForeign('comments_user_id_foreign');
//            $this->dropForeign('comments_parent_id_foreign');
        });
    }
}
