<?php

namespace App\Services;

use App\Models\BankAccountAssociation;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Kaufvertraege;
use App\Models\Mietvertraege;
use App\Models\Objekte;
use App\Models\Partner;
use App\Models\SEPAMandate;
use App\Services\Immoware24\BankAccountFromSEPAMAndateMapper;
use App\Services\Immoware24\BankAccountMapper;
use App\Services\Immoware24\ContactFromPartnerMapper;
use App\Services\Immoware24\ContactFromPurchaseContractMapper;
use App\Services\Immoware24\ContactFromRentalContractMapper;
use App\Services\Immoware24\HouseMapper;
use App\Services\Immoware24\PropertyMapper;
use App\Services\Immoware24\PurchaseContractsMapper;
use App\Services\Immoware24\RentalContractsMapper;
use App\Services\Immoware24\UnitMapper;
use Exception;


class Immoware24ExportService
{
    public function exportProperties($filename, & $config)
    {
        $fhandle = $this->fileHandle($filename);
        $properties = Objekte::with([
            'haeuser',
            'bankkonten' => function ($query) {
                $query->whereIn('VERWENDUNGSZWECK', ['Hausgeld', 'Kaution', 'IHR']);
            }
        ])->get();
        $parameterNames = [
            'OBJ_ID',
            'VerwArt',
            'Name',
            'Straße',
            'PLZ',
            'Ort',
            'ObjNr',
            'Notizen',
            'BK1_ID',
            'BK2_ID',
            'BK3_ID',
            'RL1_BK_ID',
            'RL2_BK_ID',
            'MV_Eig_K_ID',
            'MV_EigBeginn'
        ];
        $this->export($fhandle, $config, PropertyMapper::class, $parameterNames, $properties);
        fclose($fhandle);
    }

    protected function fileHandle($filename)
    {
        return fopen(storage_path($filename), 'w');
    }

    public function export($fhandle, & $config, $mapperClass, $parameters, $entities, $headers = true)
    {
        if ($headers) {
            fputcsv($fhandle, $parameters, ';');
        }
        foreach ($entities as $entity) {
            $mapper = new $mapperClass($entity, $config);
            $row = [];
            foreach ($parameters as $index => $name) {
                try {
                    $row[$index] = $mapper->get($name);
                } catch (Exception $e) {
                    continue 2;
                }
            }
            fputcsv($fhandle, $row, ';');
        }
    }

    public function exportHouses($filename, & $config)
    {
        $fhandle = $this->fileHandle($filename);
        $houses = Haeuser::all();
        $parameterNames = [
            'OBJ_ID',
            'GEB_ID',
            'Name',
            'Straße',
            'Baujahr',
            'Bauart',
            'Gebäudeart',
            'Denkmalschutz'
        ];
        $this->export($fhandle, $config, HouseMapper::class, $parameterNames, $houses);
        fclose($fhandle);
    }

    public function exportUnits($filename, & $config)
    {
        $fhandle = $this->fileHandle($filename);
        $units = Einheiten::with('haus.objekt')->get();
        $parameterNames = [
            'OBJ_ID',
            'GEB_ID',
            'VE_ID',
            'Bezeichnung',
            'Lage',
            'VENr',
            'Art',
            'Zimmer',
            'Schlafzimmer',
            'Badezimmer',
            'Gesamtfläche',
            'Kellernummer',
            'Kaution',
            'Notizen',
            'U_Wohnfläche',
            'U_Heizfläche',
            'U_MEA',
            'Z_Miete',
            'Z_BKV',
            'Z_HKV',
            'Z_Garage',
            'Z_Stellplatz'
        ];
        $this->export($fhandle, $config, UnitMapper::class, $parameterNames, $units);
        fclose($fhandle);
    }

