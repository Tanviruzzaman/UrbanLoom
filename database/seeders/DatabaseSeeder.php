<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // NOTE: the WithoutModelEvents trait is deliberately not used here. Product
    // and Category slugs (and Order numbers) are generated in model events, so
    // suppressing events during seeding would insert rows with empty slugs.

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $this->call(StoreSeeder::class);
    }
}
