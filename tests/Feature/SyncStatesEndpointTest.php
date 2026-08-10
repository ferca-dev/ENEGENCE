<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\BuildsInegiPayloads;
use Tests\TestCase;

class SyncStatesEndpointTest extends TestCase
{
    use BuildsInegiPayloads;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.inegi.sync_token' => 'test-sync-token']);
    }

    public function test_it_rejects_requests_without_the_sync_token(): void
    {
        Http::fake();

        $this->postJson(route('internal.inegi.sync-states'))->assertUnauthorized();
        $this->withToken('wrong-token')
            ->postJson(route('internal.inegi.sync-states'))
            ->assertUnauthorized();

        Http::assertNothingSent();
        $this->assertDatabaseCount('estados', 0);
    }

    public function test_it_syncs_states_with_the_correct_token(): void
    {
        Http::fake(['*' => Http::response($this->statesPayload())]);

        $this->withToken('test-sync-token')
            ->postJson(route('internal.inegi.sync-states'))
            ->assertOk()
            ->assertExactJson([
                'ok' => true,
                'exit_code' => 0,
            ]);

        $this->assertDatabaseCount('estados', 32);
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/mgee/'));
    }

    public function test_it_reports_a_failed_sync(): void
    {
        Http::fake(['*' => Http::response(['datos' => [], 'numReg' => 0])]);

        $this->withToken('test-sync-token')
            ->postJson(route('internal.inegi.sync-states'))
            ->assertStatus(500)
            ->assertExactJson([
                'ok' => false,
                'exit_code' => 1,
            ]);

        $this->assertDatabaseCount('estados', 0);
    }
}
