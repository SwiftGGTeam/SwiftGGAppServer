#SwiftGG-App-接口规范
------
V0.1：by CMB，2015-12-15

v0.2：by shanks，2015-12-20

* 统一地址,使用mod和act作为后台接口的分发处理依据
* 统一请求和返回的数据格式
* 统一使用 POST请求

###用户第三方登录（模块：登录）
使用场景：用户登录

方式：POST

地址：http://xxx.com/json.php

请求参数：

```
{
	"mod" : "user",
	"act" : "loginFromThird",
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


###用户注册（模块：注册）
使用场景：用户注册

方式：POST

地址：http://xxx.com/json.php

请求参数：

```
{
	"mod" : "user",
	"act" : "register",
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


###用户密码登录（模块：登录）
使用场景：用户登录

方式：POST

地址：http://xxx.com/json.php

请求参数：

```
{
	"mod" : "user",
	"act" : "login",
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

###获取用户信息
使用场景：获取用户详细信息

方式：POST

地址：http://xxx.com/json.php

请求参数：

```
{
	"mod" : "user",
	"act" : "getInfo",
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
		"socre"     : 用户积分,
		"signatre"  : 个性签名,
		"sex"       : 性别,
		"weibo"     : 绑定的微博号,
		"wechat"    : 绑定的微信号,
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

---

###获取分类列表（模块：分类）
使用场景：获取分类列表

方式：POST

地址：http://xxx.com/json.php

请求参数：

```
{
	"mod" : "user",
	"act" : "getCategoryList",
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

###获取分类的文章（模块：首页 | 分类）
使用场景：点击对应的分类显示文章列表

方式：POST

地址：http://xxx.com/json.php

请求参数：

```
{
	"mod" : "user",
	"act" : "getArticlesByCategory",
	"categoryId" : 分类id(如果不输入就按时间输出文章列表,可选)
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
			"starsNumber"    : 文章点赞数,
			"commentsNumber" : 评论数
		},
		{
			"id"             : 文章id,
			"coverUrl"       : 封面图片URL,
			"authorImageUrl" : 文章作者的头像Url,
			"submitData"     : 文章提交时间,
			"title"          : 文章标题, 
			"articleUrl"     : 文章Url,
			"translator"     : 翻译者名称,
			"starsNumber"    : 文章点赞数,
			"commentsNumber" : 评论数
		},
		...
	]
}
```

---

###获取文章信息（模块：首页 | 分类）
使用场景：获取用户详细信息

方式：POST

地址：http://xxx.com/json.php

请求参数：

```
{
	"mod" : "user",
	"act" : "getArticlesByCategory",
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
		"tag"            : 标签,JSON格式,
		"coverUrl"       : 封面图片URL,
		"contentUrl"     : 内容URL,
		"translator"     : 翻译者名称,
		"proofreader"    : 校对者,
		"finalization"   : 定稿者,
		"author"         : 文章作者,
		"authorImageUrl" : 文章作者的头像URL,
		"originalDate"   : 原文发布日期,
		"originalUrl"    : 原文链接,
		"clickedNumber"  : 点击数,
		"submitDate"     : 文章提交时间,
		"starsNumber"    : 文章点赞数,
		"commentsNumber" : 评论数",
		"comments" : [
			{ "name" : 评论者名称, "imageUrl" : 评论者头像URL, "dateTime" : 评论时间 },
			{ "name" : 评论者名称, "imageUrl" : 评论者头像URL, "dateTime" : 评论时间 },
			...
		]
	}
}
```

