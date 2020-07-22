<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreTest extends TestCase
{

    use DatabaseMigrations;
    /**
     * Must list the categories create.
     *
     * @return void
     */
    public function testList()
    {
        factory(Genre::class, 1)->create();

        $genres = Genre::all();

        $this->assertCount(1, $genres);
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], array_keys($genres->first()->getAttributes()));

    }


     /**
     * Must create the genre.
     *
     * @return void
     */
    public function testCreate()
    {
        $genre = Genre::create([
            'name' => 'Terror'
        ]);

        $genre->refresh();

        $this->assertEquals('Terror', $genre->name);
        $this->assertTrue($genre->is_active);

        $genre = Genre::create([
            'name' => 'Aventura',
            'is_active' => false
        ]);

        $this->assertFalse($genre->is_active);

         $genre = Genre::create([
            'name' => 'Animação',
            'is_active' => true
        ]);

        $this->assertTrue($genre->is_active);
        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($genre->id));
    } 


    /**
     * Must edit the genre by id.
     *
     * @return void
     */
    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'name' => 'Terror',
            'is_active' => false
        ])->first();

        $update = [
            'name' => 'Terror e Drama',
            'is_active' => true
        ];

        $genre->update($update);

        foreach($update as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    /**
     * Must remove the genre by id.
     *
     * @return void
     */
    public function testRemove()
    {
        $genre = factory(Genre::class)->create([
            'name' => 'Terror',
            'is_active' => false
        ])->first();

        $genre->delete();

        $this->assertNull(Genre::find($genre->id));

    }
}
