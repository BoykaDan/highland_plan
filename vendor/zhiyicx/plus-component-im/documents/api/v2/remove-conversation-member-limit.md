# 移除对话成员限制

##接口地址
`/api/v2/im/conversations/members/limited/{cid}/{uid}`

##请求方法
`delete `

##特别说明:
地址中的cid为对话id,如果该对话id不存在会返回错误,uid为需要解除限制的用户uid

## 返回体

```
Status 204 ON Content
```
## 返回字段
| name     | type     | must     | description |
|----------|:--------:|:--------:|:--------:|
|cid		|int		|yes		|对话id|
|uid		|int	   | yes		 |本次解除限制的成员uid标识|

