<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MenuItems
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MenuNodes[] $nodes
 * @mixin \Eloquent
 */
class MenuItems extends Model
{
    const ROOT = 0;
    const COMMON_MENUS = 1;
    const USER_MENUS = 2;
    const MENU_ROOT = 3;
    const MENU_ENTRY = 4;
    const MENU_TOGGLE = 5;

    public static function newForUser() {
        $instance = new self();
        $instance->label = '';
        $instance->type = MenuItems::USER_MENUS;
        $instance->target = '';
        $instance->icon = '';

        return $instance;
    }

    public static function createForMenuRoot($label, $icon) {
        $instance = new self();
        $instance->label = $label;
        $instance->type = MenuItems::MENU_ROOT;
        $instance->target = '';
        $instance->icon = $icon;
        $instance->save();

        return $instance;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function menuNodes()
    {
        return $this->hasMany('App\Models\MenuNodes');
    }
}
