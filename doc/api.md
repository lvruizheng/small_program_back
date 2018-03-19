# 小家伙后台API文档
### 返回errcode说明

     {'errcode': 401, 'errMsg': '用户未登录'}
     {'errcode': 400, 'errMsg': '参数错误'}

以下是一些通用的返回码

返回码   |   说明
-------- | --------
0        | 请求成功
400      | 参数错误，请检查所需参数是否完整，类型是否正确
401      | 用户未认证
403      | 无效的token
500      | 服务器错误，具体原因不详

### 注意
1. 小程序用到的相关的接口(接口1.*, 3.*)，请求参数中注明token的，需要把token添加到request headers进行请求，而不是作为参数进行传递
2. 在小程序中，注明必须token的接口，必须要在request headers中传入token，否则会返回403错误
3. 同一时间，同一用户的小程序的token只有一个，如果用户在其它地方(模拟器和手机同时登录的场合),只会有一个token生效，另一个将失效

4. 小程序接口(接口1.*, 3.*)和后台管理的相关接口(接口4.*-**)不可混用。因认证方式不同，接口不可混用

### 1. 微信相关
#### 1.1 获取token
接口调用说明：

    请求方式: POST
    请求uri: /api/wxuser/info/submit

请求参数说明

参数名    | 描述
--------  | ---------
code      | 小程序调用wx.login()接口获得的登陆凭证
avatarUrl | 用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。
city      | 用户所在城市
country   | 用户所在国家
province  | 用户所在省份
nickName  | 用户昵称
gender    | 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知


返回说明：

    {
        "errcode": 0,
        "errMsg": "ok",
        "token": "BVR0M0RQ9GKXGrHpn92LPw==", // 获取到的token
        "user_id": 1    // 用户id
        "auth": false   // 是否实名认证
    }
#### 1.2 发送手机验证码
接口调用说明

    请求方法： POST
    请求uri: /api/sms/verify-code

请求参数说明

参数名  | 必须 | 参数说明
------ |  --- | ------
mobile |  是  | 大陆手机号码

返回数据说明

    {
        "success": true,    // 验证码发送成功时为true，失败时为false
        "type": "sms_sent_success",
        "message": "短信验证码发送成功，请注意查收"
    }

#### 1.3 上传图片
接口调用说明

    请求参数： POST
    请求uri: /api/image/upload

请求参数说明

参数名  |  参数类型   |  必须   | 参数描述
------- | ---------- | ------ | ---------
image   |  file   |     是   | 要上传的图片

返回数据说明

    {
        "errcode": 0,
        "errMsg": "ok",
        "url": "http://develop-hello-orange.oss-cn-beijing.aliyuncs.com/1520135675-captchaGenerate.jpg"     // 图片的地址
    }

### 2. 用户相关
#### 2.1 获取用户信息
接口调用说明

    请求方法：GET
    请求uri: /api/wxuser/info/get

请求参数说明

参数名   |   必须    |   类型    |  参数描述
-------- | --------- | --------- | --------
token   |  是     |   string  |   调用接口1.1获取的token值

返回数据说明

    {
        "errcode": 0,
        "errMsg": "ok",
        "avatar": "https:\/\/wx.qlogo.cn\/mmopen\/vi_32\/aOZQ1tic5bKAKFznSWVPn7ot0rlLaXG5bsWibXwaryZdDWKSozTxaY9qP9cFXYKcOTukND35AvNXoZWu89mBPe1g\/0",
        "nickName": "虚幻",
        "name": "杨永榜",   // 真实姓名，如果没有认证，此项为空
        "id": "123456789987654321", // 身份证号，如果没有认证，此项为空
        "school": "天津大学",   // 学校。如果没有认证，此项为空
        "schoolArea": "天津"    //学校所在地，如果没有实名认证，则此项为空
    }

#### 3.2 获取用户收益
接口调用说明

    请求方法：GET
    请求uri: /api/wxuser/income/get

请求参数说明

参数名   |   必须    |   参数描述
------- | --------- | ----------
size   |    否      | 返回的条数，默认为10
page    |    否      | 分布获取中的页码，默认为1
token   |   是      | 调用接口1.1获取的token值

返回说明

    {
        "totalCount": 1,
        "totalPoints": 800,
        "totalMoney": 100,
        "projects": [   // 收入来源项目
            {
                "id": 1,
                "title": "志愿项目标题",
                "intro": "志愿项目介绍志愿项目介绍志愿项目介绍志愿项目介绍志愿项目介绍",
                "image": "http://www.golaravel.com/assets/images/laravel-5.6.png",
                "location": "志愿地点",
                "start": "2018-01-01 09:30:00",
                "end": "2018-01-15 09:30:00",
                "money": 100,
                "points": 300,
                "need": 321,
                "myInfo": {
                    "wxuser_id": 1,
                    "project_id": 1,
                    "status": 4,    // 志愿项目状态: 1审核中，2已通过，3未通过, 4已评价
                    "reason": null, // 若status=3, 返回未通过原因
                    "points": 800,  // status=4时， 返回获得的积分数
                    "money": 100    // status=4时，返回获得的现金数量
                    "updated_at": "2017-01-15 14:33:00" // 更新时间
                }
            }
        ]
    }

