<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>「ThinkSNS+」 - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('zhiyi/im/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('zhiyi/im/app.css') }}">
    <script src="{{ asset('zhiyi/im/jquery-3.2.1.min.js') }}"></script>
    @stack('heads')
</head>
<body>
<div class="container-fluid">
    @yield('content')
</div>
<script src="{{ asset('zhiyi/im/bootstrap/js/bootstrap.min.js') }}"></script>
@stack('foots')
</body>
</html>