<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()
            ->count(1)
            ->create([
                'email' => 'admin@broz.com',
                'password' => Hash::make('broz.1122'),
            ]);

        $this->call(OrdersTableSeeder::class);
    }
}
