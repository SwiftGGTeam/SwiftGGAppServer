/*文章分类*/
CREATE TABLE IF NOT EXISTS `sg_type`(
	`id`            int(11)       NOT NULL AUTO_INCREMENT COMMENT '主键ID',
	`name`          varchar(60)   NOT NULL COMMENT '分类名称',
	`cover_url`     varchar(2048) NULL     COMMENT '分类对应的图片',
  	`created_time`  int(11)       NOT NULL COMMENT '创建时间',
  	`updated_time`  int(11)       NOT NULL COMMENT '更新时间',
  	PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='分类';

/*文章*/
CREATE TABLE IF NOT EXISTS `sg_article`(
	`id`             int(11)       NOT NULL AUTO_INCREMENT COMMENT '主键ID',
	`tag`            varchar(2048) NULL     COMMENT '标签，使用JSON',
	`title`          varchar(100)  NOT NULL COMMENT '题目',
	`cover_url`      varchar(2048) NULL     COMMENT '封面图片URL',
	`content_url`    varchar(2048) NOT NULL COMMENT '内容URL',
	`translator`     varchar(100)  NULL     COMMENT '翻译者',
	`proofreader`    varchar(100)  NULL     COMMENT '校对者',
	`finalization`   varchar(100)  NULL     COMMENT '定稿者',
	`author`         varchar(100)  NULL     COMMENT '文章作者',
	`author_image`   varchar(2048) NULL     COMMENT '文章作者头像',
	`original_date`  varchar(100)  NULL     COMMENT '原文日期',
	`original_url`   varchar(2048) NULL     COMMENT '原文链接',
	`permalink`      varchar(2048) NOT NULL COMMENT '固定链接',
	`stars_number`   int(11)       DEFAULT '0' COMMENT '点赞数',
	`clicked_number` int(11)       DEFAULT '0' COMMENT '点击数',
  	`created_time`   int(11)       NOT NULL COMMENT '创建时间',
  	`updated_time`   int(11)       NOT NULL COMMENT '更新时间',
  	PRIMARY KEY (`id`),
  	KEY `title`   (`title`),
  	KEY `author`  (`author`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文章';

/*文章分类中间表*/
CREATE TABLE IF NOT EXISTS `sg_article_type`(
	`article_id` int(11)    NOT NULL COMMENT '文章ID,外键',
	`type_id`    int(11)    NOT NULL COMMENT '分类ID,外键',
	FOREIGN KEY (`article_id`) REFERENCES `sg_article` (`id`),
	FOREIGN KEY (`type_id`)    REFERENCES `sg_type` (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文章分类中间表';

/*用户*/
CREATE TABLE IF NOT EXISTS `sg_user`(
	`id`                int(11)        NOT NULL AUTO_INCREMENT COMMENT '主键ID',
	`account`           varchar(32)    NOT NULL COMMENT '账号',
	`password`          varchar(32)    NOT NULL COMMENT '密码',
	`salt`              varchar(6)     NOT NULL COMMENT '加盐',
	`nickname`          varchar(60)    NOT NULL COMMENT '昵称',
	`the_third_type`    varchar(10)    NOT NULL COMMENT '第三方类型名称',
	`the_third_keyseri` varchar(100)   NOT NULL COMMENT '第三方唯一标识',
	`image_url`         varchar(2048)  NOT NULL COMMENT '头像URL',
	`score`             int(11)        NOT NULL COMMENT '积分',
	`signature`         varchar(300)   NULL     COMMENT '个性签名',
	`sex`               tinyint(1)     NULL     COMMENT '性别',
	`weibo`             varchar(60)    NULL     COMMENT '微博',
	`github`            varchar(2048)  NULL     COMMENT 'github',
	`qq`                varchar(60)    NULL     COMMENT 'QQ',
	`level`             varchar(300)   NULL     COMMENT '等级',
  	`created_time`      int(11)        NOT NULL COMMENT '创建时间',
  	`updated_time`      int(11)        NOT NULL COMMENT '更新时间',
  	PRIMARY KEY (`id`),
  	UNIQUE KEY `account` (`account`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='用户';

/*用户阅读记录*/
CREATE TABLE IF NOT EXISTS `sg_read`(
	`user_id`       int(11)      NOT NULL COMMENT '用户ID,外键',
	`article_id`    int(11)      NOT NULL COMMENT '文章ID,外键',
	`datetime`      int(11)      NOT NULL COMMENT '阅读时间',
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
