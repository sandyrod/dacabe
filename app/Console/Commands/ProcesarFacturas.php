<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Ftp;

use Illuminate\Support\Facades\DB;

class ProcesarFacturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'procesar:facturas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesar las facturas de compra descargadas de las droguerias';

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
        $this->info('procesando facturas.');
        
        $ftp = new Ftp();
        $process = $ftp->processFtpInvoices();
        
        $this->info('Proceso Finalizado.');        
    }
}
