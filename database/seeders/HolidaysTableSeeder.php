<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaysTableSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) date('Y');

        $holidays = [
            ['date' => "{$year}-01-01", 'name' => 'Año Nuevo', 'is_recurring' => true, 'is_movable' => false],
            ['date' => "{$year}-02-16", 'name' => 'Carnaval (Día 1)', 'is_recurring' => true, 'is_movable' => true],
            ['date' => "{$year}-02-17", 'name' => 'Carnaval (Día 2)', 'is_recurring' => true, 'is_movable' => true],
            ['date' => "{$year}-04-03", 'name' => 'Viernes Santo', 'is_recurring' => true, 'is_movable' => true],
            ['date' => "{$year}-05-01", 'name' => 'Día del Trabajo', 'is_recurring' => true, 'is_movable' => false],
            ['date' => "{$year}-05-24", 'name' => 'Batalla de Pichincha', 'is_recurring' => true, 'is_movable' => true],
            ['date' => "{$year}-08-10", 'name' => 'Primer Grito de Independencia', 'is_recurring' => true, 'is_movable' => true],
            ['date' => "{$year}-10-09", 'name' => 'Independencia de Guayaquil', 'is_recurring' => true, 'is_movable' => true],
            ['date' => "{$year}-11-02", 'name' => 'Día de los Difuntos', 'is_recurring' => true, 'is_movable' => false],
            ['date' => "{$year}-11-03", 'name' => 'Independencia de Cuenca', 'is_recurring' => true, 'is_movable' => false],
            ['date' => "{$year}-12-25", 'name' => 'Navidad', 'is_recurring' => true, 'is_movable' => false],
        ];

        foreach ($holidays as $item) {
            Holiday::updateOrCreate(
                ['date' => $item['date']],
                [
                    'name' => $item['name'],
                    'is_recurring' => $item['is_recurring'],
                    'is_movable' => $item['is_movable'],
                ]
            );
        }
    }
}
