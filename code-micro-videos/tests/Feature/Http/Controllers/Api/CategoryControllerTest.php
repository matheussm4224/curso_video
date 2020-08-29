<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{

    use DatabaseMigrations, TestValidations, TestSaves;

    private $category;

    protected function setUp(): void {
        parent::setUp();

        $this->category = factory(Category::class)->create();
    } 
    /**
     * Must return list the categories creates in JSON.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);

    }

    /**
     * Must return the category by id in JSON.
     *
     * @return void
     */
    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->category->toArray());
    }


    /**
     * Must verify data validate. 
     *
     * @return void
     */
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

     /**
     * Must create category is return category creating. 
     *
     * @return void
     */
    public function testStore()
    {   
        $data = [
            'name' => 'test'
        ];
        $response = $this->assertStore($data, $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);

        $response->assertJsonStructure(['created_at', 'updated_at']);
        $data = [
            'name' => 'test',
            'description' => 'description',
            'is_active' => false
        ];
        $this->assertStore($data, $data);

    }


    /**
     * Must update category is return category modify. 
     *
     * @return void
     */
    public function testUpdate()
    {
         $this->category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
         ]);

         $data = [
            'name' => 'test',
            'description' => 'test2',
            'is_active' => true
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);


        $data = [
            'name' => 'test',
            'description' => '',
        ];

        $this->assertUpdate($data, array_merge($data, ['description' => null]));


        /*$response = $this->json('PUT', route('categories.update', [
            'category' => $category->id
        ]), [
            'name' => 'test',
            'description' => 'test2',
            'is_active' => true
        ]);

        $category = Category::find($response->json('id'));

        $response
                ->assertStatus(200)
                ->assertJson($category->toArray())
                ->assertJsonFragment([
                    'description' => 'test2',
                    'is_active' => true
                ]);

        $response = $this->json('PUT', route('categories.update', [
            'category' => $category->id
        ]), [
            'name' => 'test',
            'description' => '',
            'is_active' => true
        ]);

        $response->assertJsonFragment([
            'description' => null
        ]);


        $response = $this->json('PUT', route('categories.update', [
            'category' => $category->id
        ]), [
            'name' => null
        ]);


        $response
        ->assertStatus(422)
        ->assertJsonFragment([
            \Lang::get('validation.required', ['attribute' => 'name'])
        ]);*/

    }


    /**
     * Must category delete. 
     *
     * @return void
     */
    public function testDestroy()
    {

        $response = $this->json('DELETE', route('categories.destroy', ['category' => $this->category->id]));

        $category = Category::find($this->category->id);

        $response->assertStatus(204);

        $this->assertNull($category);
    }


    protected function routerStore() {
        return route('categories.store');
    }

    protected function routerUpdate() {
        return route('categories.update', $this->category->id);
    }

    protected function model() {
        return Category::class;
    }


}
