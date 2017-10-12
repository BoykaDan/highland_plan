<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreHelperPost extends FormRequest
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
            'uid' => 'required|exists:users,id',
            'url' => 'required|url',
        ];
    }

    /**
     * Get rule messages.
     *
     * @return array
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function messages()
    {
        return [
            'uid.required' => '请输入要添加的用户ID',
            'uid.exists' => '输入的用户不存在',
            'url.required' => '请输入助手用户主页地址',
            'url.url' => '助手地址请输入正确的URL',
        ];
    }
}
