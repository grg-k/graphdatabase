<?php

namespace App\Http\Requests;

use App\Rules\HandleStoreRelationshipRule;
use Illuminate\Foundation\Http\FormRequest;

class HandleStoreRelationshipRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $requiredIf = function () {
            return $this->get('directed') ? 'required' : 'nullable';
        };

        return [
            'type' => ['required', 'string'],
            'directed' => ['required', 'boolean'],
            'source' => [$requiredIf],
            'destination' => [$requiredIf],
            'properties' => ['required'],
        ];
    }
}

