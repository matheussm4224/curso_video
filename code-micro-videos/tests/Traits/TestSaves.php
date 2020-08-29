<?php 
declare(strict_types=1);
namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;

trait TestSaves {

	protected abstract function model();
	protected abstract function routerStore();
	protected abstract function routerUpdate();

	protected function assertStore(array $data, array $dataTest, array $dataJson = null): TestResponse {
		$response = $this->json('POST', $this->routerStore(), $data);
		if($response->status() !== 201) {
			throw new \Exception("Response status must be 201, given {$response->status()}: \n {$response->content()}");
		}
		$this->assertInDatabase($response, $dataTest);
		$jsonResponse = $dataJson ?? $dataTest;
		$this->assertJsonResponseContent($response, $jsonResponse);
		return $response;
	}

	protected function assertUpdate(array $data, array $dataTest, array $dataJson = null): TestResponse {
		$response = $this->json('PUT', $this->routerUpdate(), $data);
		if($response->status() !== 200) {
			throw new \Exception("Response status must be 200, given {$response->status()}: \n {$response->content()}");
		}
		$this->assertInDatabase($response, $dataTest);
		$jsonResponse = $dataJson ?? $dataTest;
		$this->assertJsonResponseContent($response, $jsonResponse);
		return $response;
	}



	private function assertInDatabase(TestResponse $response, array $dataTest): void {
		$model = $this->model();
		$table = (new $model)->getTable();
		$this->assertDatabaseHas($table, $dataTest + ['id' => $response->json('id')]);
	}

	private function assertJsonResponseContent(TestResponse $response, array $jsonResponse) {
		$response->assertJsonFragment($jsonResponse + ['id' => $response->json('id')]);
	}

}

?>