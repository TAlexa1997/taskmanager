<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    
    public function index()
    {
        $tasks = Task::all();
        
        return response()->json($tasks, 200); 
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
            'user_id' => 'required|exists:users,id' ,
            'image' => 'nullable|string'
        ]);

        $task = Task::create($validatedData);

        return response()->json([
            'message' => 'Feladat sikeresen létrehozva!',
            'data' => $task
        ], 201); 
    }
}