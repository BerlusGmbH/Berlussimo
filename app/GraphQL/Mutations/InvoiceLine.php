<?php

namespace App\GraphQL\Mutations;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceLine as InvoiceLineModel;
use App\Models\InvoiceLineAssignment;
use Arr;
use DB;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class InvoiceLine
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
        $pos = InvoiceLineModel::where('BELEG_NR', $args['invoiceId'])->max('POSITION') + 1;
        $line = InvoiceLineModel::forceCreate([
            'BELEG_NR' => $args['invoiceId'],
            'ART_LIEFERANT' => $args['supplierId'],
            'ARTIKEL_NR' => $args['itemNumber'],
            'MENGE' => $args['quantity'],
            'PREIS' => $args['price'],
            'MWST_SATZ' => $args['VAT'],
            'RABATT_SATZ' => $args['rebate'],
            'SKONTO' => $args['discount'],
            'GESAMT_NETTO' => $args['netAmount'],
            'POSITION' => $pos

        ]);

        $line->invoice->forceUpdate([
            'STATUS_VOLLSTAENDIG' => '1'
        ]);

        $itemExists = InvoiceItem::where('ART_LIEFERANT', $args['supplierId'])
            ->where('ARTIKEL_NR', $args['itemNumber'])
            ->where('LISTENPREIS', $args['price'])
            ->where('MWST_SATZ', $args['VAT'])
            ->where('RABATT_SATZ', $args['rebate'])
            ->where('SKONTO', $args['discount'])
            ->where('EINHEIT', $args['quantityUnit'])
            ->where('BEZEICHNUNG', $args['description'])
            ->exists();

        if (!$itemExists) {
            InvoiceItem::forceCreate([
                'ART_LIEFERANT' => $args['supplierId'],
                'ARTIKEL_NR' => $args['itemNumber'],
                'LISTENPREIS' => $args['price'],
                'MWST_SATZ' => $args['VAT'],
                'RABATT_SATZ' => $args['rebate'],
                'SKONTO' => $args['discount'],
                'EINHEIT' => $args['quantityUnit'],
                'BEZEICHNUNG' => $args['description']
            ]);
        }

        $line->refresh();

        return $line;
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
        $invoiceLine = InvoiceLineModel::where('RECHNUNGEN_POS_ID', $args['id'])->firstOrFail();

        $attributes = $this->extractAttributes(
            $args,
            [
                'ARTIKEL_NR' => 'itemNumber',
                'MENGE' => 'quantity',
                'PREIS' => 'price',
                'MWST_SATZ' => 'VAT',
                'RABATT_SATZ' => 'rebate',
                'SKONTO' => 'discount',
                'GESAMT_NETTO' => 'netAmount'
            ]
        );

        $attributes['ART_LIEFERANT'] = $invoiceLine->ART_LIEFERANT;

        InvoiceLineModel::unguard();
        $invoiceLine->update($attributes);
        $invoiceLine->refresh();

        $item = InvoiceItem::where('ART_LIEFERANT', $invoiceLine->ART_LIEFERANT)
            ->where('ARTIKEL_NR', $invoiceLine->ARTIKEL_NR)
            ->where('AKTUELL', '1')
            ->orderBy('KATALOG_ID', 'DESC')
            ->first();
        if ($item) {
            $attributes = $this->extractAttributes(
                $args,
                [
                    'BEZEICHNUNG' => 'description',
                    'EINHEIT' => 'quantityUnit'
                ]
            );
            if (count($attributes) > 0) {
                InvoiceItem::unguard();
                $item->update($attributes);
            }
        } else {
            $attributes = $this->extractAttributes(
                $args,
                [
                    'ARTIKEL_NR' => 'itemNumber',
                    'LISTENPREIS' => 'price',
                    'MWST_SATZ' => 'VAT',
                    'RABATT_SATZ' => 'rebate',
                    'SKONTO' => 'discount',
                    'BEZEICHNUNG' => 'description',
                    'EINHEIT' => 'quantityUnit'
                ],
                [
                    'ART_LIEFERANT' => $invoiceLine->ART_LIEFERANT,
                    'ARTIKEL_NR' => $invoiceLine->ARTIKEL_NR,
                    'LISTENPREIS' => $invoiceLine->PREIS,
                    'MWST_SATZ' => $invoiceLine->MWST_SATZ,
                    'RABATT_SATZ' => $invoiceLine->RABATT_SATZ,
                    'SKONTO' => $invoiceLine->SKONTO
                ]
            );
            InvoiceItem::forceCreate($attributes);
        }

        $attributes = $this->extractAttributes(
            $args,
            [
                'EINZEL_PREIS' => 'price',
                'MWST_SATZ' => 'VAT',
                'RABATT_SATZ' => 'rebate',
                'SKONTO' => 'discount'
            ]
        );

        Arr::set($attributes, 'GESAMT_SUMME', DB::raw('MENGE * EINZEL_PREIS'));

        InvoiceLineAssignment::unguard();
        $invoiceLine->assignments()->update($attributes);
        $invoiceLine->refresh();

        return $invoiceLine;
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
     */
    public function updateBatch($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $translation = [
            'RABATT_SATZ' => 'rebate',
            'SKONTO' => 'discount'
        ];
        $attributes = $this->extractAttributes($args, $translation);
        $lineIds = $args['ids'];
        $invoiceIds = InvoiceLineModel::whereIn('RECHNUNGEN_POS_ID', $lineIds)->pluck('BELEG_NR');
        foreach ($lineIds as $id) {
            $line = InvoiceLineModel::where('RECHNUNGEN_POS_ID', $id)->first();
            if (isset($line)) {
                InvoiceLineModel::unguarded(function () use ($attributes, $line) {
                    InvoiceLineAssignment::where('BELEG_NR', $line->BELEG_NR)
                        ->where('POSITION', $line->POSITION)
                        ->update($attributes);
                });
            }
        }
        Arr::set($attributes, 'GESAMT_NETTO', DB::raw('PREIS * MENGE * ((100 - RABATT_SATZ)/100)'));
        InvoiceLineModel::unguarded(function () use ($lineIds, $attributes) {
            InvoiceLineModel::whereIn('RECHNUNGEN_POS_ID', $lineIds)->update($attributes);
        });
        Invoice::whereIn('BELEG_NR', $invoiceIds)->each(function ($invoice) {
            $invoice->updateSums();
        });
        return InvoiceLineModel::whereIn('RECHNUNGEN_POS_ID', $lineIds)->get();
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

        $i = 0;

        foreach ($idOrIds as $id) {
            $this->deleteInvoiceLine($id);
            $i++;
        }

        return $i;
    }

    public function deleteInvoiceLine($id)
    {
        $invoiceLine = InvoiceLineModel::where('RECHNUNGEN_POS_ID', $id)->firstOrFail();
        InvoiceLineAssignment::where('BELEG_NR', $invoiceLine->BELEG_NR)
            ->where('POSITION', $invoiceLine->POSITION)
            ->delete();
        InvoiceLineAssignment::where('BELEG_NR', $invoiceLine->BELEG_NR)
            ->where('POSITION', '>', $invoiceLine->POSITION)
            ->update(['POSITION' => DB::raw('POSITION - 1')]);
        InvoiceLineModel::where('BELEG_NR', $invoiceLine->BELEG_NR)
            ->where('POSITION', '>', $invoiceLine->POSITION)
            ->update(['POSITION' => DB::raw('POSITION - 1')]);
        $invoice = $invoiceLine->invoice;
        $invoiceLine->delete();
        if (!$invoice->lines()->exists()) {
            Invoice::unguarded(function () use ($invoiceLine) {
                $invoiceLine->invoice->update([
                    'STATUS_VOLLSTAENDIG' => '0'
                ]);
            });
        }
    }
}
