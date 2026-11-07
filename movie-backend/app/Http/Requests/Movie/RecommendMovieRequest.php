<?php

namespace App\Http\Requests\Movie;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecommendMovieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('prompt')) {
            if ($this->has('query')) {
                $this->merge(['prompt' => $this->input('query')]);
            } elseif ($this->has('description')) {
                $this->merge(['prompt' => $this->input('description')]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:500'],
        ];
    }
}
