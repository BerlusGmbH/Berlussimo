<?php

namespace App\Jobs;

use App\Models\Person;
use App\Notifications\PersonMerged;

class MergePersons extends UserJob
{

    protected $attributes, $left, $right;

    /**
     * Create a new job instance.
     *
     * @param array $attributes
     * @param Person $left
     * @param Person $right
     */
    public function __construct(array $attributes, Person $left, Person $right)
    {
        parent::__construct();
        $this->attributes = $attributes;
        if ($left->id > $right->id) {
            $this->left = $right;
            $this->right = $left;
        } else {
            $this->left = $left;
            $this->right = $right;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleJob()
    {
        $left = $this->left->attributesToArray();
        $right = $this->right->attributesToArray();
        $this->left->merge($this->right, $this->attributes);
        $this->user->notify(new PersonMerged($left, $right, $this->attributes));
    }
}
