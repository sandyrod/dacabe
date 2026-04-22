<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        if (! Role::where('name', 'admin')->exists()){
            $admin = Role::create([
                'name' => 'admin',
                'display_name' => 'Administrador',
                'description' => 'usuario Administrador de la aplicación',
            ]);

            $this->command->info('Default role table seeded!');
        }
    }
}
