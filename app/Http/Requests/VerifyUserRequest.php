<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyUserRequest extends FormRequest{

    public function authorize(){
        return true;
    }


    public function rules(){
        if($this->route()->hasParameter('usuario')){
            $usuario_actualizando = $this->route()->parameter('usuario')->id;
            $validacion = 'required|unique:users,email,'.$usuario_actualizando;
        }else{
            $validacion = 'required|unique:users,email';
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'last1' => ['nullable', 'string', 'max:255'],
            'last2' => ['nullable', 'string', 'max:255'],
            'email' => $validacion,
            'password' => ['string', 'nullable', 'min:8']
        ];
    }
}
