<?php 

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;


trait TestValidations {

	protected abstract function model();
	protected abstract function routerStore();
	protected abstract function routerUpdate();

	protected function assertInvalidationInStoreAction(array $data, string $rule, array $ruleParams = []) {
		 $response = $this->json('POST', $this->routerStore(), $data);
		 $fields = array_keys($data);
		 $this->assertInvalidationFields($response, $fields, $rule, $ruleParams);

		 return $response;


	}

	protected function assertInvalidationInUpdateAction(array $data, string $rule, array $ruleParams = []) {
		 $response = $this->json('PUT', $this->routerUpdate(), $data);
		 $fields = array_keys($data);
		 $this->assertInvalidationFields($response, $fields, $rule, $ruleParams);

		 return $response;


	}

	protected function assertInvalidationFields(TestResponse $response, array $fields, string $rule, array $ruleParams = []) {

		$response->assertStatus(422)
				 ->assertJsonValidationErrors($fields);

		foreach ($fields as $field) {
			$fieldName = str_replace("_", " ", $field);
			$response->assertJsonFragment([
				\Lang::get('validation.'.$rule, ['attribute' => $fieldName] + $ruleParams)
			]);
		}
	}
}

?>