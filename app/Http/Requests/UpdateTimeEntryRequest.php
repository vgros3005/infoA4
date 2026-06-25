<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTimeEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'task_id'     => ['required', 'integer', 'exists:tasks,id'],
            'entry_date'  => ['required', 'date', 'before_or_equal:today'],
            'hours'       => ['required', 'numeric', 'min:0.25', 'max:24'],
            'comment'     => ['nullable', 'string', 'max:1000'],
            'is_billable' => ['boolean'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hours.min' => __('validation.hours_minimum'),
            'hours.max' => __('validation.hours_maximum'),
        ];
    }

    /**
     * Custom attribute names for error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'task_id'    => __('fields.task'),
            'entry_date' => __('fields.entry_date'),
            'hours'      => __('fields.hours'),
            'comment'    => __('fields.comment'),
        ];
    }
}
