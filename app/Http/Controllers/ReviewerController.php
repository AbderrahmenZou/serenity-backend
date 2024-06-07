<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewerController extends Controller
{
    public function index()
    {
        $advisers = User::where('role', 'adviser')->where('approved', false)->get();
        return response()->json($advisers);
    }

    public function show($id)
    {
        // Retrieve the user with the specified ID, only if they are an adviser
        $adviser = User::where('role', 'adviser')->find($id);

        if (!$adviser) {
            // If no adviser is found, return a 404 response
            return response()->json(['message' => 'Adviser not found'], 404);
        }

        // If an adviser is found, return the adviser data
        return response()->json($adviser);
    }
    
    public function approve(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->role !== 'adviser') {
            return response()->json(['message' => 'Only advisers can be approved'], 400);
        }

        if (!Auth::check() || Auth::user()->role !== 'reviewer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->approved) {
            return response()->json(['message' => 'Adviser is already approved'], 400);
        }

        $user->approved = true;
        $user->save();

        return response()->json(['message' => 'Adviser approved successfully!']);
    }

    public function delete($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'reviewer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->role !== 'adviser') {
            return response()->json(['message' => 'Only advisers can be deleted'], 400);
        }

        $user->delete();  // This now performs a soft delete

        
        return response()->json(['message' => 'Adviser deleted successfully']);
    }
}
