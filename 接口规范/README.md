## SwiftGG-App-接口规范

### 版本历史

```
V0.8：by CMB，2016-04-15

* 新增 getAppInfo 接口

V0.7：by CMB，2016-04-13

* 修改为 restful，获取文章详细信息接口打开

V0.6：by CMB，2016-01-28

* 修改了 User 模块 getInfo 接口部分不正确信息（score，github）

V0.5：by CMB，2016-01-27

* 填写了 domain 地址
* 废除 获取文章详细信息 接口

V0.4：by CMB，2016-01-24

* 增加用户注册接口，修改了第三方注册接口

v0.3：by riven,shanks，2015-12-29

* URL统一用domain/apiVersion/moduleName/methodName构成
* 当前的apiVersion为v1

v0.2：by shanks，2015-12-20

* 统一请求和返回的数据格式
* 统一使用 POST请求

V0.1：by CMB，2015-12-15

```

v0.9: 待定

### ModuleName:app

#### getAppInfo（完成）

使用场景：获取 app 参数

地址：`GET` http://123.57.250.194/v1/app/info

请求参数：

```
{

}
```

注册成功响应参数：

```
{
	"ret"  : 0,
	"data" : {
		"appVersion"        : app 版本号,
		"articleVersion"    : 文章版本号,
		"articleSum"        : 文章总数,
		"categoriesVersion" : 分类列表版本号,
		"message"           : 信息
	}
	"errMsg" : ""
}
```

### ModuleName: user

#### userLogin（完成）

使用场景：用户密码登录

地址：`POST` http://123.57.250.194/v1/user/userLogin

请求参数：

```
{
	"userName" : 用户名,
	"password" : 加密后的密码
}
```

响应参数：

```
{
	"ret"  : 0,
	"data" : {
		"userId": 1,
		用户其他信息，待定
	}
	"errMsg" : ""
}
```

错误情况：

```
{
	"ret"  : 错误码,
	"errMsg" : 错误信息
}
```


#### otherLogin（未完成）

使用场景：用户第三方登录

地址：`POST` http://123.57.250.194/v1/user/otherLogin

请求参数：

```
{
	"keyseri" : 第三方唯一标识(必填),
	"type"    : 第三方类型("qq"|"github"|"weibo",必填)
}
```

响应参数：

```
{
	"ret"  : 0,
	"data" : {
		"userId": 1,
		用户其他信息，待定
	}
	"errMsg" : ""
}
```

错误情况：

```
{
	"ret"  : 错误码,
	"errMsg" : 错误信息
}
```

#### userRegister（完成）

使用场景：用户注册

地址：`POST` http://123.57.250.194/v1/user/userRegister

请求参数：

```
{
	"userName" : 用户名,
	"password" : 加密后的密码
}
```

注册成功响应参数：

```
{
	"ret"  : 0,
	"data" : {
		"userId": 1,
		用户其他信息，待定
	}
	"errMsg" : ""
}
```

错误情况：

```
{
	"ret"  : 错误码,
	"errMsg" : 错误信息
}
```


#### userOtherRegister（未完成）

使用场景：用户第三方注册

地址：`POST` http://123.57.250.194/v1/user/userOtherRegister

请求参数：

```
{
	"keyseri"   : 第三方唯一标识(必填)
	"type"      : 第三方类型("qq"|"github"|"weibo",必填)
	"nickname"  : 昵称(必填),
	"signatre"  : 个性签名(可选),
	"sex"       : 性别0为男1为女("0"|"1",可选)
}
```

注册成功响应参数：

```
{
	"ret"  : 0,
	"data" : {
		"userId": 1,
		用户其他信息，待定
	}
	"errMsg" : ""
}
```

错误情况：

```
{
	"ret"  : 错误码,
	"errMsg" : 错误信息
}
```

#### getInfo（完成部分，已读文章和收集文章相关项未完成）

使用场景：获取用户详细信息

地址：`GET` http://123.57.250.194/v1/user/info

请求参数：

```
{
	"uid" : 用户id(必填)
}
```

响应参数：