### 3. 志愿者项目

#### 3.2 获取项目列表
接口调用说明：

    请求方法：GET
    请求uri：/api/project/get

请求参数说明：

参数名  |  必须  |  参数描述
------- | ----- | --------
page | 否    | 获取的页面，默认为1
size | 否    | 每页项目的条数，默认为10
token | 否   | 调用接口1.1获取的token值

返回说明：

    {
        "data": [   // 获取到的项目的数组
            {
                "id": 1,    // 项目id
                "title": "志愿项目标题",  // 项目标题
                "intro": "志愿项目介绍志愿项目介绍志愿项目介绍志愿项目介绍志愿项目介绍",  // 项目介绍
                "location": "志愿地点",
                "start": "2018-01-01 09:30:00", // 项目开始时间
                "end": "2018-01-15 09:30:00",   // 项目结束时间
                "money": 100,   // 奖励现金
                "points": 300,  // 奖励积分
                "need": 321     //招募人数
                "applyInfo": {  // 如果token有效，并且用户申请了该项目，则会返回此项
                    "status": 4,     // 当前请求用户的项目状态，1审核中，2已通过，3未通过, 4已评价
                    "judge": null,  // 评价信息，1为优秀，2为合格
                    "money": null,  // 获得的现金收益
                    "points": null, // 获得的积分奖励
                    "reason": null,     // 未通过的原因
                }
            }
        ],
        "links": {
            "first": "http://localhost:8000/api/v1/projects?page=1",   
            "last": "http://localhost:8000/api/v1/projects?page=1",
            "prev": null,
            "next": null
        },
        "meta": {
            "current_page": 1,  // 当前页面
            "from": 1,
            "last_page": 1,
            "path": "http://localhost:8000/api/v1/projects",
            "per_page": 10, // 每页的个数
            "to": 1,    // 项目总数
            "total": 1  // 页面总数
        }
    }

#### 3.3 报名志愿者项目
接口调用说明

    请求方法：POST
    请求uri: /api/project/apply

请求参数说明

参数名    |   必须   |  参数描述
--------- | --------| ------
tasks   |  是      | 志愿项目的任务id数组
obey    |   否      | 是否服从分配，默认不服从分配
token   |    是      | 调用接口1.1获取的token
reapply |   申请类型  | 默认为false，表示第一次申请，如果为true表示再次申请

返回说明

    {
        'errcode': 0,
        'errMsg': 'ok',
        'applyId': 1    //报名id
    }

### 4. 后台用户管理
#### 4.1 登录
```
注意: 除接口4.1之外，所有的4.*接口在请求时需要在headers中加入Authorization字段，字段值为调用4.1接口获取的token_type + ' ' + access_token值，token过期会后返回401,需要重新登录。
```
接口调用说明

    请求方法： POST
    请求uri: /api/user/login

请求参数说明

参数名   |  必须  | 参数名描述
-------- | ----- | --------
username |  是  | 用户名
password | 是  |  密码

返回数据说明

    {
        "errcode": 0,
        "errMsg": "ok",
        "token_type": "Bearer",
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImE4ZWFhY2Q4OWIwZjJjM2IxNjI2ZjdhYTllOWFmMzRmMThmYTJiOTUxY2VkYmMxMzZhNjQyNGY4OThjMmJkMTlhOTNmMWE1YjEwMTJhZTIzIn0.eyJhdWQiOiIyIiwianRpIjoiYThlYWFjZDg5YjBmMmMzYjE2MjZmN2FhOWU5YWYzNGYxOGZhMmI5NTFjZWRiYzEzNmE2NDI0Zjg5OGMyYmQxOWE5M2YxYTViMTAxMmFlMjMiLCJpYXQiOjE1MjExNzIxNTMsIm5iZiI6MTUyMTE3MjE1MywiZXhwIjoxNTIxNzc2OTUzLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.eUzPQb08pS4bZ6kOyCB-GBaZU8e3lyIuk6y0rYkWlDB74BTAa8zRtlABFoVYvtMQoxOa1BnzIv13qUesiFvBFccmFi0rJyvY_vzjzQ3sz19iTC0xXCFdmN4DyQ2dR8cBnFpIawL4a28ATIXErU4XywZ_wYvg6OB3Ih5EvY8UTP3ZbzZ-PkCufFrddZW1dkvKgqDD2nEiBFjMtpv-xiCIycgN8B5sI6Y581ClBpR_h7BdZJjduKNUZoKiPObPzbSzXIbc-lGFHv-rGgZQR-8aPa_PI2uF94aDEf6ZvwFgmETZPDGAaoCeGvD_FG9Qf3OKjNdZ_vD1fn8oRPWelDregWot05CG3iVBZaoXdXn_HEpkny38qVzURkVK5J-_vbzK9z6TBF2Dk89H8mjtcFb7JGLNrOrAtRo_egO6zKJut1h-7lYwxnRSC0kL97K2UrFcX-WrAp7ubbf8kxdQgGBL9Ca4wQBuL_RHhMTuXUDQfxW2Z8bX3RrZEHqzgaT7lrMrcWoRbwt4Duu6orWrFeciXbcjntd6TQPTan_RYxTIQZ3skdAiq9v3sl0V00XTQMNr4HTSxvT7FsrVE0Ivza4IubCSAlHVgQF9n0GuON30jpXBPwrOszXd4YEKSB8qSo474uVI8SaQMhvqp0MMHowgdoZOFuTRMqY9ju6V5wvUauY",
        "expires_in": 604800,   // access_token过期时间
        "admin_info": { // 当前登录的后台用户的信息
            "id": 1,
            "name": "admin",
            "created_at": "2018-03-10 13:59:19",
            "updated_at": "2018-03-12 12:57:30",
            "is_admin": true,
            "is_operator": true,
            "is_manager": true
        }
    }

