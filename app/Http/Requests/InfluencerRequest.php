<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InfluencerRequest extends FormRequest
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
    public function rules()
    {
        // Detectar si es actualización (tiene ID en la ruta)
        $influencerId = $this->route('id') ?? $this->route('influencer');

        return [
            'name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('user_influencer', 'phone_number')->ignore($influencerId)
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('user_influencer', 'email')->ignore($influencerId)
            ],
            'social_handle' => 'nullable|string|max:255',
            'status' => 'in:active,inactive',
            'code' => [ // 🆕 Solo un código
                'required',
                'string',
                'max:50',
                Rule::unique('user_influencer', 'code')->ignore($influencerId)
            ],
            'code_description' => 'nullable|string|max:255', // 🆕 Descripción opcional
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'phone_number.required' => 'El número de teléfono es obligatorio.',
            'phone_number.unique' => 'El número ya está registrado.',
            'email.unique' => 'El correo ya está registrado.',
            'code.required' => 'El código es obligatorio.', // 🆕
            'code.unique' => 'El código ya está asignado a otro influencer.', // 🆕
            'code.max' => 'El código no puede tener más de 50 caracteres.', // 🆕
        ];
    }
}