```
{
	"ret"  : 0,
	"data" : {
		"uid"       : 用户id,
		"nickname"  : 用户名,
		"imageUrl"  : 头像,
		"score"     : 用户积分,
		"signature"  : 个性签名,
		"sex"       : 性别,
		"weibo"     : 绑定的微博号,
		"gitub"     : 绑定的github,
		"qq"        : 绑定的QQ号,
		"github"    : 绑定的github号,
		"level"     : 用户级别,
		"readArticlesNumber"    : 已读的文章数,
		"readWordsNumber"       : 已读的字数,
		"collectArticlesNumber" : 收藏的文章数,
		"restArticlesNumber"    : 未读的文章数,
		"sort"      : 排名（单位百分比）
		"reading" : [
			{ "id" : 文章Id, "title" : 标题, "articleUrl" : 文章URL },
			{ "id" : 文章Id, "title" : 标题, "articleUrl" : 文章URL },
			...
		],
		"collection" : [
			{"id" : 文章Id, "title" : 标题, "articleUrl" : 文章URL },
			{"id" : 文章Id, "title" : 标题, "articleUrl" : 文章URL },
			...
		]
	}
	"errMsg" : ""
}
```


### ModuleName: article


#### getCategoryList（已完成）

使用场景：获取分类列表

地址：`GET` http://123.57.250.194/v1/article/categoryList

请求参数：

```
{

}
```

响应参数：

```
{
	"ret" : 0,
	"data : [
		{ "id" : 分类Id, "name" : 分类名称, "coverUrl" : 封面图片URL, "sum" : 对应的文章总数 },
		{ "id" : 分类Id, "name" : 分类名称, "coverUrl" : 封面图片URL, "sum" : 对应的文章总数 },
		...
	]
}
```

---

#### getArticlesByCategory（已完成）

使用场景：点击对应的分类显示文章列表


地址：`GET` http://123.57.250.194/v1/article

请求参数：

```
{
	"categoryId" : 分类id(如果不输入就按时间输出文章列表,可选),
	"pageIndex"  : 当前页(从1开始,可选),
	"pageSize"   : 一页显示的数量(从1开始,可选)
}
```

响应参数：

```
{
	"ret"  : 0,
	"data" : [
		{
			"id"             : 文章id,
			"coverUrl"       : 封面图片URL,
			"authorImageUrl" : 文章作者的头像Url,
			"submitData"     : 文章提交时间,
			"title"          : 文章标题,
			"articleUrl"     : 文章Url,
			"translator"     : 翻译者名称,
			"description"    : 文章描述,
			"starsNumber"    : 文章点赞数,
			"commentsNumber" : 评论数,
			"updateDate"     : 更新时间
		},
		{
			"id"             : 文章id,
			"coverUrl"       : 封面图片URL,
			"authorImageUrl" : 文章作者的头像Url,
			"submitData"     : 文章提交时间,
			"title"          : 文章标题,
			"articleUrl"     : 文章Url,
			"translator"     : 翻译者名称,
			"description"    : 文章描述,
			"starsNumber"    : 文章点赞数,
			"commentsNumber" : 评论数,
			"updateDate"     : 更新时间
		},
		...
	]
}
```

---

#### getDetail（未完成）

使用场景：获取文章详细信息

地址：`GET` http://123.57.250.194/v1/article/detail

请求参数：

```
{
	"articleId" : 文章id(必填)
}
```

响应参数：

```
{
	"ret"  : 0,
	"data" : {
		"typeId"         : 分类ID,
		"typeName"       : 分类名称,
		"tags"           : 标签,JSON格式,
		"coverUrl"       : 封面图片URL,
		"contentUrl"     : 内容URL,
		"translator"     : 翻译者名称,
		"proofreader"    : 校对者,
		"finalization"   : 定稿者,
		"author"         : 文章作者,
		"authorImageUrl" : 文章作者的头像URL,
		"originalDate"   : 原文发布日期,
		"originalUrl"    : 原文链接,
		"description"    : 文章描述,
		"clickedNumber"  : 点击数,
		"submitDate"     : 文章提交时间,
		"starsNumber"    : 文章点赞数,
		"commentsNumber" : 评论数,
		"content"        : 内容,
		"comments" : [
			{ "name" : 评论者名称, "imageUrl" : 评论者头像URL, "dateTime" : 评论时间 },
			{ "name" : 评论者名称, "imageUrl" : 评论者头像URL, "dateTime" : 评论时间 },
			...
		]
	}
}
```
