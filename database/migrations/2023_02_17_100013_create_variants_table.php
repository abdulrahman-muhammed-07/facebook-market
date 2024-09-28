<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_store_id', false);
            $table->foreign('user_store_id')->references('store_id')->on('users')->onDelete('cascade');

            $table->char('product_variant_id',);
            $table->foreign('product_variant_id')->references('product_id')->on('products')->onDelete('cascade');

            $table->char('variant_id')->index();
            // $table->primary('variant_id');
            $table->json('variant_image')->nullable();
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
        Schema::dropIfExists('variants');
    }
};
