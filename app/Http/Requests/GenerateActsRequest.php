<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateActsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'table_file' => ['required', 'max:50000', 'mimes:xlsx,xls,xlsm'],
        ];
    }
}
