<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // FilePond can send file under various field names
        return [
            'file' => 'sometimes|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'application_files' => 'sometimes|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'criminal_record' => 'sometimes|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'ivz_register' => 'sometimes|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];
    }

    public function getUploadedFile()
    {
        return $this->file('file')
            ?? $this->file('application_files')
            ?? $this->file('criminal_record')
            ?? $this->file('ivz_register');
    }

    protected function passedValidation(): void
    {
        if (!$this->getUploadedFile()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => ['Bitte wählen Sie eine Datei aus.'],
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Bitte wählen Sie eine Datei aus.',
            'file.file' => 'Die hochgeladene Datei ist ungültig.',
            'file.mimes' => 'Erlaubte Dateitypen: PDF, Word, JPG, PNG.',
            'file.max' => 'Die Datei darf maximal 10 MB gross sein.',
        ];
    }
}
