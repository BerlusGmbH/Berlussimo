<?php

namespace App\GraphQL\Mutations;

use App\Models\InvoiceLine;
use App\Models\InvoiceLine as InvoiceLineModel;
use App\Models\InvoiceLineAssignment as InvoiceLineAssignmentModel;
use Arr;
use Carbon\Carbon;
use DB;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class InvoiceLineAssignment
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function create($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $attributes = $this->extractAttributes($args, [
            'MENGE' => 'quantity',
            'KOSTENTRAEGER_TYP' => 'costBearerType',
            'KOSTENTRAEGER_ID' => 'costBearerId',
            'VERWENDUNGS_JAHR' => 'yearOfReassignment',
            'WEITER_VERWENDEN' => 'reassign',
            'KONTENRAHMEN_KONTO' => 'bookingAccountNumber'
        ]);

        $this->substituteReassignValue($attributes);

        $line = InvoiceLine::where('RECHNUNGEN_POS_ID', $args['lineId'])
            ->first();

        Arr::set($attributes, 'BELEG_NR', $line->BELEG_NR);
        Arr::set($attributes, 'POSITION', $line->POSITION);
        Arr::set($attributes, 'EINZEL_PREIS', $line->PREIS);
        Arr::set($attributes, 'MWST_SATZ', $line->MWST_SATZ);
        Arr::set($attributes, 'SKONTO', $line->SKONTO);
        Arr::set($attributes, 'RABATT_SATZ', $line->RABATT_SATZ);
        Arr::set($attributes, 'GESAMT_SUMME', $attributes['MENGE'] * $line->PREIS);
        Arr::set($attributes, 'KONTIERUNGS_DATUM', Carbon::now());
        $assignment = InvoiceLineAssignmentModel::forceCreate($attributes);

        return $assignment;
    }

    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function update($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $attributes = $this->extractAttributes(
            $args,
            [
                'MENGE' => 'quantity',
                'KOSTENTRAEGER_TYP' => 'costBearerType',
                'KOSTENTRAEGER_ID' => 'costBearerId',
                'VERWENDUNGS_JAHR' => 'yearOfReassignment',
                'WEITER_VERWENDEN' => 'reassign',
                'KONTENRAHMEN_KONTO' => 'bookingAccountNumber'
            ]
        );

        $this->substituteReassignValue($attributes);

        $invoiceLineAssignment = InvoiceLineAssignmentModel::where('KONTIERUNG_ID', $args['id'])->firstOrFail();

        InvoiceLineAssignmentModel::unguarded(function () use ($attributes, $invoiceLineAssignment) {
            Arr::set($attributes, 'GESAMT_SUMME', DB::raw('MENGE * EINZEL_PREIS'));
            $invoiceLineAssignment->update($attributes);
        });

        $invoiceLineAssignment->refresh();

        return $invoiceLineAssignment;
    }

    protected function extractAttributes($args, $translation, $defaults = [])
    {
        $attributes = [];
        foreach ($translation as $db => $api) {
            if (key_exists($api, $args) && !is_null($args[$api])) {
                $attributes[$db] = $args[$api];
            }
        }
        foreach ($defaults as $db => $value) {
            if (!key_exists($db, $attributes)) {
                $attributes[$db] = $value;
            }
        }
        return $attributes;
    }

    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     * @throws \Throwable
     */
    public function updateBatch($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $translation = [
            'KOSTENTRAEGER_TYP' => 'costBearerType',
            'KOSTENTRAEGER_ID' => 'costBearerId',
            'VERWENDUNGS_JAHR' => 'yearOfReassignment',
            'WEITER_VERWENDEN' => 'reassign',
            'KONTENRAHMEN_KONTO' => 'bookingAccountNumber'
        ];
        $attributes = $this->extractAttributes($args, $translation);
        $this->substituteReassignValue($attributes);
        $lineIds = $args['ids'];
        $create = count($attributes) === 5;
        $assignmentIds = collect();
        foreach ($lineIds as $lineId) {
            $line = InvoiceLineModel::where('RECHNUNGEN_POS_ID', $lineId)->first();
            $assignmentIds->add([
                'BELEG_NR' => $line->BELEG_NR,
                'POSITION' => $line->POSITION
            ]);
            if ($line) {
                if ($line->assignments()->exists()) {
                    $line->assignments()->update($attributes);
                } else if ($create) {
                    Arr::set($attributes, 'BELEG_NR', $line->BELEG_NR);
                    Arr::set($attributes, 'POSITION', $line->POSITION);
                    Arr::set($attributes, 'MENGE', $line->MENGE);
                    Arr::set($attributes, 'EINZEL_PREIS', $line->PREIS);
                    Arr::set($attributes, 'MWST_SATZ', $line->MWST_SATZ);
                    Arr::set($attributes, 'RABATT_SATZ', $line->RABATT_SATZ);
                    Arr::set($attributes, 'SKONTO', $line->SKONTO);
                    Arr::set($attributes, 'GESAMT_SUMME', $attributes['MENGE'] * $attributes['EINZEL_PREIS']);
                    Arr::set($attributes, 'KONTIERUNGS_DATUM', Carbon::now());
                    InvoiceLineAssignmentModel::forceCreate($attributes);
                }
            }
        }

        $query = InvoiceLineAssignmentModel::query();
        foreach ($assignmentIds as $assignmentId) {
            $query->orWhere(function ($query) use ($assignmentId) {
                $query->where('BELEG_NR', $assignmentId['BELEG_NR'])
                    ->where('POSITION', $assignmentId['POSITION']);
            });
        }
        return $query->get();
    }

    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function delete($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (!is_array($args['id'])) {
            $idOrIds = [$args['id']];
        } else {
            $idOrIds = $args['id'];
        }

        return InvoiceLineAssignmentModel::whereIn('KONTIERUNG_ID', $idOrIds)->delete();
    }

    protected function substituteReassignValue(&$attributes) {
        if (isset($attributes['WEITER_VERWENDEN'])) {
            $attributes['WEITER_VERWENDEN'] = $attributes['WEITER_VERWENDEN'] ? '1' : '0';
        }
    }
}
