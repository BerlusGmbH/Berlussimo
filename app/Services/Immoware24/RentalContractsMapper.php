<?php


namespace App\Services\Immoware24;


use App\Models\RentDefinition;
use Carbon\Carbon;
use Exception;

class RentalContractsMapper extends Mapper
{
    protected $tenants;
    protected $phones;
    protected $faxs;
    protected $emails;
    protected $SEPAMandate;
    protected $unit;
    protected $start, $end;

    public function __construct($model, & $config)
    {
        parent::__construct($model, $config);
        $this->tenants = $this->model->mieter;
        $this->phones = $this->tenants->pluck('phones')->collapse()->unique();
        $this->faxs = $this->tenants->pluck('faxs')->collapse()->unique();
        $this->emails = $this->tenants->pluck('emails')->collapse()->unique();
        $this->SEPAMandate = $this->currentSEPAMandate();
        $this->unit = $this->model->einheit;
        $this->start = $this->start($this->model->MIETVERTRAG_VON, $this->model->MIETVERTRAG_BIS);
        $this->end = $this->end($this->model->MIETVERTRAG_VON, $this->model->MIETVERTRAG_BIS);
    }

    protected function currentSEPAMandate()
    {
        $until = $this->model->BIS;
        if ($until === '0000-00-00') {
            return $this->model->SEPAMandates()->active('=', Carbon::today())->first();
        } else {
            return $this->model->SEPAMandates()->active('=', $until)->first();
        }
    }

    public function getOBJ_ID()
    {
        return $this->unit->haus->objekt->OBJEKT_ID;
    }

    public function getVE_ID()
    {
        return $this->unit->EINHEIT_ID;
    }

    public function getK_ID()
    {
        $tenantsId = implode(':', $this->tenants->pluck('id')->sort()->values()->all());
        if (array_has($this->config['person-to-contact-ids'], $tenantsId)) {
            return $this->config['person-to-contact-ids'][$tenantsId];
        } else {
            throw new Exception();
        }
    }

    public function getTyp()
    {
        return "M";
    }

    public function getvon()
    {
        return $this->date($this->model->MIETVERTRAG_VON);
    }

    public function getbis()
    {
        if ($this->model->MIETVERTRAG_BIS === '0000-00-00') {
            return "";
        }
        return $this->date($this->model->MIETVERTRAG_BIS);
    }

    public function getGewerbe()
    {
        $type = $this->unit->TYP;
        switch ($type) {
            case "Gewerbe":
                return "G";
            default:
                return "N";
        }
    }

    public function getLastschrift()
    {
        if (!empty($this->SEPAMandate)) {
            return "1";
        }
        return "0";
    }

    public function getMahnsperre()
    {
        return "";
    }

    public function getUmlAusfWagnis()
    {
        return "";
    }

    public function getNotizen()
    {
        return "";
    }

    public function getSV_Brutto()
    {
        return "";
    }

    public function getSV_Fälligkeit()
    {
        return "";
    }

    public function getZ_Miete()
    {
        $basicRentDefinitions = $this->model->basicRentDefinitions($this->start, $this->end)
            ->where('KOSTENKATEGORIE', '!=', 'Untermieter Zuschlag')
            ->where('KOSTENKATEGORIE', '!=', 'Garagenmiete')
            ->where('KOSTENKATEGORIE', '!=', 'Stellplatzmiete');
        $basicRent = RentDefinition::sumDefinitions(
            $basicRentDefinitions,
            $this->start,
            $this->end
        );
        $basicRentDeductionDefinitions = $this->model->basicRentDeductionDefinitions($this->start, $this->end);
        $basicRentDeduction = RentDefinition::sumDefinitions(
            $basicRentDeductionDefinitions,
            $this->start,
            $this->end
        );
        return number_format($basicRent + $basicRentDeduction, 2, ',', '.');
    }

    public function getZ_BKV()
    {
        $operatingCostAdvanceDefinitions = $this->model->operatingCostAdvanceDefinitions($this->start, $this->end)
            ->where('KOSTENKATEGORIE', '!=', 'Kabel TV');
        $operatingCostAdvances = RentDefinition::sumDefinitions(
            $operatingCostAdvanceDefinitions,
            $this->start,
            $this->end
        );
        return number_format($operatingCostAdvances, 2, ',', '.');
    }

