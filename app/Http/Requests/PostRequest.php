<?php

// app/Http/Requests/PostRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'  => 'required|string|max:255',
            'body'   => 'required|string',
            'status' => 'required|in:draft,published',
        ];
    }
}