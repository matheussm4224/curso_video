<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Uuid;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{

    private $category;


    protected function setUp(): void {
        parent::setUp();
        $this->category = new Category();
    }


    /**
     * This fillable attribute must contain an array with name, description and is_active.
     *
     * @return void
     */
    public function testFillableAttribute()
    {
        $this->assertEquals($this->category->getFillable(), 
        	['name', 'description', 'is_active']);
    }

    /**
     *	This traits must contain instantiation traits, such as SoftDeletes and Traits\Uuid
     *
     * @return void
     */
    public function testInstantiationTraits() {
    	$traits = [SoftDeletes::class, Uuid::class];
    	$categoryTraits = array_keys(class_uses(Category::class));
    	$this->assertEquals($traits, $categoryTraits);
    }

    /**
     * This casts attribute must contain an array with 'id' equal key is 'string' equal value.
     *
     * @return void
     */
    public function testCastsAttribute()
    {
        $this->assertEquals($this->category->getCasts(), 
        	['id' => 'string', 'is_active' => 'boolean']);
    }

    /**
     * The increment attribute value must be equal to false.
     *
     * @return void
     */
    public function testIncrementingAttribute()
    {
        $this->assertEquals($this->category->incrementing, 
        	false);
    }

    /**
     * This dates attribute must contain an array with deleted_at, created_at and updated_at.
     *
     * @return void
     */
    public function testDatesAttribute()
    {
    	$dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
        	$this->assertContains($date, $this->category->getDates());
        }
        $this->assertCount(count($dates), $this->category->getDates());
    }


}
