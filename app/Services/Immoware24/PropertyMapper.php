<?php


namespace App\Services\Immoware24;


class PropertyMapper extends Mapper
{
    protected $propertyAccounts;
    protected $ihrAccounts;

    public function __construct($model, & $config)
    {
        parent::__construct($model, $config);

        $this->propertyAccounts = $this->model->bankkonten()
            ->wherePivot('VERWENDUNGSZWECK', 'Hausgeld')
            ->orderBy('VON', 'DESC')
            ->get();

        $this->ihrAccounts = $this->model->bankkonten()
            ->wherePivot('VERWENDUNGSZWECK', 'IHR')
            ->orderBy('VON', 'DESC')
            ->get();
    }

    public function getOBJ_ID()
    {
        return $this->model->OBJEKT_ID;
    }

    public function getStraße()
    {
        $houses = $this->model->haeuser;
        if (!$houses->isEmpty()) {
            return $houses[0]->name;
        }
        return "N/A";
    }

    public function getPLZ()
    {
        $houses = $this->model->haeuser;
        if (!$houses->isEmpty()) {
            return $houses[0]->HAUS_PLZ;
        }
        return "N/A";
    }

    public function getOrt()
    {
        $houses = $this->model->haeuser;
        if (!$houses->isEmpty()) {
            return $houses[0]->HAUS_STADT;
        }
        return "N/A";
    }

    public function getObjNr()
    {
        return $this->model->OBJEKT_ID;
    }

    public function getBK1_ID()
    {

        if ($this->propertyAccounts->count() > 0) {
            $ownerId = $this->model->eigentuemer->PARTNER_ID;
            $bankAccount = 'partner:' . $ownerId . '|' . $this->propertyAccounts[0]->KONTO_ID;
            $bankAccountIds = $this->config['bank-account-ids'];
            if (array_has($bankAccountIds, $bankAccount)) {
                return $bankAccountIds[$bankAccount];
            } else {
                $this->logger->error(
                    'Property (id: '
                    . $this->model->OBJEKT_ID
                    . ' name: '
                    . $this->model->OBJEKT_KURZNAME
                    . ') missing BK1_ID ('
                    . $bankAccount
                    . ')'
                );
            }
        }
        return "";
    }

    public function getBK2_ID()
    {

        if ($this->propertyAccounts->count() > 1) {
            $ownerId = $this->model->eigentuemer->PARTNER_ID;
            $bankAccount = 'partner:' . $ownerId . '|' . $this->propertyAccounts[1]->KONTO_ID;
            $bankAccountIds = $this->config['bank-account-ids'];
            if (array_has($bankAccountIds, $bankAccount)) {
                return $bankAccountIds[$bankAccount];
            } else {
                $this->logger->error(
                    'Property (id: '
                    . $this->model->OBJEKT_ID
                    . ' name: '
                    . $this->model->OBJEKT_KURZNAME
                    . ') missing BK2_ID ('
                    . $bankAccount
                    . ')'
                );
            }
        }
        return "";
    }

    public function getBK3_ID()
    {
        if ($this->propertyAccounts->count() > 2) {
            $ownerId = $this->model->eigentuemer->PARTNER_ID;
            $bankAccount = 'partner:' . $ownerId . '|' . $this->propertyAccounts[2]->KONTO_ID;
            $bankAccountIds = $this->config['bank-account-ids'];
            if (array_has($bankAccountIds, $bankAccount)) {
                return $bankAccountIds[$bankAccount];
            } else {
                $this->logger->error(
                    'Property (id: '
                    . $this->model->OBJEKT_ID
                    . ' name: '
                    . $this->model->OBJEKT_KURZNAME
                    . ') missing BK3_ID ('
                    . $bankAccount
                    . ')'
                );
            }
        }
        return "";
    }

    public function getRL1_BK_ID()
    {
        if (in_array($this->getVerwArt(), ['WEG', 'WEG+SEV'])) {
            if ($this->ihrAccounts->count() > 0) {
                $ownerId = $this->model->eigentuemer->PARTNER_ID;
                $bankAccount = 'partner:' . $ownerId . '|' . $this->ihrAccounts[0]->KONTO_ID;
                $bankAccountIds = $this->config['bank-account-ids'];
                if (array_has($bankAccountIds, $bankAccount)) {
                    return $bankAccountIds[$bankAccount];
                } else {
                    $this->logger->error(
                        'Property (id: '
                        . $this->model->OBJEKT_ID
                        . ' name: '
                        . $this->model->OBJEKT_KURZNAME
                        . ') missing RL1_BK_ID ('
                        . $bankAccount
                        . ')'
                    );
                }
            }
        }
        return "";
    }

    public function getVerwArt()
    {
        $name = $this->getName();
        if (starts_with($name, "WEG")) {
            return "WEG";
        } elseif (str_contains($name, "-SE-") || ends_with($name, "-SE")) {
            return "WEG+SEV";
        }
        return "MV";
    }

    public function getName()
    {
        return trim($this->model->OBJEKT_KURZNAME);
    }

    public function getRL2_BK_ID()
    {
        if (in_array($this->getVerwArt(), ['WEG', 'WEG+SEV'])) {
            if ($this->ihrAccounts->count() > 1) {
                $ownerId = $this->model->eigentuemer->PARTNER_ID;
                $bankAccount = 'partner:' . $ownerId . '|' . $this->ihrAccounts[1]->KONTO_ID;
                $bankAccountIds = $this->config['bank-account-ids'];
                if (array_has($bankAccountIds, $bankAccount)) {
                    return $bankAccountIds[$bankAccount];
                } else {
                    $this->logger->error(
                        'Property (id: '
                        . $this->model->OBJEKT_ID
                        . ' name: '
                        . $this->model->OBJEKT_KURZNAME
                        . ') missing RL2_BK_ID ('
                        . $bankAccount
                        . ')'
                    );
                }
            }
        }
        return "";
    }

    public function getMV_Eig_K_ID()
    {
        if ($this->getVerwArt() === 'MV') {
            $partnerOffset = $this->config['options']['partner-offset'];
            $owner = $this->model->eigentuemer;
            if (isset($owner)) {
                return $owner->PARTNER_ID + $partnerOffset;
            }
        }
        return "";
    }

    public function getMV_EigBeginn()
    {
        $benefitsAndObligations = $this->model->commonDetails()->where('DETAIL_NAME', 'Nutzen-Lastenwechsel')->pluck('DETAIL_INHALT');
        if (!$benefitsAndObligations->isEmpty()) {
            $matches = [];
            preg_match("/[0-3][0-9]\.[0-1][0-9]\.[1-9][0-9]{3}/", $benefitsAndObligations[0], $matches);
            return count($matches) > 0 ? $matches[0] : "";
        }
        return "";
    }
}