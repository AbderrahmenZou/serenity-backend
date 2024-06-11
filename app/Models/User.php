<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\MessageSent; 
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';
    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        // 'remember_token',
    ];

    /**
     * Determine if this user can report the specified chat.
     *
     * @param \App\Models\Chat $chat
     * @return bool
     */
    public function canReport(Chat $chat)
    {
        // Example: Check if the user is a participant of the chat
        return $chat->participants()->where('user_id', $this->id)->exists();
    }

    /**
     * Check if the user is an adviser.
     *
     * @return bool
     */
    public function isAdviser()
    {
        // Assuming there is a one-to-one relationship
        return $this->Adviser()->exists();
    }

    /**
     * Check if the user is a reviewer.
     *
     * @return bool
     */
    public function isReviewer()
    {
        // Assuming there is a one-to-one relationship
        return $this->Reviewer()->exists();
    }

    /**
     * Check if the user is either an adviser or a reviewer.
     *
     * @return bool
     */
    public function isAdviserOrReviewer()
    {
        return $this->isAdviser() || $this->isReviewer();
    }

    const USER_TOKEN = "userToken";

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'created_by');
    }

    public function routeNotificationForOneSignal(): array
    {
        return ['tags' => ['key' => 'userId', 'relation' => '=', 'value' => (string)($this->id)]];
    }

    public function sendNewMessageNotification(array $data): void
    {
        $this->notify(new MessageSent($data));
    }

    public function Admin()
    {
        return $this->hasOne(Admin::class, 'id_user');
    }

    public function Client()
    {
        return $this->hasOne(Client::class, 'id_user');
    }

    public function Reviewer()
    {
        return $this->hasOne(Reviewer::class, 'id_user');
    }

    public function Adviser()
    {
        return $this->hasOne(Adviser::class, 'id_user');
    }
}
