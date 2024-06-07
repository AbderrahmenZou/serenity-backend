<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userModel = get_class(new User());

        return [
            'user_id' => "required|exists:{$userModel},id",
            'appointed_date' => 'date_format:Y-m-d H:i:s',
            'name' => 'nullable',
            'is_private' => 'nullable|boolean',
        ];
    }
}
