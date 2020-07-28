<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;

class GenreControllerTest extends TestCase
{

    use DatabaseMigrations;
    /**
     * Must return list the genres creates in JSON.
     *
     * @return void
     */
    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response->assertStatus(200)
                 ->assertJson([$genre->toArray()]);
    }

    /**
     * Must return the genre by id in JSON.
     *
     * @return void
     */
    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    /**
     * Must verify data validate. 
     *
     * @return void
     */
    public function testInvalidateData()
    {
        $response = $this->json('POST', route('genres.store', []));

        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('genres.store', 
            [
                'name' => str_repeat('b', 256),
                'is_active' => 'a'
            ]));

       $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $genre = factory(Genre::class)->create();
        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]),
            []
        );

        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]),
            [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]
        );

          $this->assertInvalidationMax($response);
          $this->assertInvalidationBoolean($response);
    }


    public function assertInvalidationRequired(TestResponse $response) {
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonMissingValidationErrors(['is_active'])
                 ->assertJsonFragment([
                    \Lang::get('validation.required', ['attribute' => 'name'])
                 ]);

    }


    public function assertInvalidationMax(TestResponse $response) {
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name'])
                 ->assertJsonFragment([
                    \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
                 ]);
    }


    public function assertInvalidationBoolean(TestResponse $response) {
        $response->assertStatus(422)
             ->assertJsonValidationErrors(['is_active'])
             ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
             ]);
    }

     /**
     * Must create genre is return genre creating. 
     *
     * @return void
     */
    public function testStore()
    {
        $response = $this->json('POST', route('genres.store', [
            'name' => 'test'
        ]));

        $genre = Genre::find($response->json('id'));

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());

        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('genres.store', [
            'name' => 'test2',
            'is_active' => false
        ]));

        $genre = Genre::find($response->json('id'));

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());

        $this->assertFalse($response->json('is_active'));

    }

    /**
     * Must update genre is return genre modify. 
     *
     * @return void
     */
    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'name' => 'test',
            'is_active' => false
        ]);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]),
            [
                'name' => 'test2',
                'is_active' => true
            ]
        );

        $genre = Genre::find($response->json('id'));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name' => 'test2',
                'is_active' => true
            ]);
    }


     /**
     * Must genre delete. 
     *
     * @return void
     */
    public function testDestroy()
    {
        $genre = factory(Genre::class)->create();

        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $genre->id]));

        $genre = Genre::find($genre->id);

        $response->assertStatus(204);

        $this->assertNull($genre);

    }

    



}
