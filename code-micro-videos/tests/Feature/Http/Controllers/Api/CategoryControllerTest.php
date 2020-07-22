<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;

class CategoryControllerTest extends TestCase
{

    use DatabaseMigrations;
    /**
     * Must return list the categories creates in JSON.
     *
     * @return void
     */
    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);

    }

    /**
     * Must return the category by id in JSON.
     *
     * @return void
     */
    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }


    /**
     * Must verify data validate. 
     *
     * @return void
     */
    public function testInvalidateData()
    {
        $response = $this->json('POST', route('categories.store', []));

        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('categories.store', 
            [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]
        ));

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $category = factory(Category::class)->create();
        $response = $this->json('PUT', route('categories.update', [
            'category' => $category->id
        ]), []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]
        ), 
        [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

    }


    protected function assertInvalidationRequired(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingVAlidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }



    protected function assertInvalidationMax(TestResponse $response) {
         $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }


    protected function assertInvalidationBoolean(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }


     /**
     * Must create category is return category creating. 
     *
     * @return void
     */
    public function testStore()
    {
        $response = $this->json('POST', route('categories.store', [
            'name' => 'test'
        ]));

        $category = Category::find($response->json('id'));

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());

        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store', [
            'name' => 'test',
            'description' => 'description',
            'is_active' => false
        ]));


        $category = Category::find($response->json('id'));

        $response
                ->assertStatus(201)
                ->assertJson($category->toArray())
                ->assertJsonFragment([
                    'description' => 'description',
                    'is_active' => false
                ]);

    }


    /**
     * Must update category is return category modify. 
     *
     * @return void
     */
    public function testUpdate()
    {
         $category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
         ]);

        $response = $this->json('PUT', route('categories.update', [
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
        ]);

    }


}
