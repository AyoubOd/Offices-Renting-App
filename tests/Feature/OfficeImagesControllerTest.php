<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Office;
use App\Models\User;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class OfficeImagesControllerTest extends TestCase
{
    use WithFaker;
    /**
     * @test
     *
     * @return void
     */
    public function itCanUploadImageForAnOffice()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        Sanctum::actingAs($user, ['images.create']);

        $response = $this->postJson('/api/offices/' . $office->id . '/images', [
            'image' => HttpUploadedFile::fake()->image('image.jpg')
        ]);

        $response->assertCreated();
    }
}
