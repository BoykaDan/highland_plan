@php
$active = $active ?? null;
@endphp

<ul class="nav nav-tabs app-header">
    <li role="serve" class="{{ $active == 'serve' ? 'active' : '' }}">
        <a href="{{ route('im:admin') }}">聊天服务器</a>
    </li>
    <li role="helper" class="{{ $active == 'helper' ? 'active' : '' }}">
        <a href="{{ route('im:admin-helper') }}">助手设置</a>
    </li>
</ul>