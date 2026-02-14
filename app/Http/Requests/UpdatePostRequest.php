<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Post;

class UpdatePostRequest extends FormRequest
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
        // Quando a rota utiliza model binding, "post" vem como instância de Post
        // e não apenas como ID. Garantimos aqui que sempre seja um ID numérico
        // para a regra de unique funcionar corretamente.
        $routePost = $this->route('post');
        $postId = $routePost instanceof Post ? $routePost->id : $routePost;
        
        return [
            'titulo' => 'sometimes|required|string|max:255|unique:posts,titulo,' . $postId,
            'slug' => 'sometimes|nullable|string|max:255|unique:posts,slug,' . $postId,
            'status' => 'sometimes|required|in:rascunho,publicado,arquivado',
            'data_publicacao' => 'sometimes|nullable|date',
            'texto' => 'sometimes|required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O campo título é obrigatório.',
            'titulo.string' => 'O campo título deve ser uma string.',
            'titulo.max' => 'O campo título não pode ter mais de 255 caracteres.',
            'titulo.unique' => 'Este título já está em uso.',
            'slug.string' => 'O campo slug deve ser uma string.',
            'slug.max' => 'O campo slug não pode ter mais de 255 caracteres.',
            'slug.unique' => 'Este slug já está em uso.',
            'status.required' => 'O campo status é obrigatório.',
            'status.in' => 'O status deve ser rascunho, publicado ou arquivado.',
            'data_publicacao.date' => 'A data de publicação deve ser uma data válida.',
            'texto.required' => 'O campo texto é obrigatório.',
            'texto.string' => 'O campo texto deve ser uma string.',
        ];
    }
}