#### 4.2 分页获取后台用户信息
接口调用说明
    
    请求方法： GET
    请求uri: /api/admin/user/all

请求参数说明

参数名  |  必须  | 类型  | 参数描述
------ | ------ | ----- | -------
page   | 否    |   number  | 页数，默认为1
size  |  否     |  number   | 每页的个数，默认为10

返回数据说明

    {
        "data": [
            {
                "id": 1,
                "name": "test",
                "created_at": "2018-03-05 14:03:26",
                "updated_at": "2018-03-05 14:03:26",
                "is_admin": 0,  // 是否是管理员
                "is_operator": 0,   // 是否是运营人员
                "is_manager": 0     // 是否是志愿者负责人
            },
            {
                "id": 2,
                "name": "test01",
                "created_at": "2018-03-07 10:36:40",
                "updated_at": "2018-03-07 10:36:40",
                "is_admin": 0,
                "is_operator": 0,
                "is_manager": 0
            },
            {
                "id": 3,
                "name": "test02",
                "created_at": "2018-03-07 10:45:07",
                "updated_at": "2018-03-07 10:45:07",
                "is_admin": 1,
                "is_operator": 0,
                "is_manager": 0
            }
        ],
        "links": {
            "first": "http://localhost:8000/api/admin/user/all?size=10&page=1",
            "last": "http://localhost:8000/api/admin/user/all?size=10&page=1",
            "prev": null,
            "next": null
        },
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "http://localhost:8000/api/admin/user/all",
            "per_page": 10,
            "to": 3,
            "total": 3
        }
    }

#### 4.3 获取单个后台管理用户信息
接口调用说明

    请求方式： GET
    请求uri: /api/admin/user/info

请求参数说明

参数名   |  必须   |   类型   |  参数描述
----- | -------- | ------ | --------
userId   | 是   |   number | 后台管理用户的id

返回数据说明

    {
        "id": 1,
        "name": "admin",
        "created_at": "2018-03-10 13:59:19",
        "updated_at": "2018-03-10 13:59:19",
        "is_admin": true,   // 是否是管理员
        "is_operator": true,    // 是否是运营人员
        "is_manager": true      // 是否是负责人
    }

#### 4.4 创建后台用户
接口调用说明:

    请求方法： POST
    请求uri: /api/admin/user/create

请求参数说明

参数名 |  必须  | 类型   |  参数说明
----- |    ----- | ----- | -------
username |  是  |  string | 用户名
password | 是  |  string  | 密码
isAdmin  | 是   | boolean  | 是否是管理员
isOperator | 是  | boolean | 是否是运营人员
isManager  | 是  | boolean  | 是否是志愿者负责人
wxuserId   | 否 | number |   如果传递此参数，本接口即为志愿者创建一个后台用户

返回数据说明

    {
        "errcode": 0,
        "errMsg": "ok",
        "userId": 3
    }

#### 4.5 更新后台用户信息
接口调用说明

    请求方式： POST
    请求uri: /api/admin/user/update

请求参数说明

参数名  | 必须   |   类型   |  参数描述
------- | ----- | -------- | -------
userId  | 是   |  number  | 后台用户的id
isAdmin  | 否  |  boolean  | 是否管理员，只有当前登录用户是管理员时有效
isOperator | 否 | boolean  | 是否运营人员，只有当前登录用户是管理员或运营人员时有效
isManager | 否  |  boolean  | 是否负责人，只有当前登录用户是管事员或运营人员时有效
oldPassword | 否  | string  | 原密码，修改密码时使用，管理员可修改所有人的密码，运营人员可修改运营人员和负责人的密码，负责人不可修改密码
newPassword | 否  | string  | 新密码，修改密码时需要传入此字段

