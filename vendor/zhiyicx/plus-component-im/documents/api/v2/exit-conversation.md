# 删除聊天对话

##接口地址
`/api/v2/im/conversations/members/{cid}`

##请求方法
`DELETE `

##特别说明:
地址中的cid为对话id,如果该对话id不存在会返回错误

## 返回体
```json5
{
    "cid": 12
}
```

```
Status 204 No Content
```

## 返回字段
| name     | type     | must     | description |
|----------|:--------:|:--------:|:--------:|
| cid  | int      | yes      | 当前退出的聊天对话ID |

