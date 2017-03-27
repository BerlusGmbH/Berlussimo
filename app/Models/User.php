<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Traits\Searchable;
use App\Models\Traits\DefaultOrder;

/**
 * App\Models\User
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use Searchable;
    use DefaultOrder;

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function menus()
    {
        return $this->belongsTo('App\Models\MenuNodes', 'menu_nodes_id');
    }

    public function arbeitgeber() {
        return $this->belongsToMany(Partner::class, 'BENUTZER_PARTNER', 'BP_BENUTZER_ID', 'BP_PARTNER_ID')->wherePivot('AKTUELL', '1');
    }

    public function gewerk()
    {
        return $this->hasOne(Gewerke::class, 'G_ID', 'trade_id');
    }

    public function scopeAktiv($query, $comparator = '=', $date = null) {
        if(is_null($date)) {
            $date = Carbon::today();
        }
        if($comparator == '=') {
            $query->where(function($query) use ($date) {
                $query->where(function($query) use ($date) {
                    $query->whereDate('join_date', '<=', $date)->whereDate('leave_date', '>=', $date);
                })->orWhere(function($query) use($date) {
                    $query->where('join_date', '<=', $date)->whereDate('leave_date', '=', '0000-00-00');
                });
            });
        } elseif ($comparator == '>') {
            $query->where(function($query) use ($date) {
                $query->whereDate('leave_date', '>=', $date)->orWhereDate('leave_date', '=', '0000-00-00');
            });
        } elseif ($comparator == '<') {
            $query->whereDate('join_date', '<=', $date);
        }
    }

    public function scopeNotAktiv($query, $comparator = '=', $date = null) {
        if(is_null($date)) {
            $date = Carbon::today();
        }
        if($comparator == '=') {
            $query->where(function ($query) use ($date) {
                $query->whereDate('join_date', '>', $date)->orWhereDate('leave_date', '<', $date)->where('leave_date', '<>', '0000-00-00');
            });
        } elseif ($comparator == '>') {
            $query->whereDate('leave_date', '<', $date)->where('leave_date', '<>', '0000-00-00');
        } elseif ($comparator == '<') {
            $query->whereDate('join_date', '>', $date);
        }
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
