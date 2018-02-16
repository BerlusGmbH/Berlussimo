<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\MenuItems;

class CreateMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('type');
            $table->string('label');
            $table->string('target');
            $table->string('icon');
            $table->timestamps();
        });

        $item = new MenuItems();
        $item->label = 'root';
        $item->type = MenuItems::ROOT;
        $item->target = '';
        $item->icon = '';
        $item->save();

        $item = new MenuItems();
        $item->label = 'common_menus';
        $item->type = MenuItems::COMMON_MENUS;
        $item->target = '';
        $item->icon = '';
        $item->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('menu_items');
    }
}
