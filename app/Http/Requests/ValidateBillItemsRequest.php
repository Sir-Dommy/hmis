<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateBillItemsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bill_items' => 'required|array', // Ensure bill_items is an array
            'bill_items.*.service' => 'required|exists:services,name',
            'bill_items.*.department' => 'nullable|exists:departments,name',
            'bill_items.*.consultation_category' => 'nullable|exists:consultation_categories,name',
            'bill_items.*.clinic' => 'nullable|exists:clinics,name',
            'bill_items.*.payment_type' => 'nullable|exists:payment_types,name',
            'bill_items.*.scheme' => 'nullable|exists:schemes,name',
            'bill_items.*.scheme_type' => 'nullable|exists:scheme_types,name',
            'bill_items.*.consultation_type' => 'nullable|exists:consultation_types,name',
            'bill_items.*.visit_type' => 'nullable|exists:visit_types,name',
            'bill_items.*.doctor' => 'nullable|string', // Assuming doctor is a free-text field
            'bill_items.*.lab_test_type' => 'nullable|exists:lab_test_types,name',
            'bill_items.*.image_test_type' => 'nullable|exists:image_test_types,name',
            'bill_items.*.drug' => 'nullable|exists:drugs,name',
            'bill_items.*.brand' => 'nullable|exists:brands,name',
            'bill_items.*.branch' => 'nullable|exists:branches,name',
            'bill_items.*.building' => 'nullable|exists:buildings,name',
            'bill_items.*.wing' => 'nullable|exists:wings,name',
            'bill_items.*.ward' => 'nullable|exists:wards,name',
            'bill_items.*.office' => 'nullable|exists:offices,name',
            'bill_items.*.discount' => 'nullable|numeric', // Ensure discount is a numeric value
            'bill_items.*.current_time' => 'nullable|date_format:H:i', // Ensure time is in H:i format
        ];
    }
}
