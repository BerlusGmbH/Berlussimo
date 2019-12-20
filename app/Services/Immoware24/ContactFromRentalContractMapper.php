<?php


namespace App\Services\Immoware24;


use Exception;

class ContactFromRentalContractMapper extends Mapper
{
    protected $tenants;
    protected $phones;
    protected $faxs;
    protected $emails;

    public function __construct($model, & $config)
    {
        parent::__construct($model, $config);
        $this->tenants = $this->model->mieter;
        $this->phones = $this->tenants->pluck('phones')->collapse()->unique();
        $this->faxs = $this->tenants->pluck('faxs')->collapse()->unique();
        $this->emails = $this->tenants->pluck('emails')->collapse()->unique();
    }

    public function getK_ID()
    {
        $tenantsId = implode(':', $this->tenants->pluck('id')->sort()->values()->all());
        if (array_has($this->config['person-to-contact-ids'], $tenantsId)) {
            throw new Exception();
        }
        if ($this->tenants->count() > 1) {
            $nextPersonId = $this->config['next-person-id'];
            $this->config['person-to-contact-ids'][$tenantsId] = $nextPersonId;
            $this->config['next-person-id'] = $nextPersonId + 1;
            return $nextPersonId;
        } else {
            $this->config['person-to-contact-ids'][$tenantsId] = $this->tenants[0]->id;
            return $this->tenants[0]->id;
        }
    }

    public function getGläubigerID()
    {
        return "";
    }

    public function getAnrede()
    {
        return "";
    }

    public function getFirma()
    {
        return "";
    }

    public function getTitel1()
    {
        return "";
    }

    public function getVorname1()
    {
        return $this->field($this->tenants, 0, 'first_name', 128);
    }

    public function getNachname1()
    {
        return $this->field($this->tenants, 0, 'name', 128);
    }

    public function getTitel2()
    {
        return "";
    }

    public function getVorname2()
    {
        return $this->field($this->tenants, 1, 'first_name', 128);
    }

    public function getNachname2()
    {
        return $this->field($this->tenants, 1, 'name', 128);
    }

    public function getTitel3()
    {
        return "";
    }

    public function getVorname3()
    {
        return $this->field($this->tenants, 2, 'first_name', 128);
    }

    public function getNachname3()
    {
        return $this->field($this->tenants, 2, 'name', 128);
    }

    public function getTitel4()
    {
        return "";
    }

    public function getVorname4()
    {
        return $this->field($this->tenants, 3, 'first_name', 128);
    }

    public function getNachname4()
    {
        return $this->field($this->tenants, 3, 'name', 128);
    }

    public function getStraße()
    {
        return "";
    }

    public function getPLZ()
    {
        return "";
    }

    public function getOrt()
    {
        return "";
    }

    public function getZustellhinweis()
    {
        return "";
    }

    public function getTelPrivat()
    {
        return $this->field($this->phones, 2, 'detail_inhalt_one_line', 48);
    }

    public function getTelDienst()
    {
        return $this->field($this->phones, 0, 'detail_inhalt_one_line', 48);
    }

    public function getTelHandy()
    {
        return $this->field($this->phones, 1, 'detail_inhalt_one_line', 48);
    }

    public function getFax()
    {
        return $this->field($this->faxs, 0, 'detail_inhalt_one_line', 48);
    }

    public function getEmail()
    {
        return $this->field($this->emails, 0, 'detail_inhalt_one_line', 64);
    }

    public function getNotizen()
    {
        return "";
    }
}