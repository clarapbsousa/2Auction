<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $schemaPath = base_path('database/schema.sql');
        $schemaSql = file_get_contents($schemaPath);
        DB::unprepared($schemaSql);
        $this->command->info('Database seeded!');

        $populationPath = base_path('database/population.sql');
        $populationSql = file_get_contents($populationPath);
        DB::unprepared($populationSql);
        $this->command->info('Population seeded!');
    }
}
