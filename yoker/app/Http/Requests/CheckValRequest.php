<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CheckValRequest extends Request
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
                //必须输入用户名且长度最大为16
                'adminName'=>'required|alpha_dash|Max:16',
                'adminPassword'=>'required|alpha_num|Max:16',
            //
        ];
    }

   //错误信息
    public function messages(){
    return [
        'adminName.required' => '用户名不能为空',
        'adminPassword.required'  => '密码不能为空',
        'adminName.max' => '用户名超过16个字符',
        'adminPassword.max' => '密码超过16个字符',
        'adminName.alpha_dash'=>'用户名含有非法字符',
        'adminPassword.alpha_num'=>'密码含有非法字符',

    ];
  
  }
}
