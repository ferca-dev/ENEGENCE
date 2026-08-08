<?php

namespace App\Console\Commands;

use App\Models\State;
use App\Services\InegiService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('inegi:sync-states')]
#[Description('Sincroniza las entidades federativas desde INEGI')]
class SyncStates extends Command
{
    public function handle(InegiService $inegi): int
    {
        try {
            $states = $inegi->states();
            $timestamp = now();
            $rows = array_map(
                fn (array $state): array => [
                    ...$state,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
                $states,
            );

            DB::transaction(fn () => State::query()->upsert(
                $rows,
                ['code'],
                [
                    'name',
                    'abbreviation',
                    'total_population',
                    'female_population',
                    'male_population',
                    'inhabited_dwellings',
                    'updated_at',
                ],
            ));
        } catch (Throwable $exception) {
            report($exception);
            $this->components->error('No fue posible sincronizar los estados desde INEGI.');

            return self::FAILURE;
        }

        $this->components->info(count($states).' estados sincronizados correctamente.');

        return self::SUCCESS;
    }
}
