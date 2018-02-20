<?php
/**
 * Created by PhpStorm.
 * User: mueller
 * Date: 17.10.17
 * Time: 14:08
 */

namespace App\Jobs;


use App\Models\Objekte;
use App\Notifications\ObjectCopied;

class CopyObject extends UserJob
{
    /**
     * @var array
     */
    protected $parameters;
    /**
     * @var Objekte
     */
    protected $object;

    /**
     * Create a new job instance.
     *
     * @param array $parameters
     * @param Objekte $object
     */
    public function __construct(array $parameters, Objekte $object)
    {
        parent::__construct();
        $this->parameters = $parameters;
        $this->object = $object;
    }

    public function handleJob()
    {
        $object_id = $this->object->copy(
            $this->parameters['owner'],
            $this->parameters['name'],
            $this->parameters['prefix'],
            $this->parameters['opening_balance_date'],
            $this->parameters['opening_balance']
        );
        $this->user->notify(new ObjectCopied($this->object, Objekte::find($object_id)));
    }
}