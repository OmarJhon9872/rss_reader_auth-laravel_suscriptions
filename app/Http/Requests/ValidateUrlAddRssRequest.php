<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateUrlAddRssRequest extends FormRequest{

    public function authorize()
    {
        return true;
    }


    public function rules(){

        return [
            'rss_url' => 'required|url'
        ];
    }
}