返回数据说明

    {
        "id": 1,
        "name": "admin",
        "created_at": "2018-03-10 13:59:19",
        "updated_at": "2018-03-10 13:59:19",
        "is_admin": true,
        "is_operator": true,
        "is_manager": true
    }

如果传入oldPassword并验证原密码失败，返回字段如下 

    {
        "errcode": 140,
        "errMsg": "密码验证失败"
    }

#### 4.6 删除后台用户
接口调用说明

    请求方式：POST
    请求uri: /api/admin/user/del

请求参数说明

参数名   |  必须   |   类型   |  参数描述
----- | -------- | ------ | --------
userId   | 是   |   number | 后台管理用户的id,管理员可删除所有人，运营人员只能删除运营人员和负责人，负责人不可删除用户

正常返回数据说明

    {
        "errcode": 0,
        "errMsg": "ok"
    }

异常返回数据说明

    {
        "errcode": 138,
        "errMsg": "没有权限"
    }


### 5. 志愿者项目
#### 5.1 创建志愿者项目
接口调用说明：

    请求方法：POST
    创建uri: /api/admin/project/create

请求参数说明

    {
        "image": "http://www.golaravel.com/assets/images/laravel-5.6.png",  // 志愿者项目的图片地址
        "title": "志愿项目标题",  // 项目标题
        "intro": "志愿项目介绍志愿项目介绍志愿项目介绍志愿项目介绍志愿项目介绍",  // 项目介绍
        "location": "志愿地点", // 志愿地点
        "start": "2018-01-01 09:30:00", // 开始时间
        "end": "2018-01-15 09:30:00",   // 结束时间
        "money": 100,   // 奖励现金
        "points": 300,  // 奖励积分
        "need": 321,    // 需要招募人数
        "showObey": true,  // 是否增加服从分配
        "tasks": [{     // 志愿项目的任务数组
            "title": "任务1标题",   // 任务标题
            "intro": "任务1描述",   // 任务描述
            "location": "任务1地点",    // 任务地点
            "start": "2018-01-01 09:30:00", // 任务开始时间
            "end": "2018-01-05 09:30:00"    // 任务结束时间
        }]
    }

返回说明：

    {
        "errcode": 0,
        "errMsg": "ok",
        "projectId": 1  // 创建项目时返回本字段，志愿者项目id
    }

#### 5.2 上传图片
接口调用说明

    请求参数： POST
    请求uri: /api/admin/image/upload

请求参数说明

参数名  |  参数类型   |  必须   | 参数描述
------- | ---------- | ------ | ---------
image   |  file   |     是   | 要上传的图片

返回数据说明

    {
        "errcode": 0,
        "errMsg": "ok",
        "url": "http://develop-hello-orange.oss-cn-beijing.aliyuncs.com/1520135675-captchaGenerate.jpg"     // 图片的地址
    }

#### 5.3 分页获取所有项目
接口调用说明

    请求方法： GET
    请求uri: /api/admin/project/all

请求参数说明

参数名   |   必须   |  类型   |  参数描述
------- | ------- | -------- | ---------
page    |  否   |  number  | 页码，默认为1
size    |   否   | number   | 每页的数据条数，默认为10

返回数据说明

    "data": [
        {
            "id": 1,
            "title": "Accusamus.",
            "intro": "Voluptates aut facilis a. Itaque necessitatibus ratione quidem rerum.",
            "image": "https://lorempixel.com/140/200/?88500",
            "location": "成都、北京、天津",
            "start": "1970-03-05 20:52:14",
            "end": "1978-02-04 03:17:11",
            "money": 468,
            "points": 559,
            "need": 406,
            "current": 1,   // 所有申请了本项目的志愿者总数：包括待审核，审核未通过，已通过，已评价的
            "tasks": [
                {
                    "id": 52,
                    "title": "Ipsum.",
                    "intro": "Atque quas qui ipsam eum.",
                    "location": "北京",
                    "start": "2015-10-20 21:30:51",
                    "end": "1984-05-20 04:09:29"
                }
            ],
            "pass": 0,  //通过的人数
            "wait_handle": 0    //等待处理的人数
        },
        ··· ]
        "links": {
            "first": "http://localhost/api/admin/project/all?size=10&page=1",
            "last": "http://localhost/api/admin/project/all?size=10&page=5",
            "prev": null,
            "next": "http://localhost/api/admin/project/all?size=10&page=2"
        },
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 5,
            "path": "http://localhost/api/admin/project/all",
            "per_page": 10,
            "to": 10,
            "total": 50
        }
    }

#### 5.3 删除项目
接口调用说明

    请求方法： POST
    请求uri: /api/admin/project/del

参数说明

参数名   |   必须   |   类型   |  参数描述
-------- | ------- | -------- | --------
projectId | 是     |  number  | 删除项目的id

返回数据说明

    {
        "errcode": 0,
        "errMsg": "ok"
    }

#### 5.4 获取志愿项目职责
接口调用说明

    请求方法： GET
    请求uri: /api/admin/project/tasks

