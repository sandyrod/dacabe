<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Company;
use App\Models\CompanyStatus;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        if (! Company::where('name', 'SOFTDESIGN')->exists()){
            $admin = Company::create([
                'name' => 'SOFTDESIGN',
                'theme' => 'default',
                'code' => 'V-123456789-0',
                'email' => 'admin@admin.com',
                'phone' => '',
                'logo' => 'logo.png',
                'id' => 1,
                'main' => 0
            ]);

            $this->command->info('Default Company table seeded!');
        }

        if (! CompanyStatus::where('name', 'Activo')->exists())
            $admin = CompanyStatus::create([
                'name' => 'Activo'
            ]);

        if (! CompanyStatus::where('name', 'Inactivo')->exists())
            $admin = CompanyStatus::create([
                'name' => 'Inactivo'
            ]);
        
        if (! CompanyStatus::where('name', 'Suspendido')->exists())
            $admin = CompanyStatus::create([
                'name' => 'Suspendido'
            ]);
        
    }
}
