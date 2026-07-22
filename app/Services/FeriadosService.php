<?php

namespace App\Services;

use App\Models\Holiday;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeriadosService
{
    protected string $apiKey;
    protected string $country;
    protected string $baseUrl = 'https://api.feriados.io/v1';

    public function __construct()
    {
        $this->apiKey = config('services.feriados_io.key', 'frd_347b2ad529ab4494b4b8781e2');
        $this->country = config('services.feriados_io.country', 'ec');
    }

    /**
     * Sincroniza los feriados para un año específico desde feriados.io y los guarda en la BD.
     */
    public function syncHolidays(?int $year = null, ?string $country = null): Collection
    {
        $year = $year ?? (int) date('Y');
        $country = strtolower($country ?? $this->country);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(5)->get("{$this->baseUrl}/{$country}/holidays/{$year}");

            if ($response->successful()) {
                $data = $response->json();
                $holidaysList = $data['data'] ?? $data ?? [];

                if (is_array($holidaysList) && count($holidaysList) > 0) {
                    foreach ($holidaysList as $item) {
                        $date = $item['date'] ?? $item['fecha'] ?? null;
                        $name = $item['name'] ?? $item['nombre'] ?? $item['title'] ?? 'Feriado';
                        $isMovable = (bool) ($item['is_movable'] ?? $item['movil'] ?? false);

                        if ($date) {
                            Holiday::updateOrCreate(
                                ['date' => $date],
                                [
                                    'name' => $name,
                                    'is_movable' => $isMovable,
                                ]
                            );
                        }
                    }
                } else {
                    $this->seedEcuadorDefaultHolidays($year);
                }
            } else {
                $this->seedEcuadorDefaultHolidays($year);
            }
        } catch (\Throwable $e) {
            Log::error('FeriadosService exception: ' . $e->getMessage());
            $this->seedEcuadorDefaultHolidays($year);
        }

        return Holiday::whereYear('date', $year)->orderBy('date')->get();
    }

    /**
     * Carga los feriados nacionales oficiales de Ecuador si la API externa falla.
     */
    protected function seedEcuadorDefaultHolidays(int $year): void
    {
        $defaults = [
            "{$year}-01-01" => 'Año Nuevo',
            "{$year}-02-16" => 'Carnaval (Día 1)',
            "{$year}-02-17" => 'Carnaval (Día 2)',
            "{$year}-04-03" => 'Viernes Santo',
            "{$year}-05-01" => 'Día del Trabajo',
            "{$year}-05-24" => 'Batalla de Pichincha',
            "{$year}-08-10" => 'Primer Grito de Independencia',
            "{$year}-10-09" => 'Independencia de Guayaquil',
            "{$year}-11-02" => 'Día de los Difuntos',
            "{$year}-11-03" => 'Independencia de Cuenca',
            "{$year}-12-25" => 'Navidad',
        ];

        foreach ($defaults as $date => $name) {
            Holiday::updateOrCreate(
                ['date' => $date],
                ['name' => $name, 'is_movable' => false]
            );
        }
    }
}
