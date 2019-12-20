<?php

namespace App\Console\Commands;


use App\Services\Immoware24ExportService;
use Illuminate\Console\Command;

class Immoware24Export extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:immoware24 {--person-offset=10000} {--partner-offset=20000} {--reporting-date= : Final date for rent and HOA fees (Format: YYYY-MM e.g. 2019-12)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CSV files that can be imported by Immoware24.';

    /**
     * Execute the console command.
     *
     * @param Immoware24ExportService $service
     * @return void
     */
    public function handle(Immoware24ExportService $service)
    {
        $config['options'] = $this->options();
        $config['logger'] = $this;
        $this->line('Exporting: Kontakte.csv');
        $service->exportContacts('Kontakte.csv', $config);
        $this->line('Exporting: Bankkonten.csv');
        $service->exportBankAccounts('Bankkonten.csv', $config);
        $this->line('Exporting: Objekte.csv');
        $service->exportProperties('Objekte.csv', $config);
        $this->line('Exporting: Gebäude.csv');
        $service->exportHouses('Gebäude.csv', $config);
        $this->line('Exporting: Einheiten.csv');
        $service->exportUnits('Einheiten.csv', $config);
        $this->line('Exporting: Verträge.csv');
        $service->exportContracts('Verträge.csv', $config);
        $this->line('Done. The files should be in the storage folder (' . storage_path() . ').');
    }
}