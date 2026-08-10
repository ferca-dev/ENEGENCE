<?php

namespace Tests\Unit;

use App\Services\InegiService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\Support\BuildsInegiPayloads;
use Tests\TestCase;
use UnexpectedValueException;

class InegiServiceTest extends TestCase
{
    use BuildsInegiPayloads;

    public function test_it_maps_the_number_of_states_returned_by_the_provider(): void
    {
        Http::fake(['*' => Http::response($this->statesPayload(count: 3))]);

        $states = app(InegiService::class)->states();

        $this->assertCount(3, $states);
        $this->assertSame([
            'code' => '01',
            'name' => 'Aguascalientes',
            'abbreviation' => 'Ags.',
            'total_population' => 1425607,
            'female_population' => 728924,
            'male_population' => 696683,
            'inhabited_dwellings' => 386671,
        ], $states[0]);
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/mgee/'));
    }

    public function test_it_maps_and_orders_municipalities(): void
    {
        Http::fake(['*' => Http::response($this->municipalitiesPayload())]);

        $municipalities = app(InegiService::class)->municipalities('01');

        $this->assertSame(['001', '002', '003'], array_column($municipalities, 'code'));
        $this->assertSame(948990, $municipalities[0]['total_population']);
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/mgem/01'));
    }

    public function test_it_accepts_a_municipality_without_census_population(): void
    {
        $payload = $this->municipalitiesPayload('02');
        unset($payload['datos'][0]['pob_total']);
        Http::fake(['*' => Http::response($payload)]);

        $municipalities = app(InegiService::class)->municipalities('02');

        $municipality = collect($municipalities)->firstWhere('code', '003');
        $this->assertNull($municipality['total_population']);
    }

    public function test_it_rejects_a_payload_without_data(): void
    {
        Http::fake(['*' => Http::response(['numReg' => 32])]);

        $this->expectException(UnexpectedValueException::class);

        app(InegiService::class)->states();
    }

    public function test_it_rejects_a_collection_with_duplicate_state_codes(): void
    {
        $payload = $this->statesPayload();
        $payload['datos'][31]['cve_ent'] = '01';

        Http::fake(['*' => Http::response($payload)]);

        $this->expectException(UnexpectedValueException::class);

        app(InegiService::class)->states();
    }

    public function test_it_rejects_a_state_without_demographic_details(): void
    {
        $payload = $this->statesPayload();
        unset($payload['datos'][0]['pob_femenina']);

        Http::fake(['*' => Http::response($payload)]);

        $this->expectException(UnexpectedValueException::class);

        app(InegiService::class)->states();
    }

    public function test_it_rejects_a_municipality_from_another_state(): void
    {
        $payload = $this->municipalitiesPayload();
        $payload['datos'][0]['cve_ent'] = '02';

        Http::fake(['*' => Http::response($payload)]);

        $this->expectException(UnexpectedValueException::class);

        app(InegiService::class)->municipalities('01');
    }

    public function test_it_propagates_provider_errors(): void
    {
        Http::fake(['*' => Http::response([], 503)]);

        $this->expectException(RequestException::class);

        app(InegiService::class)->states();
    }
}