请求参数说明

参数名  |  必须   |   类型   | 参数描述
------- | ------ | -------- | -------
projectId | 是  |   number  | 项目的id

返回数据说明

    [   // 志愿项目的所有职责
        {
            "id": 52,
            "title": "Ipsum.",
            "introduce": "Atque quas qui ipsam eum.",
            "location": "北京",
            "start": "2015-10-20 21:30:51",
            "end": "1984-05-20 04:09:29",
            "project_id": 1
        }
    ]

### 6. 志愿者
#### 6.1 处理志愿者申请
接口调用说明

    请求方法： POST
    请求uri: /api/admin/apply/deal

请求参数说明

参数名     |     必须    |    类型     |  参数描述
---------  |  --------- | ----------- | ----------
wxuserId  |  是    | number |     志愿者id
dealType  |  是     |     number   |  处理的类型，2通过，3未通过，4评价
projectId  |  是    |    number    |  处理的志愿者项目的id
taskId    |   否    |    number    |  dealType为2时必须，表示通过申请的职责
reason   |   否    |   string     |  dealType为3时必须，表示未通过的原因
judge    |   否     |   number     |   评价的类型，1为优秀，2表示合格

返回说明

    {
        "errcode": 0,
        "errMsg": "ok"
    }

#### 6.2 分页获取项目所有志愿者
接口调用说明

    请求方法： GET
    请求uri: /api/admin/project/wxuser/all

请求参数说明

参数名  |  必须   |   类型   | 参数描述
------- | ------ | -------- | -------
projectId | 是  |   number  | 项目的id
page    |  否   |   number  | 页数，默认为1
size    | 否   |   number   | 每页返回的数据条数，默认为10
name    |  否   | string    |  筛选名字
sex     |  否   | string    | 筛选性别
id_number | 否  | string    | 筛选身份证号
school    | 否  | string    | 筛选学校
school_area  | 否  | string  | 筛选地区
mobile    | 否   | string   | 筛选手机号
start   | 否   | string (2018-03-10 13:44:37) | 筛选申请时间的开始时间点，和end一起使用，否则无效
end  | 否    | string (2018-03-10 13:44:37) | 筛选申请时间的结束时间点，和start一起使用，否则无效
status | 否  | number  | 筛选申请状态，1为待审核，2为已通过，3为未通过，4为已评价, 5为已通过和已评价
duty   | 否  | number  | 筛选申请职责的id
judge  | 否  | number  | 筛选评价信息，1是优秀，2是合格

#### 6.3 获取项目的单个志愿者信息
接口调用说明

    请求方法： GET
    请求uri: /api/admin/project/wxuser/info

请求参数说明

参数名  |  必须   |   类型   | 参数描述
------- | ------ | -------- | -------
projectId | 是  |   number  | 项目的id
wxuserId  | 是  | number |  志愿者的id

返回数据说明

    {
        "data": {
            "applyInfo": {
                "tasks": [
                    {
                        "id": 26,
                        "title": "Voluptate.",
                        "introduce": "Totam ut ipsam hic rerum.",
                        "location": "北京",
                        "start": "1982-05-28 15:20:31",
                        "end": "2012-09-06 23:30:27",
                        "project_id": 2
                    },
                    {
                        "id": 35,
                        "title": "Est qui.",
                        "introduce": "Rem et tempora animi quidem.",
                        "location": "北京",
                        "start": "2017-12-27 00:46:48",
                        "end": "1983-01-17 09:20:10",
                        "project_id": 2
                    }
                ],
                "obey": 0,
                "applyTime": "2018-03-10 13:44:56",
                "status": 1
            },
            "id": 1,
            "real_info": {
                "id": 3,
                "photo": "http://develop-hello-orange.oss-cn-beijing.aliyuncs.com/1520689320-wx8d12bae22304e38f.o6zAJsyIXWyBIeEFbCFpE2vrM8nk.10bd4274173c6a4d36158fdfc1e19053.jpg",
                "name": "杨永榜",
                "id_number": "123456789",
                "sex": "男",
                "school": "天津大学",
                "school_area": "天津",
                "has_agent": 1,
                "has_volunteer": 0,
                "experience": null,
                "mobile": "15902273852",
                "wxuser_id": 1,
                "status": 1,
                "created_at": "2018-03-10 13:43:14",
                "updated_at": "2018-03-10 13:43:14"
            },
            "avatar": "https://wx.qlogo.cn/mmopen/vi_32/aOZQ1tic5bKAKFznSWVPn7ot0rlLaXG5bsWibXwaryZdDWKSozTxaY9qP9cFXYKcOTukND35AvNXoZWu89mBPe1g/0",
            "nick_name": "虚幻"
        }
    }


