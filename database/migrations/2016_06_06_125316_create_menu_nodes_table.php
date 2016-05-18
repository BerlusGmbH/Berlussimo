<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\MenuNodes;
use App\Models\MenuItems;

class CreateMenuNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_nodes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('menu_items_id')->unsigned();
            $table->foreign('menu_items_id')->references('id')->on('menu_items')->onDelete('cascade');
            $table->integer('lft')->unsigned();
            $table->integer('rht')->unsigned();
            $table->mediumInteger('lvl')->unsigned();
            $table->timestamps();
            $table->index(['lft','rht','lvl']);
        });
        
        $node = MenuNodes::addRoot();
        $commonMenus = MenuItems::find(2);
        $node->addChild($commonMenus);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('menu_nodes');
    }
}
