<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Base content: categories, models, pages, settings and the main dealer.
     * Images and texts of the old site are imported separately: php artisan jac:import-legacy
     * Administrators are created with: php artisan make:filament-user
     */
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            SiteSeeder::class,
        ]);
    }
}
