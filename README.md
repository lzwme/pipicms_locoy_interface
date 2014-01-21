pipicms_locoy_interface
=======================

pipicms皮皮影视系统火车头发布模块及免登陆接口

接口使用说明
----------------------------
1. 复制 admin 文件夹，覆盖至 pipicms 根目录即可；
2. 调用 pipicms 自动采集功能，可实现已有影片的地址更新等功能；
3. 可发布只存在下载地址的影片，下载地址和播放地址至少有一个存在。

发布模块注意事项
----------------------------
该发布模块基于火车头 v8 版本制作。应注意如下事项：

1. 应修改接口文件 /admin/pipicms_locoy.php 中的验证密码 123456 为你所设。

	//检验用户登录状态
	if(!isset($_REQUEST['pwd']) || trim($_REQUEST['pwd']) != '123456'){
		die('deny!');
	}
	
2. 应修改发布模块地址的密码验证参数 pwd=123456，为接口中设置的验证密码。

3. 影片播放地址格式与数据库中保存相同，格式参考：

	qvod$$第01集$qvod://175874380|9DF962244E01AF80A887D9097C7846DCF94954B6|女人帮妞儿第二季01[高清版].mkv|$qvod#第02集$qvod://156234593|A375670BAA225365A9B5AACE3B79CFEBBB2BD270|女人帮妞儿第二季02[高清版].mkv|$qvod$$$百度影音$$第01集$bdhd://175874380|7846FFFD968E330DA2F37DBBFB35A191|女人帮妞儿第二季01[高清版].mkv$bdhd#第02集$bdhd://156234593|E33CB3B5F3B9F75347622EFA989CCC8F|女人帮妞儿第二季02[高清版].mkv$bdhd

影片播放地址格式也可为每行一个，可自动格式化为标准格式，如：

	bdhd://156234593|E33CB3B5F3B9F75347622EFA989CCC8F|女人帮妞儿第二季01[高清版].mkv
	bdhd://156234593|E33CB3B5F3B9F75347622EFA989CCC8F|女人帮妞儿第二季02[高清版].mkv

主要标签参考
----------------------------
	v_name=[标签:标题]
	v_downdata=[标签:下载地址]
	v_playdata=[标签:播放地址]
	v_playfrom= 播放地址来源
	v_pic=[标签:缩略图]
	v_state=[标签:状态]
	v_lang=[标签:语言]
	v_publisharea=[标签:地区]
	v_publishyear=[标签:年份]
	v_note=[标签:备注]
	v_actor=[标签:演员]
	v_director=[标签:导演]
	v_content=[标签:内容]
	v_tags= 标签
	v_topic= 所属专题
	v_type=[分类ID]

相关支持
----------------------------
支持：http://lzw.me/a/pipicms-locoy-interface.html

作者：[@任侠](http://weibo.com/zhiwenweb) [@志文工作室](http://lzw.me)

网站：http://lzw.me

日期：2013-12-22