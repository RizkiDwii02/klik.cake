<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'customer_name' => 'required|string|max:100',
            'customer_phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\s\-()]+$/'],
            'customer_email' => 'nullable|email|max:100',
            'delivery_type' => 'required|in:delivery,pickup',
            'delivery_zone_id' => 'required_if:delivery_type,delivery|nullable|exists:delivery_zones,id',
            'delivery_address' => 'required_if:delivery_type,delivery|nullable|string|max:500',
            'fulfillment_date' => 'required|date',
            'fulfillment_slot' => 'required|string',
            'promo_code' => 'nullable|string|max:50',
            'customer_notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama lengkap wajib diisi.',
            'customer_phone.required' => 'Nomor WhatsApp wajib diisi.',
            'customer_phone.regex' => 'Format nomor WhatsApp tidak valid.',
            'customer_email.email' => 'Format alamat email tidak valid.',
            'delivery_type.required' => 'Pilih metode pengantaran (Delivery atau Pick-up).',
            'delivery_zone_id.required_if' => 'Pilih zona pengantaran untuk metode Delivery.',
            'delivery_address.required_if' => 'Alamat lengkap wajib diisi untuk metode Delivery.',
            'fulfillment_date.required' => 'Tanggal jadwal fresh bake wajib ditentukan.',
            'fulfillment_slot.required' => 'Slot waktu pengantaran/pengambilan wajib dipilih.',
        ];
    }
}
