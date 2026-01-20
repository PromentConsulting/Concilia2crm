<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'account_id' => ['nullable','integer','exists:accounts,id'],
            'first_name' => ['required','string','max:128'],
            'last_name' => ['required','string','max:128'],
            'email' => ['required','email','max:255','unique:contacts,email,'.$this->contact->id],
            'mobile' => ['nullable','string','max:64'],
            'phone' => ['nullable','string','max:64'],
            'extension' => ['nullable','string','max:16'],
            'job_title' => ['nullable','string','max:128'],
            'department' => ['nullable','string','max:128'],
            'language' => ['nullable','string','max:8'],
            'timezone' => ['nullable','string','max:64'],
            'preferred_channel' => ['nullable','string'],
            'decision_level' => ['nullable','string'],
            'status' => ['nullable','string'],
            'primary' => ['boolean'],
            // RGPD
            'legal_basis' => ['nullable','string'],
            'consent_status' => ['nullable','string'],
            'consent_at' => ['nullable','date'],
            'consent_source' => ['nullable','string','max:255'],
            'consent_ip' => ['nullable','string','max:64'],
            // Ownership
            'owner_user_id' => ['nullable','integer','exists:users,id'],
            'owner_team_id' => ['nullable','integer','exists:teams,id'],
        ];
    }
}
