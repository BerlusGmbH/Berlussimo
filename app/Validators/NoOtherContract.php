<?php


namespace App\Validators;


use App\Models\Mietvertraege;
use Arr;
use Exception;

class NoOtherContract
{
    public static function message()
    {
        return 'There are other contracts for this unit during this time period.';
    }

    public function validate($attribute, $value, $parameters, $validator)
    {
        try {
            $args = $validator->getData();
            $prefix = $this->prefix($attribute);

            $query = Mietvertraege::whereHas('einheit', function ($query) use ($args, $prefix) {
                $query->where('id', Arr::get($args, $prefix . 'unitId'));
            })->active('>=', Arr::get($args, $prefix . 'start'));

            if (Arr::has($args, $prefix . 'end') && !empty(Arr::get($args, $prefix . 'end'))) {
                $query->active('<=', Arr::get($args, $prefix . 'end'));
            }
            return !$query->exists();
        } catch (Exception $e) {
            return false;
        }
    }

    protected function prefix($attribute)
    {
        $path = explode('.', $attribute);
        if (count($path) > 1) {
            $prefix = array_slice($path, 0, -1);
            $prefix = implode('.', $prefix);
            $prefix .= '.';
            return $prefix;
        }
        return '';
    }
}
