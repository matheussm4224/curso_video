<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{

     use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;

    protected function setUp(): void {
        parent::setUp();

        $this->genre = factory(Genre::class)->create();
    } 

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);

    }
    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }


    public function testInvalidateData()
    {   
        $data = [
            'name' => ''
        ];
        $response = $this->assertInvalidationInStoreAction($data, 'required');
        $response->assertJsonMissingValidationErrors(['is_active']);
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);


        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');

    }

    public function testStore()
    {   
        $data = [
            'name' => 'test'
        ];
        $response = $this->assertStore($data, $data + ['is_active' => true, 'deleted_at' => null]);

        $response->assertJsonStructure(['created_at', 'updated_at']);
        $data = [
            'name' => 'test',
            'is_active' => false
        ];
        $this->assertStore($data, $data);

    }


    public function testUpdate()
    {
         $this->genre = factory(Genre::class)->create([
            'is_active' => false
         ]);

         $data = [
            'name' => 'test',
            'is_active' => true
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

    }

    public function testDestroy()
    {

        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $this->genre->id]));

        $genre = Genre::find($this->genre->id);

        $response->assertStatus(204);

        $this->assertNull($genre);
    }


    protected function routerStore() {
        return route('genres.store');
    }

    protected function routerUpdate() {
        return route('genres.update', $this->genre->id);
    }

    protected function model() {
        return Genre::class;
    }


}
