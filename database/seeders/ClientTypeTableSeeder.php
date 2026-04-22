<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClientType;

class ClientTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        if (! ClientType::where('name', 'Cliente')->exists()){
            $admin = ClientType::create([
                'name' => 'Cliente'
            ]);
        }

        if (! ClientType::where('name', 'Taller')->exists()){
            $admin = ClientType::create([
                'name' => 'Taller'
            ]);

            $this->command->info('Default clients table seeded!');
        }
    }
}
