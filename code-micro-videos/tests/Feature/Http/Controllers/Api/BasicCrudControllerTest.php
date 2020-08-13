<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use Tests\Stubs\Models\CategoryStub;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Illuminate\Http\Request;
use \Illuminate\Validation\ValidationException;


class BasicCrudControllerTest extends TestCase
{
	private $controller;
	protected function setUp(): void {
		parent::setUp();
		CategoryStub::dropTable();
		CategoryStub::createTable();
		$this->controller = new CategoryControllerStub();
	}

	protected function tearDown(): void {
		CategoryStub::dropTable();
		parent::tearDown();
	}

	public function testIndex() {
		$category = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);
		$this->assertEquals([$category->toArray()], $this->controller->index()->toArray());
	}

	public function testInvalidationDataInStore()
	{	
		$this->expectException(ValidationException::class);
		$request = \Mockery::mock(Request::class);
		$request->shouldReceive('all')
				->once()
				->andReturn(['name' => '']);

		$this->controller->store($request);
	}
}