<?php

namespace Tests\Feature;

use App\Models\State;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\BuildsInegiPayloads;
use Tests\TestCase;

class SyncStatesCommandTest extends TestCase
{
    use BuildsInegiPayloads;
    use RefreshDatabase;

    public function test_it_inserts_the_32_states(): void
    {
        Http::fake(['*' => Http::response($this->statesPayload())]);

        $this->assertSame(Command::SUCCESS, $this->artisan('inegi:sync-states'));

        $this->assertDatabaseCount('estados', 32);
        $this->assertDatabaseHas('estados', [
            'code' => '01',
            'name' => 'Aguascalientes',
            'abbreviation' => 'Ags.',
            'total_population' => 1425607,
            'female_population' => 728924,
            'male_population' => 696683,
            'inhabited_dwellings' => 386671,
        ]);
    }

    public function test_two_runs_do_not_duplicate_and_do_update_states(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push($this->statesPayload())
                ->push($this->statesPayload([
                    '01' => [
                        'nomgeo' => 'Aguascalientes actualizado',
                        'nom_abrev' => 'Ags. act.',
                        'pob_total' => '1500000',
                        'pob_femenina' => '760000',
                        'pob_masculina' => '740000',
                        'total_viviendas_habitadas' => '400000',
                    ],
                ])),
        ]);

        $this->assertSame(Command::SUCCESS, $this->artisan('inegi:sync-states'));
        $original = State::query()->where('code', '01')->firstOrFail();

        $this->assertSame(Command::SUCCESS, $this->artisan('inegi:sync-states'));

        $updated = State::query()->where('code', '01')->firstOrFail();
        $this->assertDatabaseCount('estados', 32);
        $this->assertSame($original->id, $updated->id);
        $this->assertSame('Aguascalientes actualizado', $updated->name);
        $this->assertSame('Ags. act.', $updated->abbreviation);
        $this->assertSame(1500000, $updated->total_population);
        $this->assertSame(760000, $updated->female_population);
        $this->assertSame(740000, $updated->male_population);
        $this->assertSame(400000, $updated->inhabited_dwellings);
    }

    public function test_an_invalid_response_preserves_existing_data(): void
    {
        State::query()->create([
            'code' => '01',
            'name' => 'Dato existente',
            'total_population' => 123,
        ]);
        Http::fake(['*' => Http::response(['datos' => [], 'numReg' => 0])]);

        $this->assertSame(Command::FAILURE, $this->artisan('inegi:sync-states'));

        $this->assertDatabaseCount('estados', 1);
        $this->assertDatabaseHas('estados', [
            'code' => '01',
            'name' => 'Dato existente',
            'total_population' => 123,
        ]);
    }
}
