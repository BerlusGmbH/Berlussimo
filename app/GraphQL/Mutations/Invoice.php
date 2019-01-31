<?php

namespace App\GraphQL\Mutations;

use App\GraphQL\Traits\InvoiceAttributes;
use App\Models\Invoice as InvoiceModel;
use Arr;
use DB;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Invoice
{

    use InvoiceAttributes;

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
        $invoice = InvoiceModel::where('BELEG_NR', $args['id'])->firstOrFail();
        $attributes = $this->extractAttributes($args, [
            'invoiceNumber',
            'invoiceType',
            'invoiceDate',
            'dateOfReceipt',
            'dueDate',
            'description',
            'issuerInvoiceNumber',
            'recipientInvoiceNumber',
            'firstAdvancePaymentInvoiceId',
            'serviceTimeStart',
            'serviceTimeEnd',
            'costForwarded'
        ], [
            'invoiceNumber' => $invoice->RECHNUNGSNUMMER,
            'invoiceType' => $invoice->RECHNUNGSTYP,
            'invoiceDate' => $invoice->RECHNUNGSDATUM,
            'dateOfReceipt' => $invoice->EINGANGSDATUM,
            'dueDate' => $invoice->FAELLIG_AM,
            'description' => $invoice->KURZBESCHREIBUNG,
            'issuerInvoiceNumber' => $invoice->AUSTELLER_AUSGANGS_RNR,
            'recipientInvoiceNumber' => $invoice->EMPFAENGER_EINGANGS_RNR,
            'firstAdvancePaymentInvoiceId' => $invoice->advance_payment_invoice_id,
            'serviceTimeStart' => $invoice->servicetime_from,
            'serviceTimeEnd' => $invoice->servicetime_to,
            'costForwarded' => $invoice->forwarded
        ]);

        $this->substituteCostForwardedValue($attributes);
        $this->substituteInvoiceTypeValue($attributes);

        $attributes = $this->translateAttributes($attributes, [
            'RECHNUNGSNUMMER' => 'invoiceNumber',
            'RECHNUNGSTYP' => 'invoiceType',
            'RECHNUNGSDATUM' => 'invoiceDate',
            'EINGANGSDATUM' => 'dateOfReceipt',
            'FAELLIG_AM' => 'dueDate',
            'KURZBESCHREIBUNG' => 'description',
            'AUSTELLER_AUSGANGS_RNR' => 'issuerInvoiceNumber',
            'EMPFAENGER_EINGANGS_RNR' => 'recipientInvoiceNumber',
            'advance_payment_invoice_id' => 'firstAdvancePaymentInvoiceId',
            'servicetime_from' => 'serviceTimeStart',
            'servicetime_to' => 'serviceTimeEnd',
            'forwarded' => 'costForwarded'
        ]);

        if ($attributes['RECHNUNGSTYP'] === 'Buchungsbeleg') {
            Arr::set($attributes, 'EMPFAENGER_TYP', DB::raw('AUSSTELLER_TYP'));
            Arr::set($attributes, 'EMPFAENGER_ID', DB::raw('AUSSTELLER_ID'));
        }

        $this->updateAdvancePaymentInvoices($attributes, $args['id']);

        InvoiceModel::unguarded(function () use ($attributes, $invoice) {
            $invoice->update($attributes);
        });
        return $invoice->refresh();
    }

    protected function updateAdvancePaymentInvoices(& $attributes, $id)
    {
        if (!isset($attributes['advance_payment_invoice_id'])) {
            $attributes['advance_payment_invoice_id'] = DB::raw('BELEG_NR');
        }

        if ($this->invoiceIsAssigned($attributes)) {
            $group_id = $this->advancePaymentInvoices($attributes, $id)
                ->orderBy('RECHNUNGSDATUM')
                ->value('BELEG_NR');
            if ($group_id) {
                $this->advancePaymentInvoices($attributes, $id)
                    ->update(['advance_payment_invoice_id' => $group_id]);
            }
        }

    }
}
