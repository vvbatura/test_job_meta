<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadRequest extends FormRequest
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
        return [
            'file' => 'required|file',
            'fileName' => 'required|string',
            'chunk' => 'required|integer',
            'totalChunks' => 'required|integer',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->getMessages();
        $message = '';
        foreach ($errors as $error) {
            $message .= implode(', ', $error);
        }

        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'errors' => $errors,
                'message' => $message,
            ], 422)
        );
    }
}