    public function exportContacts($filename, & $config)
    {
        $parameterNames = [
            'K_ID',
            'GläubigerID',
            'Anrede',
            'Firma',
            'Titel1',
            'Vorname1',
            'Nachname1',
            'Titel2',
            'Vorname2',
            'Nachname2',
            'Titel3',
            'Vorname3',
            'Nachname3',
            'Titel4',
            'Vorname4',
            'Nachname4',
            'Straße',
            'PLZ',
            'Ort',
            'Zustellhinweis',
            'TelPrivat',
            'TelDienst',
            'TelHandy',
            'Fax',
            'Email',
            'Notizen'
        ];

        $fhandle = $this->fileHandle($filename);
        $config['person-to-contact-ids'] = [];
        $personOffset = $config['options']['person-offset'];
        $config['next-person-id'] = $personOffset;

        $rentalContracts = Mietvertraege::with([
            'mieter.phones',
            'mieter.faxs',
            'mieter.emails'
        ])->get();
        $this->export($fhandle, $config, ContactFromRentalContractMapper::class, $parameterNames, $rentalContracts);

        $purchaseContracts = Kaufvertraege::with([
            'eigentuemer.phones',
            'eigentuemer.faxs',
            'eigentuemer.emails'
        ])->get();
        $this->export($fhandle, $config, ContactFromPurchaseContractMapper::class, $parameterNames, $purchaseContracts, false);

        $partners = Partner::with(['emails', 'faxs', 'phones'])->orderBy('PARTNER_ID')->get();
        $this->export($fhandle, $config, ContactFromPartnerMapper::class, $parameterNames, $partners, false);
        fclose($fhandle);
    }

    public function exportContracts($filename, & $config)
    {
        $parameterNames = [
            'OBJ_ID',
            'VE_ID',
            'K_ID',
            'Typ',
            'von',
            'bis',
            'Gewerbe',
            'Lastschrift',
            'Mahnsperre',
            'UmlAusfWagnis',
            'Notizen',
            'SV_Brutto',
            'SV_Fälligkeit',
            'Z_Miete',
            'Z_BKV',
            'Z_HKV',
            'Z_Garage',
            'Z_Stellplatz',
            'Z_sonstMiete',
            'Z_Hausgeld',
            'Z_Rücklage1',
            'Z_Rücklage2',
            'U_Wohnfläche',
            'U_Heizfläche',
            'U_Kabel-TV',
            'U_Personen',
            'U_Einheiten',
            'U_Garagen',
            'U_Stellplätze',
            'U_MEA',
            'BK_ID',
            'GläubigerID',
            'MandatRef',
            'MandatVon',
            'MandatBis',
            'MandatUnterschrift'
        ];

        $fhandle = $this->fileHandle($filename);

        $rentalContracts = Mietvertraege::with([
            'mieter',
            'einheit.haus.objekt'
        ])->get();
        $this->export($fhandle, $config, RentalContractsMapper::class, $parameterNames, $rentalContracts);

        $purchaseContracts = Kaufvertraege::with([
            'eigentuemer',
            'einheit.haus.objekt'
        ])->get();
        $this->export($fhandle, $config, PurchaseContractsMapper::class, $parameterNames, $purchaseContracts, false);

        fclose($fhandle);
    }

    public function exportBankAccounts($filename, & $config)
    {
        $parameterNames = [
            'BK_ID',
            'K_ID',
            'Bank',
            'BLZ',
            'KontoNr',
            'BIC',
            'IBAN',
            'Inhaber'
        ];

        $fhandle = $this->fileHandle($filename);

        $config['bank-account-ids'] = [];
        $config['next-bank-account-id'] = 1;

        $bankAcounts = BankAccountAssociation::with([
            'property.eigentuemer',
            'purchaseContract.eigentuemer',
            'partner'
        ])->get();
        $this->export($fhandle, $config, BankAccountMapper::class, $parameterNames, $bankAcounts);

        $SEPAMandates = SEPAMandate::with([
            'debtorRentalContract.mieter',
            'debtorPurchaseContract.eigentuemer'
        ])->get();
        $this->export($fhandle, $config, BankAccountFromSEPAMAndateMapper::class, $parameterNames, $SEPAMandates, false);

        fclose($fhandle);
    }
}