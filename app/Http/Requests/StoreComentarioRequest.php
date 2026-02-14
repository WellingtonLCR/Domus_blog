<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComentarioRequest extends FormRequest
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
            'post_id' => 'required|exists:posts,id',
            'texto' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'post_id.required' => 'O campo post_id é obrigatório.',
            'post_id.exists' => 'O post informado não existe.',
            'texto.required' => 'O campo texto é obrigatório.',
            'texto.string' => 'O campo texto deve ser uma string.',
            'texto.max' => 'O campo texto não pode ter mais de 1000 caracteres.',
        ];
    }
}
