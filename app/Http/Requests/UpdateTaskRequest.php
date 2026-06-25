<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'request_a4_id'    => ['nullable', 'integer', 'exists:requests_a4,id'],
            'task_type_id'     => ['nullable', 'integer', 'exists:task_types,id'],
            'assigned_to'      => ['nullable', 'integer', 'exists:users,id'],
            'status'           => ['required', Rule::in(['pending', 'in_progress', 'done', 'cancelled', 'on_hold'])],
            'priority'         => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'start_date'       => ['nullable', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_hours'  => ['nullable', 'numeric', 'min:0'],
            'actual_hours'     => ['nullable', 'numeric', 'min:0'],
            'progress'         => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_recurring'     => ['boolean'],
            'weekly_hours'     => ['nullable', 'numeric', 'min:0', 'required_if:is_recurring,true'],
            'recurrence_end'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_milestone'     => ['boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'dependency_ids'   => ['nullable', 'array'],
            'dependency_ids.*' => ['integer', 'exists:tasks,id'],
            'attachments'      => ['nullable', 'array'],
            'attachments.*'    => ['file', 'max:10240'],
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
            'title'           => __('fields.title'),
            'request_a4_id'   => __('fields.request_a4'),
            'task_type_id'    => __('fields.task_type'),
            'assigned_to'     => __('fields.assigned_to'),
            'status'          => __('fields.status'),
            'priority'        => __('fields.priority'),
            'start_date'      => __('fields.start_date'),
            'end_date'        => __('fields.end_date'),
            'estimated_hours' => __('fields.estimated_hours'),
            'actual_hours'    => __('fields.actual_hours'),
            'weekly_hours'    => __('fields.weekly_hours'),
        ];
    }
}
