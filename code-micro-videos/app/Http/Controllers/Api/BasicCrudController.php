<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


abstract class BasicCrudController extends Controller
{
	protected abstract function model();
	protected abstract function rulesStore();  
	protected abstract function rulesUpdate();

	public function index() {
		return $this->model()::all();
	} 

	public function store(Request $request)
	{
		$validateData = $this->validate($request, $this->rulesStore());
		$result  = $this->model()::create($validateData);
		return $result->refresh();
	}

	protected function show($id) {
		$model = $this->model();

		$keyName = (new $model)->getRouteKeyName();
		return $model::where($keyName, $id)->firstOrFail();
	}

	protected function update(Request $request, $id) {
		$model = $this->model();
		$validateData = $this->validate($request, $this->rulesUpdate());
		$data = (new $model)->findOrFail($id);
		$data->update($validateData);
		return $data->refresh();
	}


	protected function destroy($id) {
		$model = $this->model();
		(new $model)->findOrFail($id)->delete();
		return response()->noContent(); 
	}

}

