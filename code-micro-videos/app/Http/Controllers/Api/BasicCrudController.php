<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


abstract class BasicCrudController extends Controller
{
	protected abstract function model();
	protected abstract function rulesStore();  

	public function index() {
		return $this->model()::all();
	} 

	public function store(Request $request)
	{
		$this->validate($request, $this->rulesStore());
	}

}

