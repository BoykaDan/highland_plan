@extends('component-im::layouts.app')

@section('title', '服务地址设置')

@section('content')

    @include('component-im::layouts.header', [
        'active' => 'serve'
    ])

    <div class="panel panel-default" style="margin-top: 16px;">
        <div class="panel-heading">设置聊天服务器</div>
        <div class="panel-body">
            <form class="form-horizontal" role="form" method="POST" action="{{ route('im.manage.request') }}">

                {{ csrf_field() }}
                {{ method_field('PATCH') }}

                <!-- 服务器地址表单 -->
                <div class="form-group">
                    <label class="col-sm-2 control-label">地址</label>
                    <div class="col-sm-4">
                        <input class="form-control" name="serve" type="text" placeholder="请输入聊天服务器地址" value="{{ old('serve', $serve) }}">
                    </div>
                    <span class="col-sm-6 help-block">
                        输入聊天服务器的连接地址，例如默认的「127.0.0.1:9900」。输入的服务器不存在或者错误，将会造成 app 等客户端的运行异常。
                    </span>
                </div>

                <!-- 提交按钮 -->
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button class="btn btn-primary" type="submit" >提交</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- 错误消息提示 -->
    @if (! $errors->isEmpty())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- 正确消息提示 -->
    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

@endsection
