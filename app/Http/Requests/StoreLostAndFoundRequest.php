<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLostAndFoundRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'gender' => 'required|in:frau,herr',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'date' => 'required|string|max:50',
            'time' => 'required|string|max:50',
            'bus_line' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'gender.required' => 'Anrede fehlt',
            'firstname.required' => 'Vorname fehlt',
            'lastname.required' => 'Nachname fehlt',
            'email.required' => 'E-Mail fehlt',
            'email.email' => 'E-Mail ungültig',
            'phone.required' => 'Telefon fehlt',
            'date.required' => 'Datum fehlt',
            'time.required' => 'Uhrzeit fehlt',
            'bus_line.required' => 'Buslinie fehlt',
            'description.required' => 'Beschreibung fehlt',
        ];
    }
}
