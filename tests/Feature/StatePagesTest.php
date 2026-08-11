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
            ->assertSee('<title>Estados de México | '.e(config('app.name')).'</title>', false)
            ->assertSee('<meta name="description" content="Listado de entidades federativas de México obtenido desde INEGI">', false)
            ->assertSee('id="states-table"', false)
            ->assertSeeText('Prueba técnica - Fernando Cárdenas')
            ->assertSee('<th scope="col" class="text-center">Clave</th>', false)
            ->assertSee('<th scope="col" class="text-center">Acciones</th>', false)
            ->assertSee('<th scope="col" class="text-center"><input class="form-control form-control-sm column-filter text-center"', false)
            ->assertSee('aria-label="Buscar por viviendas habitadas"', false)
            ->assertSee('data-state-details', false)
            ->assertSee('data-state-name="Aguascalientes"', false)
            ->assertSee('data-details-url="'.route('states.municipalities', '01').'"', false)
            ->assertSee('aria-label="Mostrar municipios de Aguascalientes"', false)
            ->assertSee('class="table table-hover align-middle w-100 states-table"', false)
            ->assertSee('class="state-table__secondary mb-0"', false)
            ->assertSee('class="state-action__label-compact">Municipios</span>', false)
            ->assertSeeText('Mostrar municipios')
            ->assertSee('<span class="fw-semibold">Aguascalientes</span>', false)
            ->assertSee('<span>(Agua)</span>', false)
            ->assertDontSee('aria-label="Buscar por abreviatura"', false)
            ->assertDontSee('href="'.route('states.municipalities', '01').'"', false)
            ->assertSeeTextInOrder(['Aguascalientes', 'Baja California'])
            ->assertSeeText('1,425,607')
            ->assertSeeText('712,803')
            ->assertSeeText('356,401');
    }

    public function test_the_states_page_explains_how_to_load_data_when_it_is_empty(): void
    {
        $this->get(route('states.index'))
            ->assertOk()
            ->assertSeeText('No hay estados disponibles.')
            ->assertSeeText('php artisan inegi:sync-states')
            ->assertSeeText('make vercel-sync-states')
            ->assertDontSee('id="states-table"', false);
    }

    public function test_the_municipalities_endpoint_returns_the_complete_provider_response(): void
    {
        $state = $this->createState('01', 'Aguascalientes', 1425607);
        Http::fake(['*' => Http::response($this->municipalitiesPayload())]);

        $this->get(route('states.municipalities', $state))
            ->assertOk()
            ->assertJsonCount(3, 'municipalities')
            ->assertJsonPath('municipalities.0.code', '001')
            ->assertJsonPath('municipalities.0.name', 'Aguascalientes')
            ->assertJsonPath('municipalities.0.total_population', 948990);
        $this->assertDatabaseCount('estados', 1);
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/mgem/01'));
    }

    public function test_the_municipalities_endpoint_returns_missing_population_as_null(): void
    {
        $state = $this->createState('02', 'Baja California', 3769020);
        $payload = $this->municipalitiesPayload('02');
        $payload['datos'][0]['cve_mun'] = '007';
        $payload['datos'][0]['nomgeo'] = 'San Felipe';
        unset($payload['datos'][0]['pob_total']);
        Http::fake(['*' => Http::response($payload)]);

        $this->get(route('states.municipalities', $state))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'San Felipe',
                'total_population' => null,
            ])
            ->assertJsonMissing(['message' => 'No fue posible consultar los municipios.']);
    }

    public function test_a_missing_state_returns_404_without_calling_inegi(): void
    {
        Http::fake();

        $this->get('/states/99/municipalities')->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_a_provider_error_returns_a_safe_json_response(): void
    {
        $state = $this->createState('01', 'Aguascalientes', 1425607);
        Http::fake(['*' => Http::response([], 503)]);

        $this->get(route('states.municipalities', $state))
            ->assertStatus(502)
            ->assertJsonPath('message', 'No fue posible consultar los municipios.')
            ->assertDontSee('gaia.inegi.org.mx')
            ->assertDontSee('RequestException');
    }

    public function test_an_invalid_municipality_payload_returns_the_same_safe_json_response(): void
    {
        $state = $this->createState('01', 'Aguascalientes', 1425607);
        Http::fake(['*' => Http::response(['datos' => [], 'numReg' => 0])]);

        $this->get(route('states.municipalities', $state))
            ->assertStatus(502)
            ->assertJsonPath('message', 'No fue posible consultar los municipios.');
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
