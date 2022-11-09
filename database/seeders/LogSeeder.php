<?php

namespace Database\Seeders;

use App\Models\Log;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         Log::factory(1000)->create();
         Log::factory()->create([
             'bank' => 'AdaBank',
             'request' => [
                 'name' => fake()->name(),
                 'email' => fake()->unique()->safeEmail(),
                 'email_verified_at' => now(),
                 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                 'remember_token' => Str::random(10),
             ],
             'response' => [
                 'status' => true,
                 'message' => 'Success'
             ]
         ]);
    }
}
