/*管理员*/
CREATE TABLE IF NOT EXISTS `sg_admin`(
	`id`              smallint(6)  NOT NULL AUTO_INCREMENT COMMENT '主键ID',
	`account`         varchar(32)  NOT NULL COMMENT '账号',
	`password`        varchar(32)  NOT NULL COMMENT '密码',
	`salt`            varchar(6)   NOT NULL COMMENT '加盐',
	`nickname`        varchar(60)  NOT NULL COMMENT '昵称',
	`ip`              varchar(15)  NOT NULL COMMENT 'ip地址',
	`last_login_time` int(11)      NOT NULL COMMENT '最后登录时间',
  	`created_time`    int(11)      NOT NULL COMMENT '创建时间',
  	`updated_time`    int(11)      NOT NULL COMMENT '更新时间',
  	PRIMARY KEY (`id`),
  	UNIQUE KEY `account` (`account`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='管理员';

/*文章分类*/
CREATE TABLE IF NOT EXISTS `sg_article_type`(
	`id`            int(11)      NOT NULL AUTO_INCREMENT COMMENT '主键ID',
	`name`          varchar(30)  NOT NULL COMMENT '分类名称',
  	`created_time`  int(11)      NOT NULL COMMENT '创建时间',
  	`updated_time`  int(11)      NOT NULL COMMENT '更新时间',
  	PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文章分类';

/*文章*/
CREATE TABLE IF NOT EXISTS `sg_article`(
	`id`             int(11)      NOT NULL AUTO_INCREMENT COMMENT '主键ID',
	`type_id`        int(11)      NOT NULL COMMENT '分类ID,外键',
	`title`          varchar(60)  NOT NULL COMMENT '题目',
	`cover_url`      varchar(100) NULL     COMMENT '封面图片URL',
	`content`        text         NOT NULL COMMENT '内容',
	`translator`     varchar(60)  NULL     COMMENT '翻译者',
	`proofreader`    varchar(60)  NULL     COMMENT '校对者',
	`finalization`   varchar(60)  NULL     COMMENT '定稿者',
	`author`         varchar(60)  NULL     COMMENT '文章作者',
	`original_date`  varchar(15)  NULL     COMMENT '原文日期',
	`original_url`   varchar(100) NULL     COMMENT '原文链接',
	`clicked_number` int(11)      DEFAULT '0' COMMENT '点击数',
	`star_number`    int(11)      DEFAULT '0' COMMENT '点赞数',
  	`admin_id`       smallint(6)  NOT NULL COMMENT '操作者ID,外键',
  	`created_time`   int(11)      NOT NULL COMMENT '创建时间',
  	`updated_time`   int(11)      NOT NULL COMMENT '更新时间',
  	PRIMARY KEY (`id`),
  	FOREIGN KEY (`type_id`)  REFERENCES `sg_article_type` (`id`),
  	FOREIGN KEY (`admin_id`) REFERENCES `sg_admin` (`id`),
  	KEY `title`   (`title`),
  	KEY `author`  (`author`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文章';

/*文章标签*/
CREATE TABLE IF NOT EXISTS `sg_tag`(
	`id`            int(11)      NOT NULL AUTO_INCREMENT COMMENT '主键ID',
	`name`          varchar(30)  NOT NULL COMMENT '标签名称',
  	`created_time`  int(11)      NOT NULL COMMENT '创建时间',
  	`updated_time`  int(11)      NOT NULL COMMENT '更新时间',
  	PRIMARY KEY (`id`),
  	KEY `name`  (`name`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文章标签';

/*文章和标签关系*/
CREATE TABLE IF NOT EXISTS `sg_article_tag`(
	`article_id` int(11)      NOT NULL COMMENT '文章ID,外键',
	`tag_id`     int(11)      NOT NULL COMMENT '标签ID,外键',
	FOREIGN KEY (`article_id`) REFERENCES `sg_article` (`id`),
  	FOREIGN KEY (`tag_id`)     REFERENCES `sg_tag` (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文章和标签关系';

/*用户*/
CREATE TABLE IF NOT EXISTS `sg_user`(
	`id`              int(11)      NOT NULL AUTO_INCREMENT COMMENT '主键ID',
	`account`         varchar(32)  NOT NULL COMMENT '账号',
	`password`        varchar(32)  NOT NULL COMMENT '密码',
	`salt`            varchar(6)   NOT NULL COMMENT '加盐',
	`nickname`        varchar(60)  NOT NULL COMMENT '昵称',
	`ip`              varchar(15)  NOT NULL COMMENT 'ip地址',
	`last_login_time` int(11)      NOT NULL COMMENT '最后登录时间',
  	`created_time`    int(11)      NOT NULL COMMENT '创建时间',
  	`updated_time`    int(11)      NOT NULL COMMENT '更新时间',
  	PRIMARY KEY (`id`),
  	UNIQUE KEY `account` (`account`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户';

/*用户第三方登录*/
CREATE TABLE IF NOT EXISTS `sg_user_the_third`(
	`user_id`    int(11)      NOT NULL COMMENT '用户ID,外键',
	`type`       varchar(10)  NOT NULL COMMENT '类型名称',
	`keyseri`    varchar(100) NOT NULL COMMENT '唯一标识',
	FOREIGN KEY (`user_id`) REFERENCES `sg_user` (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户第三方登录';

/*用户详情*/
CREATE TABLE IF NOT EXISTS `sg_user_info`(
	`user_id`       int(11)      NOT NULL COMMENT '用户ID,外键',
	`image_url`     varchar(100) NOT NULL COMMENT '头像URL',
	`socre`         int(11)      NOT NULL COMMENT '积分',
	`signature`     varchar(60)  NULL     COMMENT '个性签名',
	`sex`           tinyint(1)   NULL     COMMENT '性别',
	`weibo`         varchar(60)  NULL     COMMENT '微博',
	`wechat`        varchar(60)  NULL     COMMENT '微信',
	`qq`            varchar(60)  NULL     COMMENT 'QQ',
	FOREIGN KEY (`user_id`) REFERENCES `sg_user` (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户详情';

/*等级说明*/
CREATE TABLE IF NOT EXISTS `sg_level`(
	`id`              int(11)      NOT NULL AUTO_INCREMENT COMMENT '主键ID',
	`name`            varchar(30)  NOT NULL COMMENT '名称',
	`socre`           int(11)      NOT NULL COMMENT '需要达到的积分',
	PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='等级说明';

/*用户阅读记录*/
CREATE TABLE IF NOT EXISTS `sg_read`(
	`user_id`       int(11)      NOT NULL COMMENT '用户ID,外键',
	`article_id`    int(11)      NOT NULL COMMENT '文章ID,外键',
	`datetime`      int(11)      NOT NULL COMMENT '阅读的时间',
	`progress`      int(11)      NOT NULL COMMENT '阅读的进度',
	`is_finished`   tinyint(1)   unsigned DEFAULT '0' COMMENT '是否完成阅读',
	FOREIGN KEY (`user_id`)    REFERENCES `sg_user`    (`id`),
	FOREIGN KEY (`article_id`) REFERENCES `sg_article` (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户阅读记录';

/*用户收藏记录*/
CREATE TABLE IF NOT EXISTS `sg_collection`(
	`user_id`       int(11)      NOT NULL COMMENT '用户ID,外键',
	`article_id`    int(11)      NOT NULL COMMENT '文章ID,外键',
	`datetime`      int(11)      NOT NULL COMMENT '收藏的时间',
	FOREIGN KEY (`user_id`)    REFERENCES `sg_user`    (`id`),
	FOREIGN KEY (`article_id`) REFERENCES `sg_article` (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户收藏记录';

/*评论*/
CREATE TABLE IF NOT EXISTS `sg_comment`(
	`id`              int(11)      NOT NULL AUTO_INCREMENT COMMENT '主键ID',
	`user_id`         int(11)      NOT NULL COMMENT '用户ID,外键',
	`article_id`      int(11)      NOT NULL COMMENT '文章ID,外键',
	`created_time`    int(11)      NOT NULL COMMENT '创建时间',
	`updated_time`    int(11)      NOT NULL COMMENT '更新时间',
	PRIMARY KEY (`id`),
	FOREIGN KEY (`user_id`)    REFERENCES `sg_user`    (`id`),
	FOREIGN KEY (`article_id`) REFERENCES `sg_article` (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='评论';

/* RBAC */
/*角色*/
CREATE TABLE IF NOT EXISTS `sg_role` (
  `id`     smallint(6)  NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name`   varchar(20)  NOT NULL     COMMENT '角色名',
  `pid`    smallint(6)  DEFAULT NULL COMMENT '父ID',
  `status` tinyint(1)   unsigned DEFAULT NULL COMMENT '角色状态',
  `remark` varchar(255) DEFAULT NULL COMMENT '说明',
  PRIMARY KEY (`id`),
  KEY `pid`    (`pid`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='角色';

/*管理员对应角色*/
CREATE TABLE IF NOT EXISTS `sg_role_admin` (
  `role_id`   smallint(6)  NOT NULL COMMENT '角色ID,外键',
  `admin_id`  smallint(6)  NOT NULL COMMENT '管理员ID,外键',
  FOREIGN KEY (`role_id`)  REFERENCES `sg_role`  (`id`),
  FOREIGN KEY (`admin_id`) REFERENCES `sg_admin` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员对应角色';

/*节点*/
CREATE TABLE IF NOT EXISTS `sg_node` (
  `id`     smallint(6)  NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name`   varchar(20)  NOT NULL                COMMENT '名称',
  `title`  varchar(50)  DEFAULT NULL            COMMENT '题目',
  `status` tinyint(1)   DEFAULT '0'             COMMENT '状态',
  `remark` varchar(255) DEFAULT NULL            COMMENT '说明',  
  `sort`   smallint(6)  unsigned DEFAULT NULL   COMMENT '排序',
  `pid`    smallint(6)  unsigned NOT NULL       COMMENT '父ID',
  `level`  tinyint(1)   unsigned NOT NULL       COMMENT '级别',
  PRIMARY KEY (`id`),
  KEY `level`  (`level`),
  KEY `pid`    (`pid`),
  KEY `status` (`status`),
  KEY `name`   (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='节点';

/*权限*/
CREATE TABLE IF NOT EXISTS `sg_access` (
  `role_id`  smallint(6)  NOT NULL     COMMENT '角色ID,外键',
  `node_id`  smallint(6)  NOT NULL     COMMENT '节点ID,外键',
  `level`    tinyint(1)   NOT NULL     COMMENT '级别',
  `module`   varchar(50)  DEFAULT NULL COMMENT '控制器名称',
  FOREIGN KEY (`role_id`) REFERENCES `sg_role` (`id`),
  FOREIGN KEY (`node_id`) REFERENCES `sg_node` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限';
