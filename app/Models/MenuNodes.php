<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MenuNodes
 *
 * @package App\Models
 * @property-read \App\Models\MenuItems $item
 * @mixin \Eloquent
 */
class MenuNodes extends Model
{
    /**
     * @return MenuNodes|bool
     * @throws \Exception
     */
    public static function addRoot()
    {
        if ($node = static::getRoot() == null) {
            $node = new static();
            $node->lft = 1;
            $node->rht = 2;
            $node->lvl = 0;
            $item = MenuItems::find(1);
            $node->item()->associate($item);
            $node->save();
        }
        return $node;
    }

    /**
     * @return MenuNodes
     * @throws \Exception
     */
    public static function getRoot()
    {
        if (($root = MenuNodes::find(1)) != null) {
            if ($root->item->label != 'root') {
                throw new \Exception('Malformed Menu Tree.');
            }
        }
        return $root;
    }

    /**
     * @param MenuItems|MenuNodes $itemOrNode
     * @return \App\Models\MenuNodes
     */
    public function addChild($itemOrNode)
    {
        $transaction = \DB::transaction(function () use ($itemOrNode) {

            //make room for new node
            MenuNodes::where('rht', '>=', $this->rht)->where('lft', '>', $this->rht)->increment('lft', 2);
            MenuNodes::where('rht', '>=', $this->rht)->increment('rht', 2);

            //insert new node with old rht
            if ($itemOrNode instanceof MenuItems) {
                $node = new MenuNodes();
                $node->item()->associate($itemOrNode);
            } else {
                $node = $itemOrNode;
            }
            $node->lft = $this->rht;
            $node->rht = $this->rht + 1;
            $node->lvl = $this->lvl + 1;
            $node->save();

            //reload new attributes
            $fresh = $this->fresh();
            $this->attributes = $fresh->attributes;
            $this->syncOriginal();

            return $node;
        });

        return $transaction;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo('App\Models\MenuItems', 'menu_items_id');
    }

    /**
     * @return boolean
     */
    public function hasChildren()
    {
        return !($this->lft + 1 == $this->rht);
    }

    /**
     * @param $depth
     * @return Builder
     */
    public function getChildren($depth = PHP_INT_MAX)
    {
        if ($depth < 1)
            throw new \InvalidArgumentException('$depth smaller than 1');
        return MenuNodes::where('lft', '>', $this->lft)
            ->where('lft', '<', $this->rht)
            ->where('lvl', '<=', $this->lvl + $depth);
    }

    /**
     * @return Builder
     */
    public function getParent()
    {
        if ($this->lvl == 0) {
            return MenuNodes::find($this->id);
        }
        return MenuNodes::where('lft', '<', $this->lft)
            ->where('rht', '>', $this->rht)
            ->where('lvl', '=', $this->lvl - 1);
    }
}
