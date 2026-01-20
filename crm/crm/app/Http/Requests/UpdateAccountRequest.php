<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'legal_name' => ['nullable','string','max:255'],
            'vat' => ['nullable','string','max:64'],
            'domain' => ['nullable','string','max:255'],
            'website' => ['nullable','string','max:255'],
            'phone' => ['nullable','string','max:64'],
            'email' => ['nullable','email','max:255'],
            'status' => ['nullable','string'],
            'size' => ['nullable','string','max:64'],
            'source' => ['nullable','string','max:128'],
            // Billing
            'billing_street' => ['nullable','string','max:255'],
            'billing_street2' => ['nullable','string','max:255'],
            'billing_postal_code' => ['nullable','string','max:32'],
            'billing_city' => ['nullable','string','max:128'],
            'billing_state' => ['nullable','string','max:128'],
            'billing_country_code' => ['nullable','string','max:2'],
            'payment_method' => ['nullable','string','max:64'],
            'payment_term_days' => ['nullable','integer','min:0'],
            'iban' => ['nullable','string','max:64'],
            'bic' => ['nullable','string','max:64'],
            'sepa_mandate_ref' => ['nullable','string','max:128'],
            'sepa_mandate_date' => ['nullable','date'],
            'fiscal_position' => ['nullable','string','max:64'],
            'tax_exemption_reason' => ['nullable','string','max:255'],
            // E-invoicing
            'public_administration' => ['boolean'],
            'dir3_office' => ['nullable','string','max:64'],
            'dir3_manager' => ['nullable','string','max:64'],
            'dir3_unit' => ['nullable','string','max:64'],
            'e_invoice_channel' => ['nullable','string'],
            'e_invoice_identifier' => ['nullable','string','max:128'],
            'e_invoice_ready' => ['boolean'],
            'e_invoice_acceptance_status' => ['nullable','string'],
            'e_invoice_acceptance_at' => ['nullable','date'],
            'payment_effective_at' => ['nullable','date'],
            'payment_effective_amount' => ['nullable','numeric'],
            // Ownership
            'owner_user_id' => ['nullable','integer','exists:users,id'],
            'owner_team_id' => ['nullable','integer','exists:teams,id'],
            // Hierarchy & relations
            'parent_id' => ['nullable','integer','exists:accounts,id'],
        ];
    }
}
