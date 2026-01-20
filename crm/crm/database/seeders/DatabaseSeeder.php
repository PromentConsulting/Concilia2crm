<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Contact;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CrmSeed::class);

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User']
        );

        if (Account::count() === 0) {
            Account::factory()
                ->count(10)
                ->has(Contact::factory()->count(3), 'contacts')
                ->create();
        }
    }
}