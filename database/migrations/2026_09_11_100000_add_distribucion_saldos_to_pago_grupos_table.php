<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDistribucionSaldosToPagoGruposTable extends Migration
{
    protected $connection = 'company';

    public function up()
    {
        $databases = $this->getCompanyDatabases();

        foreach ($databases as $db) {
            try {
                config(['database.connections.company.database' => $db]);
                DB::purge('company');
                DB::reconnect('company');

                if (Schema::connection('company')->hasTable('pago_grupos')) {
                    if (!Schema::connection('company')->hasColumn('pago_grupos', 'distribucion_saldos')) {
                        Schema::connection('company')->table('pago_grupos', function (Blueprint $table) {
                            $table->longText('distribucion_saldos')->nullable()->after('seller_id');
                        });
                    }
                }
            } catch (\Throwable $e) {
                // Omitir bases de datos no existentes o inactivas
                continue;
            }
        }
    }

    public function down()
    {
        $databases = $this->getCompanyDatabases();

        foreach ($databases as $db) {
            try {
                config(['database.connections.company.database' => $db]);
                DB::purge('company');
                DB::reconnect('company');

                if (Schema::connection('company')->hasTable('pago_grupos')) {
                    if (Schema::connection('company')->hasColumn('pago_grupos', 'distribucion_saldos')) {
                        Schema::connection('company')->table('pago_grupos', function (Blueprint $table) {
                            $table->dropColumn('distribucion_saldos');
                        });
                    }
                }
            } catch (\Throwable $e) {
                // Omitir bases de datos no existentes o inactivas
                continue;
            }
        }
    }

    private function getCompanyDatabases(): array
    {
        $current = config('database.connections.company.database');
        if (!empty($current)) {
            return [$current];
        }

        $databases = [];
        try {
            $databases = DB::connection('mysql')
                ->table('companies')
                ->whereNotNull('db_name')
                ->where('db_name', '!=', '')
                ->pluck('db_name')
                ->toArray();
        } catch (\Throwable $e) {
            // Fallback
        }

        if (empty($databases)) {
            $envDb = env('DB_COMPANY_DATABASE') ?: env('DB_DATABASE', 'dacabe');
            if (!empty($envDb)) {
                $databases = [$envDb];
            }
        }

        return array_unique($databases);
    }
}