    public function getZ_HKV()
    {
        $heatingExpenseAdvanceDefinitions = $this->model->heatingExpenseAdvanceDefinitions($this->start, $this->end);
        $heatingExpenseAdvances = RentDefinition::sumDefinitions(
            $heatingExpenseAdvanceDefinitions,
            $this->start,
            $this->end
        );
        return number_format($heatingExpenseAdvances, 2, ',', '.');
    }

    public function getZ_Garage()
    {
        $garageRentDefinitions = $this->model->rentDefinitions($this->start, $this->end)
            ->where('KOSTENKATEGORIE', '=', 'Garagenmiete');
        $garageRent = RentDefinition::sumDefinitions(
            $garageRentDefinitions,
            $this->start,
            $this->end
        );
        return number_format($garageRent, 2, ',', '.');
    }

    public function getZ_Stellplatz()
    {
        $parkingSpaceRentDefinitions = $this->model->rentDefinitions($this->start, $this->end)
            ->where('KOSTENKATEGORIE', '=', 'Stellplatzmiete');
        $parkingSpaceRent = RentDefinition::sumDefinitions(
            $parkingSpaceRentDefinitions,
            $this->start,
            $this->end
        );
        return number_format($parkingSpaceRent, 2, ',', '.');
    }

    public function getZ_sonstMiete()
    {
        $miscRentDefinitions = $this->model->rentDefinitions($this->start, $this->end)
            ->where(function ($query) {
                $query->where('KOSTENKATEGORIE', '=', 'Untermieter Zuschlag')
                    ->orWhere('KOSTENKATEGORIE', '=', 'Kabel TV');
            });
        $miscRent = RentDefinition::sumDefinitions(
            $miscRentDefinitions,
            $this->start,
            $this->end
        );
        return number_format($miscRent, 2, ',', '.');
    }

    public function getZ_Hausgeld()
    {
        return "";
    }

    public function getZ_Rücklage1()
    {
        return "";
    }

    public function getZ_Rücklage2()
    {
        return "";
    }

    public function getU_Wohnfläche()
    {
        if (!empty($this->unit)) {
            return number_format(trim($this->unit->EINHEIT_QM), 2, ',', '.');
        }
        return "";
    }

    public function getU_Heizfläche()
    {
        if (!empty($this->unit)) {
            return number_format(trim($this->unit->EINHEIT_QM), 2, ',', '.');
        }
        return "";
    }

    public function getU_KabelTV()
    {
        return "";
    }

    public function getU_Personen()
    {
        return $this->tenants->count();
    }

    public function getU_Einheiten()
    {
        return "";
    }

    public function getU_Garagen()
    {
        return "";
    }

    public function getU_Stellplätze()
    {
        return "";
    }

    public function getU_MEA()
    {
        return "";
    }

    public function getBK_ID()
    {
        if (!empty($this->SEPAMandate)) {
            $id = 'sepa:'
                . implode(':', $this->tenants->pluck('id')->sort()->values()->all())
                . '|' . $this->SEPAMandate->M_ID;
            if (array_has($this->config['bank-account-ids'], $id)) {
                return $this->config['bank-account-ids'][$id];
            } else {
                $this->logger->error(
                    'Missing BK_ID for contract (id: '
                    . $this->model->MIETVERTRAG_ID . ' unit: ' . $this->model->einheit->EINHEIT_KURZNAME . ').'
                );
                throw new Exception();
            }
        }
        return "";
    }

    public function getGläubigerID()
    {
        if (!empty($this->SEPAMandate)) {
            return trim($this->SEPAMandate->GLAEUBIGER_ID);
        }
        return "";
    }

    public function getMandatRef()
    {
        if (!empty($this->SEPAMandate)) {
            return trim($this->SEPAMandate->M_REFERENZ);
        }
        return "";
    }

    public function getMandatVon()
    {
        if (!empty($this->SEPAMandate)) {
            return $this->date($this->SEPAMandate->M_ADATUM);
        }
        return "";
    }

    public function getMandatBis()
    {
        if (!empty($this->SEPAMandate)
            && $this->SEPAMandate->M_EDATUM !== '9999-12-31') {
            return $this->date($this->SEPAMandate->M_EDATUM);
        }
        return "";
    }

    public function getMandatUnterschrift()
    {
        if (!empty($this->SEPAMandate)) {
            return $this->date($this->SEPAMandate->M_UDATUM);
        }
        return "";
    }
}