<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $todos = Todo::all();

        $todos_list = [];
        if ($todos->isNotEmpty()) {
            foreach ($todos as $todo_data) {
                $todos_list[$todo_data['id']] = [
                    'id' => $todo_data['id'],
                    'name' => $todo_data['name'],
                    'is_completed' => $todo_data['is_completed']
                ];
            }
        }

        return $todos_list;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = [];

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Name field is required and must be less than 255 characters.'
            ], 503);
        }

        $todo = new Todo();
        $todo->name         = $request->name;
        $todo->is_completed = false;
        $todo->save();

        return response()->json(['id' => $todo->id], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Todo::where('id', $id)->update(['is_completed' => $request->is_completed]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Todo::where('id', $id)->delete();
    }
}
