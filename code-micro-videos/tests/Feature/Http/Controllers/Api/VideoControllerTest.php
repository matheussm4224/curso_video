<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Http\Request;
use \App\Http\Controllers\Api\VideoController;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

use Tests\Exceptions\TestException;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;


    protected function setUp(): void {
        parent::setUp();
        $this->video = factory(Video::class)->create();
    } 

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);

    }
    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }


    public function testInvalidateData()
    {   
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
        ];
        $response = $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'title' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);


        $data = [
            'duration' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');

        $data = [
            'year_launched' => 's'
        ];

        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);

        $data = [
            'opened' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');

         $data = [
            'rating' => 0
        ];

        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');

    }


    public function testInvalidateCategoriesIdField() {
        $data = [
            'categories_id' => 'a'
        ];

        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'categories_id' => [100]
        ];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidateGenresIdField() {
         $data = [
            'genres_id' => 'a'
        ];

        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'genres_id' => [100]
        ];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testStore()
    {   
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $data = [
            'title' => 'test',
            'description' => 'teste',
            'year_launched' => 2020,
            'rating' => Video::RATING_LIST[array_rand(Video::RATING_LIST)],
            'duration' => 30
        ];
        $relationship = [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ];
        $response = $this->assertStore($data + $relationship, $data + ['opened' => false, 'deleted_at' => null]);

        $response->assertJsonStructure(['created_at', 'updated_at']);

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $data = [
            'title' => 'test',
            'description' => 'teste',
            'year_launched' => 2020,
            'rating' => Video::RATING_LIST[array_rand(Video::RATING_LIST)],
            'duration' => 30,
            'opened' => true
        ];
        $relationship = [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ];
        $this->assertStore($data + $relationship, $data);

    }


    public function testUpdate()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
         $this->video = factory(Video::class)->create([
            'opened' => true,
         ]);

         $data = $this->video->toArray();
         $data['opened'] = false;

         $relationship = [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ];

        $response = $this->assertUpdate($data + $relationship, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

    }

    public function testRollbackStore() {
        $controller = \Mockery::mock(VideoController::class)
                        ->makePartial()
                        ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->video->toArray());

        $controller
            ->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $request = \Mockery::mock(Request::class);


        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());
        try{
            $controller->store($request);
        } catch(TestException $e) {
            $this->assertCount(1, Video::all());
        }

    }

    public function testDestroy()
    {

        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->video->id]));

        $video = Video::find($this->video->id);

        $response->assertStatus(204);

        $this->assertNull($video);
    }


    protected function routerStore() {
        return route('videos.store');
    }

    protected function routerUpdate() {
        return route('videos.update', $this->video->id);
    }

    protected function model() {
        return Video::class;
    }

}
