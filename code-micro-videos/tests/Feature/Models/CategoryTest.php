<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Must list the categories create.
     *
     * @return void
     */
    public function testList()
    {
        factory(Category::class, 1)->create();

        $categories = Category::all();

        $this->assertCount(1, $categories);
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], array_keys($categories->first()->getAttributes()));
    }


    /**
     * Must create the category.
     *
     * @return void
     */
    public function testCreate()
    {
        $category = Category::create([
            'name' => 'test1'
        ]);

        $category->refresh();

        $this->assertEquals('test1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create([
            'name' => 'test1',
            'description' => null
        ]);

        $this->assertNull($category->description);


        $category = Category::create([
            'name' => 'test1',
            'description' => 'test description'
        ]);

        $this->assertEquals('test description', $category->description);

        $category = Category::create([
            'name' => 'test1',
            'is_active' => false
        ]);

        $this->assertFalse($category->is_active);

         $category = Category::create([
            'name' => 'test1',
            'is_active' => true
        ]);

        $this->assertTrue($category->is_active);
        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($category->id));
    } 


    /**
     * Must edit the category by id.
     *
     * @return void
     */
    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description' => 'test description',
            'is_active' => false
        ])->first();

        $update = [
            'name' => 'test2',
            'description' => 'test update description',
            'is_active' => true
        ];

        $category->update($update);

        foreach($update as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }

    /**
     * Must remove the category by id.
     *
     * @return void
     */
    public function testRemove()
    {
        $category = factory(Category::class)->create([
            'name' => 'test2',
            'description' => 'test description',
            'is_active' => false
        ])->first();

        $category->delete();

        $this->assertNull(Category::find($category->id));

    }
}
