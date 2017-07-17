<?php

namespace App\Models\Traits;


use DB;
use Illuminate\Database\Eloquent\Model;

trait Mergeable
{
    public static function bootMergeable()
    {
        app('events')->listen('eloquent.booted: ' . static::class, function ($model) {
            $model->addObservableEvents('merging', 'merged');
        });
    }

    public function merge(Model $model, array $attributes)
    {
        return DB::transaction(function () use ($model, $attributes) {
            if ($this->fireModelEvent('merging') === false) {
                return false;
            }
            $merged = $this->updateModel($attributes);
            $this->updateRelations($model);
            $merged = $merged && $model->delete();
            if ($merged) {
                $this->fireModelEvent('merged');
            }
            return $merged;
        });
    }

    protected function updateModel(array $attributes)
    {
        foreach ($attributes as $key => $attribute) {
            $this->{$key} = $attribute;
        }
        return $this->save();
    }

    protected abstract function updateRelations(Model $model);
}