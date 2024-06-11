<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetChatRequest;
use App\Http\Requests\DeleteChatRequest;
use App\Http\Requests\StoreChatRequest;
use App\Models\Chat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Gets chats
     *
     * @param GetChatRequest $request
     * @return JsonResponse
     */
    public function index(GetChatRequest $request): JsonResponse
    {
        $data = $request->validated();

        $isPrivate = 1;
        if ($request->has('is_private')) {
            $isPrivate = (int)$data['is_private'];
        }

        $chats = Chat::where('is_private', $isPrivate)
            ->hasParticipant(auth()->user()->id)
            ->whereHas('messages')
            ->with('lastMessage.user', 'participants.user')
            ->latest('updated_at')
            ->get();
        return $this->success($chats);
    }


    /**
     * Stores a new chat
     *
     * @param StoreChatRequest $request
     * @return JsonResponse
     */
    public function store(StoreChatRequest $request): JsonResponse
    {
        $data = $this->prepareStoreData($request);
        if ($data['userId'] === $data['otherUserId']) {
            return $this->error('You can not create a chat with your own');
        }

        $previousChat = $this->getPreviousChat($data['otherUserId']);

        if ($previousChat === null) {

            $chat = Chat::create($data['data']);
            $chat->participants()->createMany([
                [
                    'user_id' => $data['userId']
                ],
                [
                    'user_id' => $data['otherUserId']
                ]
            ]);

            $chat->refresh()->load('lastMessage.user', 'participants.user');
            return $this->success($chat);
        }
        return $this->success($previousChat->load('lastMessage.user', 'participants.user'));
    }

    /**
     * Check if user and other user has previous chat or not
     *
     * @param int $otherUserId
     * @return mixed
     */
    private function getPreviousChat(int $otherUserId): mixed
    {
        $userId = auth()->user()->id;

        return Chat::where('is_private', 1)
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereHas('participants', function ($query) use ($otherUserId) {
                $query->where('user_id', $otherUserId);
            })
            ->first();
    }


    /**
     * Prepares data for store a chat
     *
     * @param StoreChatRequest $request
     * @return array
     */
    private function prepareStoreData(StoreChatRequest $request): array
    {
        $data = $request->validated();
        $otherUserId = (int)$data['user_id'];
        unset($data['user_id']);
        $data['created_by'] = auth()->user()->id;
        $data['appointed_date'] = $data['appointed_date'] ?? null;
        
        return [
            'otherUserId' => $otherUserId,
            'userId' => auth()->user()->id,
            'data' => $data,
        ];
    }


    /**
     * Gets a single chat
     *
     * @param Chat $chat
     * @return JsonResponse
     */
    public function show(Chat $chat): JsonResponse
    {
        $chat->load('lastMessage.user', 'participants.user');
        return $this->success($chat);
    }


/**
 * Deletes a single chat
 *
 * @param int $chatId
 * @return JsonResponse
 */
public function destroy($chatId)
{
    try {
        $chat = Chat::findOrFail($chatId); // Ensures the chat exists
        $chat->delete(); // Perform the deletion
        return response()->json(['message' => 'Chat deleted successfully'], 204); // Return success response
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['message' => 'Chat not found'], 404); // Return not found response
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to delete chat', 'error' => $e->getMessage()], 500); // Return error response
    }
}

    /**
     * Reports a chat as inappropriate or spam.
     *
     * @param int $chatId
     * @return JsonResponse
     */
    public function report($chatId)
    {
        try {
            // Log the attempt to report the chat
            \Log::info("Reporting chat with ID: {$chatId}");

            $chat = Chat::findOrFail($chatId); // Ensures the chat exists

            // Optional: Check if the authenticated user has the right to report the chat
            if (!auth()->user()->canReport($chat)) {
                return response()->json(['message' => 'Unauthorized to report this chat'], 403);
            }

            $chat->is_reported = true; // Set the chat as reported
            $chat->save(); // Save the updated chat

            return response()->json(['message' => 'Chat reported successfully'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("Failed to find chat with ID: {$chatId}"); // Log the failure to find the chat
            return response()->json(['message' => 'Chat not found'], 404);
        } catch (\Exception $e) {
            \Log::error("Error reporting chat with ID: {$chatId}: {$e->getMessage()}"); // Log other exceptions
            return response()->json(['message' => 'Failed to report chat', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Retrieves all reported chats.
     * Only accessible by reviewers.
     *
     * @return JsonResponse
     */
    public function getReportedChats(): JsonResponse
    {
        $user = auth()->user();

        // التحقق مما إذا كان المستخدم هو مراجع فقط
        if (!$user->isReviewer()) {
            return response()->json(['message' => 'Unauthorized to access reported chats'], 403);
        }

        $reportedChats = Chat::where('is_reported', true)->with('lastMessage.user', 'participants.user')->get();
        return response()->json($reportedChats);
    }
}
