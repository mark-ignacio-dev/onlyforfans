<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vault_folders', function (Blueprint $table) {
            $table->uuid('id')->primary;
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // $table->string('guid')->unique();
            $table->string('slug')->unique();

            $table->uuid('parent_id')->nullable()->comment('Parent folder, NULL for root');
            $table->foreign('parent_id')->references('id')->on('vault_folders');

            $table->uuid('vault_id');
            $table->foreign('vault_id')->references('id')->on('vaults');

            $table->string('name')->comment('Vault folder name');

            $table->json('custom_attributes')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('metadata')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vault_folders');
    }
}