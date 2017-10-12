@extends('component-im::layouts.app')

@section('title', '助手设置')

@section('content')

    @include('component-im::layouts.header', [
        'active' => 'helper'
    ])

    <table class="table">
        <thead>
            <tr>
                <th>助手用户🆔</th>
                <th>助手首页地址</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($helpers as $helper)
                <tr>
                    <td>{{ $helper['uid'] }}</td>
                    <td>{{ $helper['url'] }}</td>
                    <td>
                        <a class="btn btn-danger" href="{{ route('im:admin-helper-delete', [ 'uid' => $helper['uid'] ]) }}" role="button">删除</a>
                    </td>
                </tr>
            @endforeach
            <tr>
                <form role="form" method="POST" action="{{ route('im:admin-helper-store') }}">
                    {{ csrf_field() }}
                    <td>
                        <input class="form-control" type="text" name="uid" placeholder="请输入助手用户ID" value="{{ old('uid') }}" />
                    </td>
                    <td>
                        <input class="form-control" type="text" name="url" placeholder="请输入助手首页地址" value="{{ old('url') }}" />
                    </td>
                    <td>
                        <button type="sumbit" class="btn btn-primary">添加</button>
                    </td>
                </form>
            </tr>
        </tbody>
    </table>

    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

@endsection