<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
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
        $usuarioId = $this->route('usuario');
        
        return [
            'nome' => 'sometimes|required|string|max:255',
            'usuario' => 'sometimes|required|string|max:255|unique:usuarios,usuario,' . $usuarioId,
            'senha' => 'sometimes|required|string|min:6',
            'biografia' => 'nullable|string|max:1000',
            'banido_em' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser uma string.',
            'nome.max' => 'O campo nome não pode ter mais de 255 caracteres.',
            'usuario.required' => 'O campo usuário é obrigatório.',
            'usuario.string' => 'O campo usuário deve ser uma string.',
            'usuario.max' => 'O campo usuário não pode ter mais de 255 caracteres.',
            'usuario.unique' => 'Este usuário já está em uso.',
            'senha.required' => 'O campo senha é obrigatório.',
            'senha.string' => 'O campo senha deve ser uma string.',
            'senha.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'biografia.string' => 'O campo biografia deve ser uma string.',
            'biografia.max' => 'O campo biografia não pode ter mais de 1000 caracteres.',
            'banido_em.date' => 'O campo banido em deve ser uma data válida.',
        ];
    }
}