返回数据说明

    {
        "data": [
            {
                "applyInfo": {  // 申请本项目的所有列表
                    "tasks": [
                        {
                            "id": 52,
                            "title": "Ipsum.",
                            "introduce": "Atque quas qui ipsam eum.",
                            "location": "北京",
                            "start": "2015-10-20 21:30:51",
                            "end": "1984-05-20 04:09:29",
                            "project_id": 1
                        }
                    ],
                    "obey": 0,  // 是否服从分配
                    "applyTime": "2018-03-10 13:44:38", // 申请时间
                    "status": 4, // 状态，1为待审核，2为已通过，3为未通过，4为已评价
                    "judge": "1"    // 评价信息，1为优秀，2为合格
                },
                "id": 1,
                "real_info": {
                    "id": 3,
                    "photo": "http://develop-hello-orange.oss-cn-beijing.aliyuncs.com/1520689320-wx8d12bae22304e38f.o6zAJsyIXWyBIeEFbCFpE2vrM8nk.10bd4274173c6a4d36158fdfc1e19053.jpg",
                    "name": "杨永榜",
                    "id_number": "123456789",
                    "sex": "男",
                    "school": "天津大学",
                    "school_area": "天津",
                    "has_agent": 1,
                    "has_volunteer": 0,
                    "experience": null,
                    "mobile": "15902273852",
                    "wxuser_id": 1,
                    "status": 1,
                    "created_at": "2018-03-10 13:43:14",
                    "updated_at": "2018-03-10 13:43:14"
                },
                "avatar": "https://wx.qlogo.cn/mmopen/vi_32/aOZQ1tic5bKAKFznSWVPn7ot0rlLaXG5bsWibXwaryZdDWKSozTxaY9qP9cFXYKcOTukND35AvNXoZWu89mBPe1g/0",
                "nick_name": "虚幻"
            }
        ],
        "links": {
            "first": "http://localhost:8000/api/admin/project/user?projectId=4&size=10&page=1",
            "last": "http://localhost:8000/api/admin/project/user?projectId=4&size=10&page=1",
            "prev": null,
            "next": null
        },
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "http://localhost:8000/api/admin/project/user",
            "per_page": 15,
            "to": 1,
            "total": 1
        }
    }


#### 6.4 获取单个志愿者信息
接口调用说明
    
    请求方式： GET
    请求uri: /api/admin/wxuser/info

请求参数说明

参数名  | 必须 | 类型  | 参数描述
------- | --- | ----- | ------
wxuserId | 是 | number | 志愿者id

返回数据说明

    {
        "errcode": 0,
        "errMsg": "ok",
        "id": 1,
        "openid": "oQTVV49afvyvrPzPv-ROeoScKu4w",
        "wx_session": "0W2Xh2S8hMowRSlw5MtOcA==",
        "token": "AFaiUbrmq1BPNNDoOmnWXineUVqjv0vz8s8NRodY",
        "avatar": "https://wx.qlogo.cn/mmopen/vi_32/aOZQ1tic5bKAKFznSWVPn7ot0rlLaXG5bsWibXwaryZdDWKSozTxaY9qP9cFXYKcOTukND35AvNXoZWu89mBPe1g/0",
        "nick_name": "虚幻",
        "gender": "2",
        "city": null,
        "province": null,
        "country": "Andorra",
        "created_at": "2018-03-10 13:38:06",
        "updated_at": "2018-03-10 13:38:06",
        "admin_info": {     // 如果当前志愿者是后台用户，会返回后台用户信息，如果不是，返回null
            "id": 8,
            "name": "test5",
            "is_admin": true,
            "is_operator": false,
            "is_manager": false
        },
        "real_info": {
            "id": 1,
            "photo": "http://develop-hello-orange.oss-cn-beijing.aliyuncs.com/1520689320-wx8d12bae22304e38f.o6zAJsyIXWyBIeEFbCFpE2vrM8nk.10bd4274173c6a4d36158fdfc1e19053.jpg",
            "name": "杨永榜",
            "id_number": "123456789",
            "sex": "男",
            "school": "天津大学",
            "school_area": "天津",
            "has_agent": 1,
            "has_volunteer": 0,
            "experience": null,
            "mobile": "15902273852",
            "wxuser_id": 1,
            "status": 1,
            "created_at": "2018-03-10 13:43:14",
            "updated_at": "2018-03-10 13:43:14"
        },
        "working_projects": [  // 当前的志愿者任务 
            {
                "id": 3,
                "title": "Nisi aut.",
                "introduce": "Vel est voluptas perspiciatis. Iure ut quo magni veniam est. Et eveniet nesciunt ut.",
                "location": "成都、北京、天津",
                "start": "1975-02-21 20:27:21",
                "end": "1989-06-16 19:56:57",
                "points": 758,
                "money": 469,
                "need": 459,
                "image": "https://lorempixel.com/140/200/?55233",
                "show_obey": 0,
                "duty_limit": 0,
                "created_at": "2018-03-10 13:41:20",
                "updated_at": "2018-03-10 13:41:20",
                "publisher_id": 5,
                "apply_info": {
                    "wxuser_id": 1,
                    "project_id": 3,
                    "task_id": 7
                },
                "tasks": [  // 当前项目的职责列表
                    {
                        "id": 7,
                        "title": "Aperiam.",
                        "introduce": "Illo cum dolor et commodi.",
                        "location": "北京",
                        "start": "2015-08-28 00:10:31",
                        "end": "1972-12-06 11:21:18",
                        "project_id": 3
                    },
                    ...
                ]
            }
        ]
    }

