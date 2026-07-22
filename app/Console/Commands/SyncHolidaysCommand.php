<?php

namespace App\Console\Commands;

use App\Services\FeriadosService;
use Illuminate\Console\Command;

class SyncHolidaysCommand extends Command
{
    protected $signature = 'holidays:sync {--year= : Año a sincronizar} {--country= : Código del país (ej. co, ar, mx)}';
    protected $description = 'Sincroniza los días feriados/festivos desde feriados.io';

    public function handle(FeriadosService $feriadosService): int
    {
        $year = $this->option('year') ? (int) $this->option('year') : (int) date('Y');
        $country = $this->option('country') ?: null;

        $this->info("Sincronizando feriados desde feriados.io para el año {$year}...");

        $holidays = $feriadosService->syncHolidays($year, $country);

        $this->info("¡Sincronización completada! Total feriados registrados: " . $holidays->count());

        foreach ($holidays as $h) {
            $this->line("- {$h->date->format('Y-m-d')}: {$h->name}" . ($h->is_movable ? ' (Móvil)' : ''));
        }

        return Command::SUCCESS;
    }
}
