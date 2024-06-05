<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Models\Adviser;
use App\Models\BecomeAdviser;

class BecomeAdviserController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'age' => 'required|date',
            'gender' => 'required|in:male,female',
            'role' => 'required|in:client,admin,adviser,reviewer',
            'username' => 'required|string|max:255|unique:users',
            'specialities' => 'required',
            'description' => 'required',
            'downloading_a_file' => 'required|file',
        ]);

        // Determine the original file name
        $documentName = $request->file('downloading_a_file')->getClientOriginalName();

        // Attempt to store the file
        try {
            $path = $request->file('downloading_a_file')->storeAs('become_adviser', $documentName, 'public');
        } catch (\Exception $e) {
            // In case storing the file fails
            return response()->json(['error' => 'An error occurred while uploading the file'], 500);
        }

        // Create a new BecomeAdviser resource
        $becomeAdviser = BecomeAdviser::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'role' => $validatedData['role'],
            'age' => $validatedData['age'],
            'gender' => $validatedData['gender'],
            'username' => $validatedData['username'],
            'specialities' => $validatedData['specialities'],
            'description' => $validatedData['description'],
            'downloading_a_file' => $path,
        ]);

        // Check if the becomeAdviser was created successfully
        if ($becomeAdviser) {
            // Return a success response with the created becomeAdviser data
            return response()->json(['message' => 'Message sent successfully', 'becomeAdviser' => $becomeAdviser], 201);
        } else {
            // Return an error response if creating the becomeAdviser fails
            return response()->json(['error' => 'Failed to create the becomeAdviser'], 500);
        }
    }
}
