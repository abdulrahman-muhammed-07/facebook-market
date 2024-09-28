<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_settings', function (Blueprint $table) {

            $table->unsignedBigInteger('user_store_id', false);
            $table->foreign('user_store_id')->references('store_id')->on('users')->onDelete('cascade');
            $table->json('settings')->nullable();
            $table->json('last_updates')->nullable();
            $table->json('issues')->nullable();
            $table->json('smtp')->nullable();
            $table->json('mapping_settings')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->text('expiry_date')->nullable();
            $table->primary('user_store_id');
            $table->text('download_token')->nullable();
            $table->text('facebook_catalog_id')->nullable();
            $table->text('facebook_feed_id')->nullable();
            $table->text('facebook_feed_fetch_url')->nullable();

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
        Schema::dropIfExists('user_settings');
    }
}
