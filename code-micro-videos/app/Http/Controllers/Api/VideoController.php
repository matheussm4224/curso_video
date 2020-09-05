<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BasicCrudController;
use App\Models\Video;
use Illuminate\Support\Facades\DB;

class VideoController extends BasicCrudController
{
   
	private $rules;

    function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:'.implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id',
            'genres_id' => 'required|array|exists:genres,id'
        ];
    }

    public function store(Request $request) {
        $validateData = $this->validate($request, $this->rulesStore());
        $self = $this;
        $result = \DB::transaction(function() use ($request, $validateData, $self) {
            $result  = $self->model()::create($validateData);
            $self->handleRelations($result, $request);
            return $result;
        });
        return $result->refresh();
    }

    protected function update(Request $request, $id) {
        $model = $this->model();
        $validateData = $this->validate($request, $this->rulesUpdate());
        $self = $this;
        $data = (new $model)->findOrFail($id);
        $data = \DB::transaction(function() use ($data, $request, $validateData, $self) {
            $data->update($validateData);
            $self->handleRelations($data, $request);
            return $data;
        });
        return $data->refresh();
    }

    protected function handleRelations($video, $request) {
        $video->categories()->sync($request->get('categories_id'));
        $video->genres()->sync($request->get('genres_id'));
    } 

    protected function model() {
        return Video::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

}
