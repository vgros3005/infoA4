<?php

namespace App\Http\Requests;

use App\Models\Priority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequestA4Request extends FormRequest
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
            'title'                  => ['required', 'string', 'max:50'],
            'description'            => ['required', 'string'],
            'content'                => ['nullable', 'string'],
            'internal_notes'         => ['nullable', 'string'],
            'request_type_id'        => ['required', 'integer', 'exists:request_types,id'],
            'priority_id'            => ['required', 'integer', 'exists:priorities,id'],
            'priority_justification' => [
                'nullable',
                'string',
                'max:2000',
                $this->justificationRule(),
            ],
            'status_id'              => ['nullable', 'integer', 'exists:statuses,id'],
            'assigned_team_id'       => ['nullable', 'integer', 'exists:teams,id'],
            'company_ids'            => ['nullable', 'array'],
            'company_ids.*'          => ['integer', 'exists:companies,id'],
            'software_ids'           => ['nullable', 'array'],
            'software_ids.*'         => ['integer', 'exists:softwares,id'],
            'desired_date'           => ['nullable', 'date'],
            'estimated_hours'        => ['nullable', 'numeric', 'min:0'],
            'attachments'            => ['nullable', 'array'],
            'attachments.*'          => ['file', 'max:10240'], // 10 MB per file
        ];
    }

    /**
     * Build a conditional 'required_if' rule based on the selected priority flag.
     *
     * @return \Illuminate\Contracts\Validation\ValidationRule|string
     */
    private function justificationRule(): string
    {
        $iPriorityId = (int) $this->input('priority_id');

        if ($iPriorityId > 0) {
            $oPriority = Priority::find($iPriorityId);
            if ($oPriority?->requires_justification) {
                return 'required';
            }
        }

        return 'nullable';
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.max'                       => __('validation.title_max_50'),
            'priority_justification.required' => __('validation.justification_required_for_priority'),
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
            'title'                  => __('fields.title'),
            'description'            => __('fields.description'),
            'content'                => __('fields.content'),
            'request_type_id'        => __('fields.request_type'),
            'priority_id'            => __('fields.priority'),
            'priority_justification' => __('fields.priority_justification'),
            'company_ids'            => __('fields.companies'),
            'software_ids'           => __('fields.softwares'),
            'requested_date'         => __('fields.requested_date'),
            'desired_date'           => __('fields.desired_date'),
        ];
    }
}
