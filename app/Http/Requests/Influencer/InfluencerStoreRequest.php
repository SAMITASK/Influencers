<?php

namespace App\Http\Requests\Influencer;

use App\Models\InfluencerCode;
use Illuminate\Foundation\Http\FormRequest;

class InfluencerStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:user_influencer,phone_number',
            'email' => 'nullable|email|unique:user_influencer,email',
            'social_handle' => 'nullable|string|max:255',
            'status' => 'in:active,inactive',
            'codes' => 'nullable|array',
            'codes.*' => [
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    $exists = InfluencerCode::where('code', $value)->exists();
                    if ($exists) {
                        $fail("El código '{$value}' ya está asignado a otro influencer.");
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'phone_number.required' => 'El número de teléfono es obligatorio.',
            'phone_number.unique' => 'El número ya existe.',
            'email.unique' => 'El correo ya está registrado.',
            'codes.array' => 'Los códigos deben enviarse como un array.',
            'codes.*.string' => 'Cada código debe ser una cadena de texto.',
        ];
    }
}
