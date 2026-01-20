<?php

use App\Models\Account;
use App\Models\Contact;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        if (Account::count() === 0) {
            Account::factory()
                ->count(25)
                ->state([
                    'status' => 'prospect',
                    'group_note' => 'Demo seed data',
                    'system_origin' => 'demo_seed',
                ])
                ->has(Contact::factory()->count(5), 'contacts')
                ->create();
        }
    }

    public function down(): void
    {
        $demoIds = Account::query()
            ->where('system_origin', 'demo_seed')
            ->pluck('id');

        if ($demoIds->isNotEmpty()) {
            Contact::withTrashed()
                ->whereIn('account_id', $demoIds)
                ->forceDelete();

            Account::whereIn('id', $demoIds)->delete();
        }
    }
};