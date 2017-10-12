# 动态赞相关接口

- [点赞](#点赞)
- [取消赞](#取消赞)
- [赞的人列表](#赞的人列表)

## 点赞

```
POST /feeds/:feed/like
```

#### Response

```
Status: 201 Created
```
```json
{
    "message": [
        "操作成功"
    ]
}
```

#### 通知类型

```json5
{
    "channel": "feed:digg", // 通知关键字
    "target": 325, // 动态id
    "content": "@2222 点赞了你的动态",
    "extra": {
        "user_id": 2 // 点赞者id
    }
}
```

#### 用户收到的点赞

```json5
  {
    "id": 7,
    "user_id": 2,
    "target_user": 2,
    "likeable_id": 327,
    "likeable_type": "feeds",
    "created_at": "2017-07-14 07:35:38",
    "updated_at": "2017-07-14 07:35:38",
    "likeable": {
        ... // 动态内容  参考单条动态内容
    }
  }
```
## 取消赞

```
DELETE /feeds/:feed/unlike
```

#### Response

```
Status: 204 Not Content
```

## 赞的人列表

```
GET /feeds/:feed/likes
```

### Parameters

| 名称 | 类型 | 描述 |
|:----:|:----:|----|
| limit | Integer | 获取条数，默认 20 |
| after | Integer | 获取之后数据，默认 0 |

#### Response

```
Status: 200 OK
```

```json5
[
    // ...
    {
        "id": 2, // 赞 ID
        "user_id": 1, // 赞的用户
        "target_user": 1, // 目标用户
        "likeable_id": 1, // 目标内容ID
        "likeable_type": "feeds", // 目标来源
        "created_at": "2017-07-12 08:09:07", // 点赞时间
        "updated_at": "2017-07-12 08:09:07" // 点赞更新时间
    }
]
```
