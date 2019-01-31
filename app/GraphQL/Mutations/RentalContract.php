<?php

namespace App\GraphQL\Mutations;

use App\Models\Mietvertraege;
use DB;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class RentalContract
{
    /* Limit result to persons matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function create($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $rentalContractValues = [
            'MIETVERTRAG_VON' => $args['start'],
            'EINHEIT_ID' => $args['unitId'],
            'MIETVERTRAG_AKTUELL' => '1'
        ];
        if (key_exists('end', $args)) {
            $rentalContractValues['MIETVERTRAG_BIS'] = $args['end'];
        }
        $rentalContract = Mietvertraege::create($rentalContractValues);

        $rentalContract->save();
        $rentalContract->refresh();

        if (key_exists('tenants', $args)) {
            $rentalContract->mieter()->attach($args['tenants']);
        }

        if (key_exists('baseRent', $args) && !empty($args['baseRent'])) {
            $rentalContract->rentDefinitions()->create([
                'KOSTENTRAEGER_TYP' => 'Mietvertag',
                'KOSTENTRAEGER_ID' => $rentalContract->id,
                'KOSTENKATEGORIE' => 'Miete kalt',
                'ANFANG' => $rentalContract->MIETVERTRAG_VON,
                'ENDE' => $rentalContract->MIETVERTRAG_BIS,
                'MWST_ANTEIL' => 0,
                'BETRAG' => $args['baseRent'],
                'MIETENTWICKLUNG_AKTUELL' => '1'
            ]);
        }

        if (key_exists('heatingCostAdvance', $args) && !empty($args['heatingCostAdvance'])) {
            $rentalContract->rentDefinitions()->create([
                'KOSTENTRAEGER_TYP' => 'Mietvertag',
                'KOSTENTRAEGER_ID' => $rentalContract->id,
                'KOSTENKATEGORIE' => 'Heizkosten Vorauszahlung',
                'ANFANG' => $rentalContract->MIETVERTRAG_VON,
                'ENDE' => $rentalContract->MIETVERTRAG_BIS,
                'MWST_ANTEIL' => 0,
                'BETRAG' => $args['heatingCostAdvance'],
                'MIETENTWICKLUNG_AKTUELL' => '1'
            ]);
        }

        if (key_exists('operatingCostAdvance', $args) && !empty($args['operatingCostAdvance'])) {
            $rentalContract->rentDefinitions()->create([
                'KOSTENTRAEGER_TYP' => 'Mietvertag',
                'KOSTENTRAEGER_ID' => $rentalContract->id,
                'KOSTENKATEGORIE' => 'Nebenkosten Vorauszahlung',
                'ANFANG' => $rentalContract->MIETVERTRAG_VON,
                'ENDE' => $rentalContract->MIETVERTRAG_BIS,
                'MWST_ANTEIL' => 0,
                'BETRAG' => $args['operatingCostAdvance'],
                'MIETENTWICKLUNG_AKTUELL' => '1'
            ]);
        }

        if (key_exists('deposit', $args) && !empty($args['deposit'])) {
            $db_abfrage = "INSERT INTO KAUTION_DATEN VALUES (NULL, '$rentalContract->id', 'SOLL', '$args[deposit]', '1')";
            DB::insert($db_abfrage);

            // protokollieren
            $last_dat = DB::getPdo()->lastInsertId();
            protokollieren('KAUTION_DATEN', $last_dat, $last_dat);
        }

        return $rentalContract;
    }
}