#### 6.5 分页获取全部志愿者
接口调用说明

    请求方式： GET
    请求uri: /api/admin/wxuser/all

请求参数

参数名   |   必须    |   类型   | 参数描述
-------- | -------- | -------- | --------
page    | 否   |   number |   页码，默认为1
size    | 否   |  number   |  每页的数目，默认为10
name    |  否   | string    |  筛选名字
sex     |  否   | string    | 筛选性别
id_number | 否  | string    | 筛选身份证号
school    | 否  | string    | 筛选学校
school_area  | 否  | string  | 筛选地区
mobile    | 否   | string   | 筛选手机号
role    | 否   | string   | 筛选权限，2为管理员，3为运营人员，4为负责人
orderType | 否  | string   | 排序类型，asc为正序(从小到大),desc为倒序（从大到小)
orderValue | 否 | string  | 排序列，可选money, history_money, points, history_points

返回数据说明

    {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "avatar": "https://wx.qlogo.cn/mmopen/vi_32/aOZQ1tic5bKAKFznSWVPn7ot0rlLaXG5bsWibXwaryZdDWKSozTxaY9qP9cFXYKcOTukND35AvNXoZWu89mBPe1g/0",
                "nick_name": "虚幻",
                "gender": "2",
                "city": null,
                "province": null,
                "country": "Andorra",
                "created_at": "2018-03-10 13:38:06",
                "updated_at": "2018-03-10 13:38:06",
                "money": 300,
                "points": 250,
                "history_money": 300,
                "history_points": 250,
                "completed_count": 1,
                "admin_info": { // 如果是后台用户，返回后台的用户信息，如果不是后台用户，返回null
                    "id": 8,
                    "name": "test5",
                    "is_admin": true,
                    "is_operator": false,
                    "is_manager": false
                },
                "real_info": {
                    "id": 1,
                    "photo": "http://develop-hello-orange.oss-cn-beijing.aliyuncs.com/1520689320-wx8d12bae22304e38f.o6zAJsyIXWyBIeEFbCFpE2vrM8nk.10bd4274173c6a4d36158fdfc1e19053.jpg",
                    "name": "杨永榜",
                    "id_number": "123456789",
                    "sex": "男",
                    "school": "天津大学",
                    "school_area": "天津",
                    "has_agent": 1,
                    "has_volunteer": 0,
                    "experience": null,
                    "mobile": "15902273852",
                    "wxuser_id": 1,
                    "status": 1,
                    "created_at": "2018-03-10 13:43:14",
                    "updated_at": "2018-03-10 13:43:14"
                }
            }
        ],
        "first_page_url": "http://localhost/api/admin/wxuser/all?size=10&page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://localhost/api/admin/wxuser/all?size=10&page=1",
        "next_page_url": null,
        "path": "http://localhost/api/admin/wxuser/all",
        "per_page": 10,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    }



### 7. 职责模板
#### 7.1 创建职责模板
接口调用说明

    请求方式：POST
    请求uri: /api/admin/templet/create

请求参数说明

参数名   |   必须   |  类型  | 参数描述
------- | ------- | ------- | -------
title  |   是    | string   | 职责标题
introduce | 否  | string  | 职责描述
location  | 否   | string  | 志愿地址
start   | 否   | string  | 开始时间，	2019-3-10 14:00
end   | 否  | string | 结束时间，2019-3-10 14:00

正常返回数据说明

    {
        "title": "职责测试",
        "introduce": "职责详情",
        "location": "北京",
        "start": "2019-3-10 14:00",
        "end": "2019-3-10 14:00",
        "id": 1
    }

异常返回数据如下

    {
        "errcode": 141,
        "errMsg": "模板数量已达上限"    // 目前数量上限为14
    }

#### 7.2 删除职责模板
接口调用说明

    请求方式：POST
    请求uri: /api/admin/templet/update

请求参数说明

参数名   |   必须   |  类型  | 参数描述
------- | ------- | ------- | -------
templetId  |  是    |  number | 要更新的职责模板的id
title  |   是    | string   | 职责标题，不可为空
introduce | 否  | string  | 职责描述
location  | 否   | string  | 志愿地址
start   | 否   | string  | 开始时间，	2019-3-10 14:00
end   | 否  | string | 结束时间，2019-3-10 14:00

返回数据说明

    {   // 更新后的模板详情
        "title": "职责测试",
        "introduce": "职责详情",
        "location": "北京",
        "start": "2019-3-10 14:00",
        "end": "2019-3-10 14:00",
        "id": 1
    }

