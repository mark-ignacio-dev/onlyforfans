<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMutedToChatthreadUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatthread_user', function (Blueprint $table) {
            $table->boolean('is_muted')->default(false)->after('chatthread_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatthread_user', function (Blueprint $table) {
            $table->dropColumn('is_muted');
        });
    }
}
