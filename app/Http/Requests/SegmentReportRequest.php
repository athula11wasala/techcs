<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SegmentReportRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'segmentType' => 'bail|required|numeric|min:0|max:3',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'segmentType.required' => 'Segment type is required',
        ];
    }
}
