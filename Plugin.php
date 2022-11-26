<?php
/**
 * 禁止国外、港澳台IP访问，也可以自己添加国内IP黑名单
 * 被禁止的IP会默认跳转到谷歌搜索
 * @package ProhibitIP
 * @author culturesun
 * @version 1.0
 * @update: 2022.11.17
 * @link https://culturesun.site
 */
class ProhibitIP_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('ProhibitIP_Plugin', 'ProhibitIP');
        return "启用ProhibitIP成功";
    }

    public static function deactivate()
    {
        return "禁用ProhibitIP成功";
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {	
        $ips = new Typecho_Widget_Helper_Form_Element_Textarea('ips', null, null, _t('IP黑名单列表'), _t('一行一个，支持规则:<br>以下是例子qwq<br>192.168.1.1<br>210.10.2.1-20<br>222.34.4.*<br>218.192.104.*'));
        $form->addInput($ips);
		$location_url = new Typecho_Widget_Helper_Form_Element_Text('location_url', NULL, 'https://www.google.com/', _t('跳转链接'),'请输入标准的URL地址，IP黑名单的IP访问将会跳转至这个URL');
        $form->addInput($location_url);

    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}

    public static function prohibitIP()
    {
        

        if (ProhibitIP_Plugin::checkIP()) {
			$location_url = trim($config->location_url) ? trim($config->location_url) : 'https://www.google.com/';
			Typecho_Cookie::delete('__typecho_uid');
            Typecho_Cookie::delete('__typecho_authCode');
            @session_destroy();
            header('Location: '.$location_url);
            exit;
        }

    }

    private static function checkIP()
    {
        $flag = false;
        $request = new Typecho_Request;
        $ip = trim($request->getIp());
        $iptable = ProhibitIP_Plugin::getAllProhibitIP();
        if ($iptable) {
            foreach ($iptable as $value) {
                if (preg_match("{$value}", $ip)) {
                    $flag = true;
                    break;
                }
            }
        }
		$lang = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$login_addr_arra = file_get_contents('http://whois.pconline.com.cn/ipJson.jsp?json=true&ip='.$ip);
		$login_addr_arra = json_decode(trim(mb_convert_encoding($login_addr_arra, "UTF-8", "GBK")),true);	
		$proCode1 = $login_addr_arra['proCode'];
		if($proCode1 == '999999' || $proCode1 == '810000' || $proCode1 == '820000' || $proCode1 == '710000' || !strstr($lang, 'zh')){
			$flag = true;
		}
		return $flag;

    }

    private static function makePregIP($str)
    {
        if (strpos($str, "-") !== false) {
            $aIP = explode(".", $str);
            foreach ($aIP as $key => $value) {
                if (strpos($value, "-") === false) {
                    if ($key == 0) {
                        $preg_limit .= ProhibitIP_Plugin::makePregIP($value);
                    } else {
                        $preg_limit .= '.' . ProhibitIP_Plugin::makePregIP($value);
                    }

                } else {
                    $aipNum = explode("-", $value);
                    for ($i = $aipNum[0]; $i <= $aipNum[1]; $i++) {
                        $preg .= $preg ? "|" . $i : "[" . $i;
                    }
                    $preg_limit .= strrpos($preg_limit, ".", 1) == (strlen($preg_limit) - 1) ? $preg . "]" : "." . $preg . "]";
                }
            }
        } else {
            $preg_limit .= $str;
        }
        return $preg_limit;
    }

    private static function getAllProhibitIP()
    {
        $config = Typecho_Widget::widget('Widget_Options')->plugin('ProhibitIP');
        $ips = $config->ips;
        if ($ips) {
            $ip_array = explode("\n", $ips);
            foreach ($ip_array as $value) {
                $ipaddress = ProhibitIP_Plugin::makePregIP($value);
                $ip = str_ireplace(".", "\.", $ipaddress);
                $ip = str_replace("*", "[0-9]{1,3}", $ip);
                $ipaddress = "/" . trim($ip) . "/";
                $ip_list[] = $ipaddress;
            }
        }
        return $ip_list;
    }

}
