ThinkPHP 6.0
===============

> 运行环境要求PHP7.2+，兼容PHP8.1

[官方应用服务市场](https://market.topthink.com) | [`ThinkAPI`——官方统一API服务](https://docs.topthink.com/think-api)

ThinkPHPV6.0版本由[亿速云](https://www.yisu.com/)独家赞助发布。

## 主要新特性

* 采用`PHP7`强类型（严格模式）
* 支持更多的`PSR`规范
* 原生多应用支持
* 更强大和易用的查询
* 全新的事件系统
* 模型事件和数据库事件统一纳入事件系统
* 模板引擎分离出核心
* 内部功能中间件化
* SESSION/Cookie机制改进
* 对Swoole以及协程支持改进
* 对IDE更加友好
* 统一和精简大量用法

## 安装

~~~
composer create-project topthink/think tp 6.0.*
~~~

如果需要更新框架使用

~~~
composer update topthink/framework
~~~

## 文档

[完全开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)

## 参与开发

请参阅 [ThinkPHP 核心框架包](https://github.com/top-think/framework)。

## 版权信息

ThinkPHP遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2021 by ThinkPHP (http://thinkphp.cn)

All rights reserved。

ThinkPHP® 商标和著作权所有者为上海顶想信息科技有限公司。

更多细节参阅 [LICENSE.txt](LICENSE.txt)

寻找专属客服按省分地区 所有省+未知省
单人后台管理（以后在分级）
文章列表（成功案例）
订单信息
短信推送机制
小小程序提醒有星期限制 收钱 转账 退款 分享机制

# 这周完成功能：

1. 客服分配
   1). 根据注册用户的号码归属地，进行分配
   2).后端管理客服，增加，删除，禁用
2. 订单管理
   1).订单胜诉审核
   2).订单的退款
   3).在无申述的情况下订单（24小时）自动完成交易
3. 案例管理
   1).上传案例
   2).以及案例的其他操作(增加，修改，删除，隐藏)
4. 微信公众号功能
   1).公众号的自定义菜单
   2).公众号上传临时素材

# 还未完成的功能

1. 消息模板
2. 订阅通知
3. 后端功能的细节优化（列表分页，列表模糊查询）等
4. 营销短信
5. 发布者的累计收入金额页面
6. 转账 （交易成功后向发布者，进行打款）

