<?php

namespace App\Http\Requests;

use App\Rules\MaxTourists;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'origin' => ['required', 'string'],
            'destination' => ['required', 'string', 'different:origin'],
            'departure_date' => ['required', 'date', 'after:now'],
            'return_date' => ['required', 'date', 'after:+1 day'],
            'tourists.adults' => ['required', 'numeric', 'between:1,10'],
            'tourists.children' => ['required', 'numeric', new MaxTourists('tourists.babies')],
            'tourists.babies' => ['required', 'numeric', new MaxTourists('tourists.children')],
        ];
    }
}
