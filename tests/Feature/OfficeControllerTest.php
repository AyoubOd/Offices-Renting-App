<?php

namespace Tests\Feature;

use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OfficeControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function itListsAllOfficesPaginatedWay()
    {
        Office::factory(3)->create();

        $response = $this->get('/api/offices');

        $response->assertStatus(200)->dump();
        $this->assertNotNull($response->json('data')[0]['id']);
        $this->assertNotNull($response->json('meta'));
        $this->assertNotNull($response->json('links'));
    }

    /**
     * @test
     */
    public function itOnlyListsOfficesThatAreNotHiddenAndApproved()
    {
        Office::factory(3)->create();

        $response = $this->get('/api/offices');

        Office::factory()->create(['approval_status' => Office::APPROVAL_PENDING]);
        Office::factory()->create(['hidden' => true]);

        $response->assertStatus(200)->dump();
        $response->assertJsonCount(3, 'data');
    }

    /**
     * @test
     */
    public function itFiltersByHostId()
    {
        Office::factory(3)->create();

        $host = User::factory()->create();
        $office = Office::factory()->for($host)->create();
        $response = $this->get(
            '/api/offices?host_id=' . $host->id
        );

        $response->assertStatus(200)->dump();
        $response->assertJsonCount(1, 'data');
        // making sure the returned office belongs to the requested host
        $this->assertEquals($office->id, $response->json('data')[0]['id']);
    }
}
