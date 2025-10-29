<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertMenuForPondolMeta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('manage_menus')) {
            DB::table('manage_menus')->updateOrInsert(
                ['type' => 'lnb', 'title' => 'pondol-meta'],
                ['component' => 'pondol-meta::partials.admin-lnb', 'order' => '10']
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
