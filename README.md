# typecho-prohibitIP
一款禁止国外IP访问的安全插件

插件的主要实现是在线向某API接口查询访问IP的归属地，如果不是国内则返回跳转链接。
需要网站服务器可以访问API接口。

在此鸣谢太平洋网络，API接口地址--http://whois.pconline.com.cn/

使用非常简单，在usr/Plugins/目录下创建 ProhibitIP 文件夹，把Plugin.php放文件夹中，typecho后台启用即可。

默认禁止国外和港澳台IP访问，也可以自己添加国内IP黑名单。

使用本插件时，希望可以添加本人博客网站的友链。

