<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use App\Models\Company;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        if (! User::where('name', 'Administrador')->exists()){
            $company = Company::where('name', 'SOFTDESIGN')->first();
            $admin = User::create([
                'name' => 'Administrador',
                'last_name' => 'Sistema',
                'document' => 'admin',
                'email' => 'admin@admin.com',
                'password' => '123456',
                'company_id' => $company->id
            ]);

            $this->command->info('Default user table seeded!');
        }
    }
}
