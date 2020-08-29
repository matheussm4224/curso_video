<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use Tests\Stubs\Models\CategoryStub;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Illuminate\Http\Request;
use \Illuminate\Validation\ValidationException;
use \Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Api\BasicCrudController;


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

	public function testStore() {
		$request = \Mockery::mock(Request::class);
		$request->shouldReceive('all')
				->once()
				->andReturn(['name' => 'teste', 'description' => 'test']);

		$data = $this->controller->store($request);

		$this->assertEquals(
			CategoryStub::find(1)->toArray(),
		 	$data->toArray());
	}


	public function testFindByIdFetchModel() {
		$category = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);
		$reflection = new \ReflectionClass(BasicCrudController::class);
		$method = $reflection->getMethod('show');
		$method->setAccessible(true);

		$result = $method->invokeArgs($this->controller, [$category->id]);
		$this->assertInstanceOf(CategoryStub::class, $result);
	}

	public function testFindByIdThrowError(){
		$this->expectException(ModelNotFoundException::class);
		$reflection = new \ReflectionClass(BasicCrudController::class);
		$method = $reflection->getMethod('show');
		$method->setAccessible(true);

		$result = $method->invokeArgs($this->controller, [0]);
	}

	public function testInvalidationDataInUpdate()
	{	
		$category = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);

		$this->expectException(ValidationException::class);

		$request = \Mockery::mock(Request::class);
		$request->shouldReceive('all')
				->once()
				->andReturn(['is_active' => 'a']);

		$reflection = new \ReflectionClass(BasicCrudController::class);
		$method = $reflection->getMethod('update');
		$method->setAccessible(true);

		$result = $method->invokeArgs($this->controller, [$request, $category->id]);
	}


	public function testUpdate() {
		$category = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);

		$request = \Mockery::mock(Request::class);
		$request->shouldReceive('all')
				->once()
				->andReturn([
					'description' => 'test'
				]);

		$reflection = new \ReflectionClass(BasicCrudController::class);
		$method = $reflection->getMethod('update');
		$method->setAccessible(true);

		$result = $method->invokeArgs($this->controller, [$request, $category->id]);

		$this->assertEquals(
			$category->refresh()->toArray(),
			$result->toArray());
	}

	public function testDestroyThrowError(){
		$this->expectException(ModelNotFoundException::class);
		$reflection = new \ReflectionClass(BasicCrudController::class);
		$method = $reflection->getMethod('destroy');
		$method->setAccessible(true);

		$result = $method->invokeArgs($this->controller, [0]);
	}


	public function testDestroy() {
		$category = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);

		$reflection = new \ReflectionClass(BasicCrudController::class);
		$method = $reflection->getMethod('destroy');
		$method->setAccessible(true);

		$result = $method->invokeArgs($this->controller, [$category->id]);
		$this->assertEquals(
			204,
			$result->status()
		);

		$this->assertCount(0, CategoryStub::all());
	}
}