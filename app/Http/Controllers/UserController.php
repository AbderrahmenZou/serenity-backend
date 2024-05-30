<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Adviser;
class UserController extends Controller
{
    /**
     *Gets users except yourself
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = User::where('role', 'Adviser')->get();
        return $this->success($users);
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

}
