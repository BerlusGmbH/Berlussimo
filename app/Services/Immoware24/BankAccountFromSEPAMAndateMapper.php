<?php


namespace App\Services\Immoware24;

use Exception;

class BankAccountFromSEPAMAndateMapper extends Mapper
{
    protected $rentalContract;
    protected $purchaseContract;
    protected $tenants;
    protected $homeOwners;

    public function __construct($model, & $config)
    {
        parent::__construct($model, $config);
        switch ($this->model->M_KOS_TYP) {
            case "Mietvertrag":
                $this->rentalContract = $this->model->debtorRentalContract;
                $this->tenants = $this->rentalContract->mieter;
                break;
            case "Eigentuemer":
                $this->purchaseContract = $this->model->debtorPurchaseContract;
                $this->homeOwners = $this->purchaseContract->eigentuemer;
                break;
        }
    }

    public function getBK_ID()
    {
        switch ($this->model->M_KOS_TYP) {
            case "Mietvertrag":
                $bankAccountId = 'sepa:' . implode(':', $this->tenants->pluck('id')->sort()->values()->all());
                break;
            case "Eigentuemer":
                $bankAccountId = 'sepa:' . implode(':', $this->homeOwners->pluck('id')->sort()->values()->all());
                break;
            default:
                throw new Exception();
        }
        $bankAccountId .= '|' . $this->model->M_ID;
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
        switch ($this->model->M_KOS_TYP) {
            case "Mietvertrag":
                $ids = implode(':', $this->tenants->pluck('id')->sort()->values()->all());
                break;
            case "Eigentuemer":
                $ids = implode(':', $this->homeOwners->pluck('id')->sort()->values()->all());
                break;
        }
        if (isset($ids) && array_has($this->config['person-to-contact-ids'], $ids)) {
            return $this->config['person-to-contact-ids'][$ids];
        }
        throw new Exception();
    }

    public function getBank()
    {
        return $this->oneLine($this->crop($this->model->BANKNAME, 128));
    }

    public function getBLZ()
    {
        return $this->oneLine($this->crop($this->model->BLZ, 64));
    }

    public function getKontoNr()
    {
        return $this->oneLine($this->crop($this->model->KONTONR, 64));
    }

    public function getBIC()
    {
        return $this->oneLine($this->crop($this->model->BIC, 64));
    }

    public function getIBAN()
    {
        return $this->oneLine($this->crop($this->model->IBAN, 34));
    }

    public function getInhaber()
    {
        return $this->oneLine($this->crop($this->model->NAME, 64));
    }
}