#### 7.3 获取所有模板
接口调用说明

    请求方式：GET
    请求uri: /api/admin/templet/all

请求参数说明

    无

返回数据说明

    [   // 所有的职责模板
        {
            "id": 1,
            "title": "职责测试",
            "introduce": "职责详情",
            "location": "北京",
            "start": "2019-03-10 14:00:00",
            "end": "2019-03-10 14:00:00"
        }
    ]

#### 7.4 获取单个模板的信息
接口调用说明

    请求方式：GET
    请求uri: /api/admin/templet/info

请求参数说明

参数名   |   必须   |  类型  | 参数描述
------- | ------- | ------- | -------
templetId  |  是    |  number | 职责模板的id

返回数据说明

    {
        "id": 1,
        "title": "职责测试",
        "introduce": "职责详情",
        "location": "北京",
        "start": "2019-03-10 14:00:00",
        "end": "2019-03-10 14:00:00"
    }

#### 7.5 删除模板
接口调用说明

    请求方式： POST
    请求uri: /api/admin/templet/delete

请求参数说明

参数名   |   必须   |  类型  | 参数描述
------- | ------- | ------- | -------
templetId  |  是    |  number | 要删除的职责模板的id

返回数据说明

    {
        "errcode": 0,
        "errMsg": "ok"
    }


### 8 拒绝原因模板
#### 8.1 新增拒绝原因模板
接口调用说明

    请求方式： POST
    请求uri: /api/admin/reason/create

请求参数说明

参数名   |  必须   |   类型   |   参数名描述
------- | ------- | -------- | ----------
content | 是 |  string   | 模板内容

返回数据示例

    {
        "content": "testtesttest",
        "id": 1
    }

#### 8.2 获取拒绝模板
接口调用说明

    请求方式： GET
    请求uri: /api/admin/reason/all

请求参数说明

    无

返回数据示例

    [   // 所有的模板数组
        {
            "id": 1,
            "content": "testtesttest"
        }
    ]

#### 8.3 删除拒绝模板
接口调用说明

    请求方式： POST
    请求uri: /api/admin/reason/delete

请求参数说明

参数名   |  必须   |   类型   |   参数名描述
------- | ------- | -------- | ----------
reasonId | 是 |  number   | 模板id

返回示例

    {
        "errcode": 0,
        "errMsg": "ok"
    }
    

### 9 导出接口
#### 9.1 筛选导出全部志愿者
接口调用说明

    请求方式： GET
    请求uri: /api/admin/export/wxuser/all

请求参数说明

参数名  |  必须   |   参数类型   |  参数描述
------- | ------- | ---------- | --------
name    |  否   | string    |  筛选名字
sex     |  否   | string    | 筛选性别
id_number | 否  | string    | 筛选身份证号
school    | 否  | string    | 筛选学校
school_area  | 否  | string  | 筛选地区
mobile    | 否   | string   | 筛选手机号
role    | 否   | string   | 筛选权限，1为普通用户，2为管理员，3为运营人员，4为负责人

#### 9.2 筛选导出指定项目的志愿者
接口调用说明

    请求方式： GET
    请求uri: /api/admin/export/project/wxuser

请求参数说明

参数名  |  必须   |   参数类型   |  参数描述
------- | ------- | ---------- | --------
projectId | 是  | number | 项目id
name    |  否   | string    |  筛选名字
sex     |  否   | string    | 筛选性别
id_number | 否  | string    | 筛选身份证号
school    | 否  | string    | 筛选学校
school_area  | 否  | string  | 筛选地区
mobile    | 否   | string   | 筛选手机号
role    | 否   | string   | 筛选权限，1为普通用户，2为管理员，3为运营人员，4为负责人
start   | 否   | string (2018-03-10 13:44:37) | 筛选申请时间的开始时间点，和end一起使用，否则无效
end  | 否    | string (2018-03-10 13:44:37) | 筛选申请时间的结束时间点，和start一起使用，否则无效
status | 否  | number  | 筛选申请状态，1为待审核，2为已通过，3为未通过，4为已评价, 5为已通过和已评价
duty   | 否  | number  | 筛选申请职责的id
judge  | 否  | number  | 筛选评价信息，1是优秀，2是合格

#### 9.3 筛选导出志愿者收益
接口调用说明

    请求方式： GET
    请求uri: /api/admin/export/wxuser/income

请求参数说明

参数名  |  必须   |   参数类型   |  参数描述
------- | ------- | ---------- | --------
type    | 否    |  string  |  收益的类型，默认为'money'现金收益，可选'points'积分收益
name    |  否   | string    |  筛选名字
sex     |  否   | string    | 筛选性别
id_number | 否  | string    | 筛选身份证号
school    | 否  | string    | 筛选学校
school_area  | 否  | string  | 筛选地区
mobile    | 否   | string   | 筛选手机号
role    | 否   | string   | 筛选权限，1为普通用户，2为管理员，3为运营人员，4为负责人