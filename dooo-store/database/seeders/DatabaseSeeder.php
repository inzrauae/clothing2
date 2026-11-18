<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Local development seed: catalogue + a default owner account + sample orders.
     * On cPanel use the web installer (/install) instead, which asks for your own login.
     */
    public function run(): void
    {
        $this->call(CatalogSeeder::class);

        User::query()->firstOrCreate(['email' => 'admin@dooo.lk'], [
            'name' => 'Store Owner',
            'password' => 'password',
            'role' => 'owner',
        ]);

        $this->call(DemoOrdersSeeder::class);
    }
}
