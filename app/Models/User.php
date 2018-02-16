<?php

namespace App\Models;

use App\Models\Contracts\Active as ActiveContract;
use App\Models\Traits\Active;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\Models\User
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements ActiveContract
{
    use Searchable;
    use DefaultOrder;
    use Active;

    protected $searchableFields = ['name', 'email'];
    protected $defaultOrder = ['name' => 'asc'];

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

    public function arbeitgeber()
    {
        return $this->belongsToMany(Partner::class, 'BENUTZER_PARTNER', 'BP_BENUTZER_ID', 'BP_PARTNER_ID')->wherePivot('AKTUELL', '1');
    }

    public function gewerk()
    {
        return $this->hasOne(Gewerke::class, 'G_ID', 'trade_id');
    }

    /**
     * @param $label
     * @param $icon
     * @return mixed
     */
    public function addMenu($label, $icon)
    {
        $item = MenuItems::createForMenuRoot($label, $icon);
        return $this->menus->addChild($item);
    }

    /**
     * @return Collection
     */
    public function getMenus()
    {
        return $this->menus->getChildren(1)->with('item')->get();
    }

    /**
     * Save the model to the database.
     *
     * @param  array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if (!$this->exists) {
            $item = MenuItems::newForUser();
            $item->save();
            $node = new MenuNodes();
            $node->item()->associate($item);
            self::addUserToTree($node);
            $this->menus()->associate($node);
        }
        return parent::save($options);
    }

    /**
     * @return \App\Models\MenuNodes
     */
    private static function addUserToTree(MenuNodes $node)
    {
        return MenuNodes::getRoot()->addChild($node);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function menus()
    {
        return $this->belongsTo('App\Models\MenuNodes', 'menu_nodes_id');
    }


    public function getStartDateFieldName()
    {
        return 'join_date';
    }

    public function getEndDateFieldName()
    {
        return 'leave_date';
    }
}
