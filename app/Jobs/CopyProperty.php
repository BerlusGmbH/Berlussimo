<?php

namespace App\Jobs;


use App\Models\Objekte;
use App\Notifications\PropertyCopied;
use InvalidArgumentException;

class CopyProperty extends UserJob
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * Create a new job instance.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        parent::__construct();
        $this->parameters = $parameters;
    }

    public function handleJob()
    {
        $property = Objekte::where('id', $this->parameters['id'])->first();
        if (!empty($property)) {
            $propertyId = $property->copy(
                $this->parameters['ownerId'],
                $this->parameters['name'],
                $this->parameters['prefix'],
                $this->parameters['openingBalanceDate'],
                $this->parameters['openingBalance']
            );
            $this->user->notify(new PropertyCopied($property, Objekte::where('id', $propertyId)->first()));
        } else {
            throw new InvalidArgumentException('Property with ID[' . $this->parameters['id'] . '] not found.');
        }
    }
}
