<?php

namespace App\Http\Controllers;

use App\Events\NewMessageSent;
use App\Http\Requests\GetMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatMessageController extends Controller
{

    /**
     * Get messages from a chat
     *
     * @param GetMessageRequest $request
     * @return JsonResponse
     */
    public function index(GetMessageRequest $request): JsonResponse
        {
            $data = $request->validated();
            $chatId = $data['chat_id'];

            $messages = ChatMessage::where('chat_id', $chatId)
                ->with('user')  // Eager load the related user
                ->latest('created_at')  // Order by the latest created_at
                ->get();

            return response()->json([
                'success' => true,
                'data' => $messages,
            ]);
        }



    /**
     * Create a chat message
     *
     * @param StoreMessageRequest $request
     * @return JsonResponse
     */
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->user()->id;

        $chatMessage = ChatMessage::create($data);
        $chatMessage->load('user');

        /// TODO send broadcast event to pusher and send notification to onesignal services
        $this->sendNotificationToOther($chatMessage);

        return $this->success($chatMessage, 'Message has been sent successfly.');
    }


    /***
     * Send notification to other users in the chat
     * @param ChatMessage $chatMessage
     */
    private function sendNotificationToOther(ChatMessage $chatMessage)
    {

        broadcast(new NewMessageSent($chatMessage))->toOthers();

        $user = auth()->user();
        $userId = $user->id;

        $chat = Chat::where('id', $chatMessage->chat_id)
            ->with(['participants' => function ($query) use ($userId) {
                $query->where('user_id', '!=', $userId);
            }])
            ->first();

        if (count($chat->participants) > 0) {
            $otherUserId = $chat->participants[0]->user_id;
            $otherUser = User::where('id', $otherUserId)->first();

            $otherUser->sendNewMessageNotification([
                'messageData' => [
                    'senderName' => $user->username,
                    'message' => $chatMessage->message,
                    'chatId' => $chatMessage->chat_id
                ]
            ]);
        }
    }
}
