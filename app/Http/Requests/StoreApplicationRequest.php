<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_id' => 'required|string',
            'gender' => 'required|in:frau,herr',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'zip' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'german_skills' => 'required|in:muttersprache,gut,grundkenntnisse',
            'permit' => 'required|in:schweizer,b,c,g,eu_efta',
            'application_files' => 'required|array|min:1|max:5',
            'application_files.*' => 'required|string',
            'criminal_record' => 'required|string',
            'ivz_register' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'job_id.required' => 'Die Stelle ist erforderlich.',
            'gender.required' => 'Bitte wählen Sie eine Anrede.',
            'gender.in' => 'Ungültige Anrede.',
            'firstname.required' => 'Bitte geben Sie Ihren Vornamen ein.',
            'firstname.max' => 'Der Vorname darf maximal 255 Zeichen lang sein.',
            'lastname.required' => 'Bitte geben Sie Ihren Nachnamen ein.',
            'lastname.max' => 'Der Nachname darf maximal 255 Zeichen lang sein.',
            'street.required' => 'Bitte geben Sie Ihre Strasse ein.',
            'street.max' => 'Die Strasse darf maximal 255 Zeichen lang sein.',
            'zip.required' => 'Bitte geben Sie Ihre Postleitzahl ein.',
            'zip.max' => 'Die Postleitzahl darf maximal 10 Zeichen lang sein.',
            'city.required' => 'Bitte geben Sie Ihren Ort ein.',
            'city.max' => 'Der Ort darf maximal 255 Zeichen lang sein.',
            'phone.required' => 'Bitte geben Sie Ihre Telefonnummer ein.',
            'phone.max' => 'Die Telefonnummer darf maximal 50 Zeichen lang sein.',
            'email.required' => 'Bitte geben Sie Ihre E-Mail-Adresse ein.',
            'email.email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
            'email.max' => 'Die E-Mail-Adresse darf maximal 255 Zeichen lang sein.',
            'german_skills.required' => 'Bitte wählen Sie Ihre Deutschkenntnisse.',
            'german_skills.in' => 'Ungültige Deutschkenntnisse.',
            'permit.required' => 'Bitte wählen Sie Ihre Bewilligung.',
            'permit.in' => 'Ungültige Bewilligung.',
            'application_files.required' => 'Bitte laden Sie mindestens eine Bewerbungsdatei hoch.',
            'application_files.min' => 'Bitte laden Sie mindestens eine Bewerbungsdatei hoch.',
            'application_files.max' => 'Sie können maximal 5 Bewerbungsdateien hochladen.',
            'criminal_record.required' => 'Bitte laden Sie Ihren Strafregisterauszug hoch.',
            'ivz_register.required' => 'Bitte laden Sie Ihren IVZ-Registerauszug hoch.',
        ];
    }
}
