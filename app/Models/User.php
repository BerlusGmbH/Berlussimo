<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\Models\User
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'password', 'remember_token', 'api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function menus()
    {
        return $this->belongsTo('App\Models\MenuNodes', 'menu_nodes_id');
    }

    public function arbeitgeber() {
        return $this->belongsToMany(Partner::class, 'BENUTZER_PARTNER', 'BP_BENUTZER_ID', 'BP_PARTNER_ID');
    }

    /**
     * @param $label
     * @param $icon
     * @return mixed
     */
    public function addMenu($label, $icon) {
        $item = MenuItems::createForMenuRoot($label, $icon);
        return $this->menus->addChild($item);
    }


    /**
     * @return Collection
     */
    public function getMenus() {
        return $this->menus->getChildren(1)->with('item')->get();
    }

    /**
     * @return \App\Models\MenuNodes
     */
    private static function addUserToTree(MenuNodes $node) {
        return MenuNodes::getRoot()->addChild($node);
    }

    /**
     * Save the model to the database.
     *
     * @param  array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if(!$this->exists) {
            $item = MenuItems::newForUser();
            $item->save();
            $node = new MenuNodes();
            $node->item()->associate($item);
            self::addUserToTree($node);
            $this->menus()->associate($node);
        }
        return parent::save($options);
    }


}
