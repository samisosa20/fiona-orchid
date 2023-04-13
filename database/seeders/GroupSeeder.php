<?php

namespace Database\Seeders;

use App\Models\Group;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Group::factory()->create(['name' => 'Transferencia']);
        Group::factory()->create(['name' => 'Ingresos']);
        Group::factory()->create(['name' => 'Gastos Fijos']);
        Group::factory()->create(['name' => 'Gastos Personales']);
    }
}
