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
        Schema::create('configs', function (Blueprint $table) {
            $table->comment('全局配置');

            $table->id();
            $table->string('item_tag')->index();
            $table->string('item_key')->index();
            $table->text('item_value')->nullable();
            $table->string('item_type');
            $table->unsignedTinyInteger('is_multilingual')->default(0)->comment('是否多语言');
            $table->unsignedTinyInteger('is_api')->default(0)->comment('是否接口输出');
            $table->unsignedTinyInteger('is_custom')->default(1)->comment('是否为自定义');
            $table->unsignedTinyInteger('is_enable')->default(1)->comment('是否有效');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configs');
    }
};
