<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentIm\AdminControllers;

use Illuminate\Http\Request;
use Zhiyi\Plus\Http\Controllers\Controller;
use Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Repository\ImServe as ImServeRepostory;

class HomeController extends Controller
{
    /**
     * The component manage entry.
     *
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function show(Request $request, ImServeRepostory $repostiory)
    {
        if (! $request->user()) {
            return redirect(route('admin'), 302);
        }

        return view('component-im::admin', ['serve' => $repostiory->get()]);
    }

    /**
     * 更新服务地址接口.
     *
     * @param Request $request
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function update(Request $request, ImServeRepostory $repostiory)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());

        $repostiory->store(
            $request->input('serve')
        );

        return redirect()->back()
            ->with('message', '更新成功');
    }

    /**
     * Get the serve validation rules.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'serve' => 'required',
        ];
    }

    /**
     * Get the serve validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages(): array
    {
        return [
            'serve.required' => '请输入服务器地址',
        ];
    }
}
