<?php


namespace App\Services\Immoware24;

use Exception;

class BankAccountMapper extends Mapper
{
    protected $owner;
    protected $homeOwners;
    protected $partner;

    public function __construct($model, & $config)
    {
        parent::__construct($model, $config);
        $property = $this->model->property;
        if (!is_null($property)) {
            $this->owner = $property->eigentuemer;
        }
        $purchaseContract = $this->model->purchaseContract;
        if (!is_null($purchaseContract)) {
            $this->homeOwners = $purchaseContract->eigentuemer;
        }
        $this->partner = $this->model->partner;
    }

    public function getBK_ID()
    {
        $bankAccountId = null;
        switch ($this->model->KOSTENTRAEGER_TYP) {
            case "Objekt":
                $bankAccountId = 'partner:' . $this->owner->PARTNER_ID;
                break;
            case "Eigentuemer":
                $bankAccountId = 'person:' . implode(':', $this->homeOwners->pluck('id')->sort()->values()->all());
                break;
            case "Partner":
                $bankAccountId = 'partner:' . $this->partner->PARTNER_ID;
                break;
            default:
                throw new Exception();
        }
        $bankAccountId .= '|' . $this->model->bankAccount->KONTO_ID;
        $nextBankAccountId = $this->config['next-bank-account-id'];
        if (array_has($this->config['bank-account-ids'], $bankAccountId)) {
            throw new Exception();
        }
        $this->config['bank-account-ids'][$bankAccountId] = $nextBankAccountId;
        $this->config['next-bank-account-id'] = $nextBankAccountId + 1;
        return $nextBankAccountId;
    }

    public function getK_ID()
    {
        $partnerOffset = $this->config['options']['partner-offset'];
        switch ($this->model->KOSTENTRAEGER_TYP) {
            case "Objekt":
                return $this->owner->PARTNER_ID + $partnerOffset;
            case "Eigentuemer":
                $ownersId = implode(':', $this->homeOwners->pluck('id')->sort()->values()->all());
                if (array_has($this->config['person-to-contact-ids'], $ownersId)) {
                    return $this->config['person-to-contact-ids'][$ownersId];
                } else {
                    throw new Exception();
                }
            case "Partner":
                return $this->partner->PARTNER_ID + $partnerOffset;
        }
        throw new Exception();
    }

    public function getBank()
    {
        return $this->oneLine($this->crop($this->model->bankAccount->INSTITUT, 128));
    }

    public function getBLZ()
    {
        return $this->oneLine($this->crop($this->model->bankAccount->BLZ, 64));
    }

    public function getKontoNr()
    {
        return $this->oneLine($this->crop($this->model->bankAccount->KONTONUMMER, 64));
    }

    public function getBIC()
    {
        return $this->oneLine($this->crop($this->model->bankAccount->BIC, 64));
    }

    public function getIBAN()
    {
        return $this->oneLine($this->crop($this->model->bankAccount->IBAN, 34));
    }

    public function getInhaber()
    {
        return $this->oneLine($this->crop($this->model->bankAccount->BEGUENSTIGTER, 64));
    }
}