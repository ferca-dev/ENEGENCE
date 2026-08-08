<?php

namespace Tests\Feature;

use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\BuildsInegiPayloads;
use Tests\TestCase;

class StatePagesTest extends TestCase
{
    use BuildsInegiPayloads;
    use RefreshDatabase;

    public function test_the_states_page_renders_the_datatable_ordered_and_formatted(): void
    {
        $this->createState('02', 'Baja California', 3769020);
        $this->createState('01', 'Aguascalientes', 1425607);

        $response = $this->get(route('states.index'));

        $response
            ->assertOk()
            ->assertSee('id="states-table"', false)
            ->assertSeeTextInOrder(['Aguascalientes', 'Baja California'])
            ->assertSeeText('1,425,607')
            ->assertSeeText('712,803')
            ->assertSeeText('356,401')
            ->assertSee(route('states.municipalities', '01'));
    }

    public function test_the_municipalities_page_shows_the_complete_provider_response(): void
    {
        $state = $this->createState('01', 'Aguascalientes', 1425607);
        Http::fake(['*' => Http::response($this->municipalitiesPayload())]);

        $response = $this->get(route('states.municipalities', $state));

        $response
            ->assertOk()
            ->assertSeeText('Volver a estados')
            ->assertSee('bi-arrow-left', false)
            ->assertSeeText('3 municipios')
            ->assertSeeTextInOrder(['001', 'Aguascalientes', '002', 'Asientos', '003', 'Calvillo'])
            ->assertSeeText('948,990');
        $this->assertDatabaseCount('estados', 1);
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/mgem/01'));
    }

    public function test_a_missing_state_returns_404_without_calling_inegi(): void
    {
        Http::fake();

        $this->get('/states/99/municipalities')->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_a_provider_error_returns_a_safe_error_page(): void
    {
        $state = $this->createState('01', 'Aguascalientes', 1425607);
        Http::fake(['*' => Http::response([], 503)]);

        $this->get(route('states.municipalities', $state))
            ->assertStatus(502)
            ->assertSeeText('No fue posible consultar los municipios')
            ->assertDontSee('gaia.inegi.org.mx')
            ->assertDontSee('RequestException');
    }

    public function test_an_invalid_municipality_payload_returns_the_same_safe_error_page(): void
    {
        $state = $this->createState('01', 'Aguascalientes', 1425607);
        Http::fake(['*' => Http::response(['datos' => [], 'numReg' => 0])]);

        $this->get(route('states.municipalities', $state))
            ->assertStatus(502)
            ->assertSeeText('No fue posible consultar los municipios');
    }

    private function createState(string $code, string $name, int $population): State
    {
        return State::query()->create([
            'code' => $code,
            'name' => $name,
            'abbreviation' => mb_substr($name, 0, 4),
            'total_population' => $population,
            'female_population' => intdiv($population, 2),
            'male_population' => $population - intdiv($population, 2),
            'inhabited_dwellings' => intdiv($population, 4),
        ]);
    }
}
