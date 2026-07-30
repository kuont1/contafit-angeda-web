<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class PurgeTrashCommand extends Command
{
    protected $signature = 'events:purge-trash';

    protected $description = 'Purga automáticamente de la base de datos los eventos en papelera que superen los 30 días';

    public function handle(): int
    {
        $cutoffDate = now()->subDays(30);

        $count = Event::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate)
            ->forceDelete();

        $this->info("Purga de papelera completada. Eventos eliminados definitivamente: {$count}");

        return Command::SUCCESS;
    }
}
