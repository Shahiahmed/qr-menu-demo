<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Creates the Filament admin account and the sample "Дастархан" menu, so a
     * fresh clone on the client's server is immediately usable after
     * `php artisan migrate --seed`.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@demo.kz'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password'),
            ],
        );

        $this->call(DemoSeeder::class);
    }
}
