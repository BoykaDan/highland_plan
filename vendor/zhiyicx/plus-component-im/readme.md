# ✈️ThinkSNS+ 聊天服务拓展包

此包是针对「成都·智艺创想」所开发的 IM 即使聊天服务集成包。

> 使用本包前需要确定你已经拥有并安装了 IM 服务器。

## 相关文档

- [API documents](documents/api/v1)

## 安装

首先，我们要确立 ThinkSNS+ 和 IM 服务器的依赖关系，只需要运行：

```shell
composer require zhiyicx/plus-component-im
```

运行完成命令后，代码上已经确立了关系，但是还并没有完成拓展包的部署，所以你还需要执行：

```shell
php artisan package:handle im install
```

运行完成，提示成功后，进入后台 -> 即时聊天 进行服务信息的设置即可。

## 卸载

卸载分为两种，一种是删除 IM 和 ThinkSNS+ 的服务注入，则只需运行：

```shell
php artisan package:handle im remove
```

如果你希望完全删除 IM 拓展，运行上面的命令取消服务注入后，再运行：

```shell
composer remove zhiyicx/plus-component-im
```

提示成功后，即 IM 模块已经从 ThinkSNS+ 系统中完全的删除。
