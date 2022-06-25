<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Image;
use App\Models\Tag;

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

        $response->assertStatus(200);
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

        $response->assertStatus(200);
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
            '/api/offices?user_id=' . $host->id
        );

        $response->assertJsonCount(1, 'data');
        // making sure the returned office belongs to the requested host
        $this->assertEquals($office->id, $response->json('data')[0]['id']);
    }


    /**
     * @test
     */

    public function itReturnsCountOfReservationPerOffice()
    {
        $tags = Tag::factory(2);
        $user = User::factory()->create();
        Image::factory(1)->for($user, 'resource')->create();
        $office = Office::factory()->for($user, 'user')->has($tags)->create();
        $images = Image::factory(3)->for($office, 'resource')->create();
        Reservation::factory(3)->for($office, 'office')->create();

        $response = $this->get('/api/offices');

        $this->assertEquals(3, $response->json('data')[0]['reservations_count']);
    }


    /**
     * @test
     */

    public function itOrderByDistance()
    {
        // 34.03350426619025, -6.770779397422873

        $office1 = Office::factory()->create([
            'lat' => '33.56944577728282',
            'lng' => '-7.589895810088722',
            'title' => 'Casablanca'
        ]);

        $office2 = Office::factory()->create([
            'lat' => '31.63606386410263',
            'lng' => '-7.97077618431435',
            'title' => 'Marrakesh'
        ]);

        $response = $this->get('/api/offices?lat=34.03350426619025&lng=-6.770779397422873');
        $response->assertOk();
        $this->assertEquals('Casablanca', $response->json('data')[0]['title']);
        $this->assertEquals('Marrakesh', $response->json('data')[1]['title']);

        // if we didn't provide tha lat and tha lng
        $response = $this->get('/api/offices');
        $response->assertOk();
        $this->assertGreaterThan($response->json('data')[0]['id'], $response->json('data')[1]['id']);
    }

    /**
     * @test
     */

    public function itShowsTheCountOfReservationsPerOffice()
    {
        $office = Office::factory()->create();
        Reservation::factory(3)->for($office, 'office')->create();

        $response = $this->get('/api/offices/' . $office->id);
        $response->dump('data');
        $this->assertEquals(3, $response->json('data')['reservations_count']);
    }
}
