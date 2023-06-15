<?php
/**
 * 禁止中国大陆IP访问
 * 被禁止的IP会显示一个提示
 * @package ProhibitIP
 * @author chatgpt-4
 * @version 1.3
 * @update: 2023.06.16
 * @link https://culturesun.site
 */
class ProhibitIP_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Typecho_Plugin::factory('index.php')->begin = array('ProhibitIP_Plugin', 'ProhibitIP');
        Typecho_Plugin::factory('admin/common.php')->begin = array('ProhibitIP_Plugin', 'ProhibitIP');
        return "启用ProhibitIP成功";
    }

    public static function deactivate()
    {
        return "禁用ProhibitIP成功";
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {}

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}

    public static function prohibitIP()
    {
        if (ProhibitIP_Plugin::checkIP()) {
            header('HTTP/1.1 403 Forbidden');
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Access Denied</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 0;
                        background-color: #f0f0f0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 100vh;
                    }
                    .container {
                        text-align: center;
                    }
                    h1 {
                        color: #333;
                    }
                    p {
                        color: #666;
                    }
                    @media (max-width: 600px) {
                        h1 {
                            font-size: 1.5em;
                        }
                        p {
                            font-size: 1.2em;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>你的访问被阻止</h1>
                    <p>本站不提供给局域网居民服务，但是欢迎你越过长城，走向世界。</p>
                </div>
            </body>
            </html>';
            exit;
        }
    }

    private static function checkIP()
    {
        $request = new Typecho_Request;
        $ip = trim($request->getIp());
        $login_addr_arra = file_get_contents('http://ip-api.com/json/'.$ip.'?lang=en');
        $login_addr_arra = json_decode($login_addr_arra,true);
        $countryCode = $login_addr_arra['countryCode'];
        // 判断是否为大陆IP
        return $countryCode == 'CN';
    }
}
