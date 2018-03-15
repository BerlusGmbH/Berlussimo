<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvancePaymentInvoiceIdToRECHNUNGENTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function up()
    {
        if (Schema::hasTable('RECHNUNGEN')) {
            Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');

            Schema::table('RECHNUNGEN', function (Blueprint $table) {
                $table->integer('advance_payment_invoice_id')->unsigned()->nullable()->default(null);
            });

            if (Schema::hasTable('RECHNUNGEN_SCHLUSS')) {
                $final_invoices = DB::table('RECHNUNGEN_SCHLUSS')
                    ->where('AKTUELL', '1')
                    ->groupBy('SCHLUSS_R_ID')
                    ->get(['SCHLUSS_R_ID']);

                foreach ($final_invoices as $final_invoice) {
                    $invoices = DB::table('RECHNUNGEN_SCHLUSS')
                        ->where('SCHLUSS_R_ID', $final_invoice['SCHLUSS_R_ID'])
                        ->where('AKTUELL', '1')
                        ->orderBy('TEIL_R_ID')
                        ->get()->pluck('TEIL_R_ID');

                    $invoices->push($final_invoice['SCHLUSS_R_ID']);

                    $advance_payment_invoice_id = \App\Models\Invoice::whereIn('BELEG_NR', $invoices->all())
                        ->where('AKTUELL', '1')
                        ->orderBy('RECHNUNGSDATUM')
                        ->value('BELEG_NR');

                    \App\Models\Invoice::whereIn('BELEG_NR', $invoices->all())
                        ->whereIn('RECHNUNGSTYP', ['Teilrechnung', 'Schlussrechnung'])
                        ->update(['advance_payment_invoice_id' => $advance_payment_invoice_id]);
                }
                \App\Models\Invoice::whereIn('RECHNUNGSTYP', ['Teilrechnung', 'Schlussrechnung'])
                    ->whereNull('advance_payment_invoice_id')
                    ->update(['advance_payment_invoice_id' => DB::raw('BELEG_NR')]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function down()
    {
        if (Schema::hasTable('RECHNUNGEN')) {
            Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');

            Schema::table('RECHNUNGEN', function (Blueprint $table) {
                $table->dropColumn('advance_payment_invoice_id');
            });
        }
    }
}
