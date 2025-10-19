<?php

namespace Database\Seeders;

use App\Models\Integration;
use Illuminate\Database\Seeder;

class TestIntegrationSeeder extends Seeder
{
    public function run(): void
    {
        $integration = Integration::firstOrCreate(
            ['name' => 'Sistema Legado Deep Freeze - TEST'],
            [
                'status' => 1,
            ]
        );

        $this->command->info('Integration created successfully!');
        $this->command->info('Name: ' . $integration->name);
        $this->command->info('Token: ' . $integration->token);
        $this->command->info('');
        $this->command->warn('IMPORTANT: Copy this token to use in Postman!');
        $this->command->warn('Token: ' . $integration->token);
    }
}
