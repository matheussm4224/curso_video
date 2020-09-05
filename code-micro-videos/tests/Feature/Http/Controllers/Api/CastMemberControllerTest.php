<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use App\Models\CastMember;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;


class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $castMember;

    protected function setUp(): void {
        parent::setUp();

        $this->castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
    } 

    public function testIndex()
    {
        $response = $this->get(route('cast_member.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);

    }
    public function testShow()
    {
        $response = $this->get(route('cast_member.show', ['cast_member' => $this->castMember->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->castMember->toArray());
    }


    public function testInvalidateData()
    {   
        $data = [
            'name' => ''
        ];
        $response = $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);


        $data = [
            'type' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');

    }

    public function testStore()
    {   
        $data = [
            'name' => 'test',
            'type' => CastMember::TYPE_ACTOR
        ];
        $response = $this->assertStore($data, $data + ['type' => CastMember::TYPE_ACTOR, 'deleted_at' => null]);

        $response->assertJsonStructure(['created_at', 'updated_at']);
        $data = [
            'name' => 'test',
            'type' => CastMember::TYPE_ACTOR
        ];
        $this->assertStore($data, $data);

    }


    public function testUpdate()
    {

         $data = [
            'name' => 'test',
            'type' => CastMember::TYPE_ACTOR
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

    }

    public function testDestroy()
    {

        $response = $this->json('DELETE', route('cast_member.destroy', ['cast_member' => $this->castMember->id]));

        $castMember = CastMember::find($this->castMember->id);

        $response->assertStatus(204);

        $this->assertNull($castMember);
    }


    protected function routerStore() {
        return route('cast_member.store');
    }

    protected function routerUpdate() {
        return route('cast_member.update', $this->castMember->id);
    }

    protected function model() {
        return CastMember::class;
    }

}
