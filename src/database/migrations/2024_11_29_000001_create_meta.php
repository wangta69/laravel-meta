<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeta extends Migration
{
/**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (!Schema::hasTable('metas')) {
      Schema::create('metas', function (Blueprint $table) {
        $table->id();
        $table->string('name')->comment('route name');
        $table->string('params')->nullable()->comment('route parameters');
        $table->string('title')->nullable();
        $table->string('keywords')->nullable();
        $table->text('description')->nullable();
        $table->string('path')->nullable();
        $table->timestamps();
      });
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('metas');
  }
}
