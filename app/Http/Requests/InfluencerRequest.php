<?php

namespace App\Http\Requests;

use App\Models\InfluencerCode;
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
        $isUpdate = !is_null($influencerId);

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
            'codes' => 'nullable|array',
            'codes.*' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($influencerId) {
                    $exists = InfluencerCode::where('code', $value)
                        ->when($influencerId, fn($q) => $q->where('influencer_id', '!=', $influencerId))
                        ->exists();

                    if ($exists) {
                        $fail("El código '{$value}' ya está asignado a otro influencer.");
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'phone_number.required' => 'El número de teléfono es obligatorio.',
            'phone_number.unique' => 'El número ya existe.',
            'email.unique' => 'El correo ya está registrado.',
            'codes.*.required' => 'Los códigos no pueden estar vacíos.',
        ];
    }
}
