<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdviserIndexRequest;
use App\Models\Adviser;
use App\Models\User;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;

class AdviserController extends Controller

{
    public function index()
    {
        $data = Adviser::all();
        if ($data->isNotEmpty()) {
            echo $data->first()->id;
        }
        return $this->success($data);
    }

    public function show($adviser)
    {
        $data = User::where('id', $adviser)
            ->get();
        return $this->success($data);
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('search');
    
        $advisers = Adviser::where('first_name', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                           ->get();
        return response()->json([
            'data' => $advisers,
            'success' => true,
            'message' => 'نجاح',
        ]);
    }
    
    // public function store(Request $request)
    // {
    //     $data = new Adviser();
    //     $data->name = $request->name;
    //     $data->email = $request->email;
    //     $data->save();
    //     return $this->success($data);
    // }
}
