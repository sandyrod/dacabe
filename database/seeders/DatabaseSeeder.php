<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CompanyTableSeeder::class);        
        $this->call(UserTableSeeder::class);        
        $this->call(RoleTableSeeder::class);        
        $this->call(RoleUserTableSeeder::class);        
        $this->call(PermissionTableSeeder::class);            
    }
}
