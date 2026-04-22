<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Ftp;

use Illuminate\Support\Facades\DB;

class ProcesarFtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'procesar:ftp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Descargar las facturas de compra de las droguerias';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('procesando ftp.');
        log::info('procesando ftp.');

        $ftp = new Ftp();
        $process = $ftp->checkDrugstoreFiles();
        
        $this->info('Descarga Finalizada.');
        log::info('Descarga Finalizada.');
    }
}
