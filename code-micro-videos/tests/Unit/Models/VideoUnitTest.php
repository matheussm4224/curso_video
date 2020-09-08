<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Video;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Uuid;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideoUnitTest extends TestCase
{
	private $video;

	public function setUp(): void
	{
		parent::setUp();
		$this->video = new Video();
	}

    public function testFillableAttribute()
    {
    	$this->assertEquals($this->video->getFillable(), 
       	[
	    	'title',
	    	'description',
	    	'year_launched',
	    	'opened',
	    	'rating',
	    	'duration'
    	]);
    }

    public function testInstantiationTraits() {
    	$traits = [SoftDeletes::class, Uuid::class];
    	$videoTraits = array_keys(class_uses(Video::class));
    	$this->assertEquals($traits, $videoTraits);
    }

    public function testCastsAttribute()
    {
        $this->assertEquals($this->video->getCasts(), 
        [
	    	'id' => 'string',
	    	'opened' => 'boolean',
	    	'year_launched' => 'integer',
	    	'duration' => 'integer'
    	]);
    }

    public function testIncrementingAttribute()
    {
        $this->assertEquals($this->video->incrementing, 
        	false);
    }

    public function testDatesAttribute()
    {
    	$dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
        	$this->assertContains($date, $this->video->getDates());
        }
        $this->assertCount(count($dates), $this->video->getDates());
    }

    public function testRelationshipsCategoryAttribute()
    {
    	$this->assertInstanceOf(BelongsToMany::class, $this->video->categories());
    }

     public function testRelationshipsGenreAttribute()
    {
    	$this->assertInstanceOf(BelongsToMany::class, $this->video->genres());
    }

}
