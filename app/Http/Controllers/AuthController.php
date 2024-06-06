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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $validatedData['password'] = Hash::make($validatedData['password']);
        $validatedData['approved'] = $validatedData['role'] !== 'adviser'; // Set approved to false for advisers

        // Handle adviser-specific fields
        if ($validatedData['role'] === 'adviser') {
            if (!$request->hasFile('downloading_a_file')) {
                return response()->json(['error' => 'File is required for advisers'], 422);
            }

            // Determine the original file name and store the file
            $documentName = $request->file('downloading_a_file')->getClientOriginalName();
            try {
                $path = $request->file('downloading_a_file')->storeAs('become_adviser', $documentName, 'public');
                $validatedData['downloading_a_file'] = $path;
            } catch (\Exception $e) {
                return response()->json(['error' => 'An error occurred while uploading the file'], 500);
            }

            // Check if the email already exists in the advisers table
            if (DB::table('advisers')->where('email', $validatedData['email'])->exists()) {
                return response()->json(['error' => 'Email already exists in advisers table'], 422);
            }

            $validatedData['specialities'] = $request->input('specialities');
            $validatedData['description'] = $request->input('description');
        }

        // Create user
        $user = User::create($validatedData);
        $token = $user->createToken(User::USER_TOKEN);

        

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token->plainTextToken,
            ],
            'message' => 'User registered successfully',
        ], 201);
    }




    
    /**
     * Login a user
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $isValid = $this->isValidCredential($request);

        if (!$isValid['success']) {
            return response()->json(['success' => false, 'message' => $isValid['message']], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $isValid['user'];

        if (!$user->approved) {
            return response()->json(['success' => false, 'message' => 'Your account is not approved yet.'], Response::HTTP_FORBIDDEN);
        }
        
        $token = $user->createToken(User::USER_TOKEN);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token->plainTextToken,
            ],
            'message' => 'User logged in successfully',
        ]);
    }

    /**
     * Check if the user credentials are valid
     *
     * @param LoginRequest $request
     * @return array
     */
    private function isValidCredential(LoginRequest $request): array
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid credentials',
            ];
        }

        if (Hash::check($data['password'], $user->password)) {
            return [
                'success' => true,
                'user' => $user,
            ];
        }

        return [
            'success' => false,
            'message' => 'Password is incorrect',
        ];
    }

    /**
     * Login with token
     *
     * @return JsonResponse
     */
    public function loginWithToken(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->approved) {
            return response()->json(['success' => false, 'message' => 'Your account is not approved yet.'], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => null, // No new token is created here
            ],
            'message' => 'User logged in successfully',
        ], 200);
    }

    /**
     * Logout a user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'User logged out successfully']);
    }
}
