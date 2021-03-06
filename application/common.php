<?php
/**
 * Created by sunny.
 * User: sunny
 * For Darling
 * Date: 2016/12/1
 * Time: 14:13
 */
function Adminlog($user_name,$action,$class_name,$class_obj,$result){
    $data=array(
        "user_name"=>$user_name,
        "action"=>$action,
        "class_name"=>$class_name,
        "class_obj"=>$class_obj,
        "result"=>$result,
        "op_time"=>time()
    );
    $res=model("SysLog")->save($data);
    return $res;
}
function encrypt($value){
    if(!$value){return false;}
    $key = \sunny\Config::get('SECRET');
    $text = $value;
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);
    return trim(base64_encode($crypttext)); //encode for cookie
}

function decrypt($value){
    if(!$value){return false;}
    $key =  config('SECRET');
    $crypttext = base64_decode($value); //decode cookie
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
    return trim($decrypttext);
}

function setCookieRemember($encrypted,$day=7){
    setcookie("sunny_remember",$encrypted,time()+3600*24*$day);
}

function getCookieRemember(){
    if(!empty(cookie('sunny_remember'))) {
        $encrypted = cookie("sunny_remember");
        $base64 = urldecode($encrypted);
        return decrypt($base64);
    }else{
        return "";
    }
}

/**
 * 获取IP地址
 * @return string
 */
function getIp() {
    if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" )) {
        $ip = getenv ( "HTTP_CLIENT_IP" );
    } elseif (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" )) {
        $ip = getenv ( "HTTP_X_FORWARDED_FOR" );
    } elseif (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" )) {
        $ip = getenv ( "REMOTE_ADDR" );
    } elseif (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" )) {
        $ip = $_SERVER ['REMOTE_ADDR'];
    } else {
        $ip = "unknown";
    }
    return ($ip);
}

//获取用户所在分组
function getGroupName($group_id){
    if(empty($group_id)){
        return false;
    }
    $name=model("UserGroup")->field("group_name")->find($group_id);
    if(!empty($name)){
        return $name['group_name'];
    }else{
        return false;
    }
}
//添加js
function renderJsConfirm($class,$confirm_title="确定要这样做吗？"){
    $confirm_html="<script>";
    if(!is_array($class)){
        $class=explode(',',$class);
    }

    foreach($class as $item){
        $confirm_html .= "
				$('.$item').click(function(){
						
						var href=$(this).attr('goto');
						var result=window.confirm('$confirm_title');
							if(result){
								window.location.href=href;
							}
					})
				";
    }

    $confirm_html.="</script>

";
    return $confirm_html;
}

function getSysInfo() {
    $sys_info_array = array ();
    $sys_info_array ['gmt_time'] = gmdate ( "Y年m月d日 H:i:s", time () );
    $sys_info_array ['bj_time'] = gmdate ( "Y年m月d日 H:i:s", time () + 8 * 3600 );
    $sys_info_array ['server_ip'] = gethostbyname ( $_SERVER ["SERVER_NAME"] );
    $sys_info_array ['software'] = $_SERVER ["SERVER_SOFTWARE"];
    $sys_info_array ['port'] = $_SERVER ["SERVER_PORT"];
    if(isset($_SEVER['SERVER_ADMIN']))
    {
        $sys_info_array ['admin'] = $_SERVER ["SERVER_ADMIN"];
    }else{
        $sys_info_array ['admin'] = 'unknown';
    }
    $sys_info_array ['diskfree'] = intval ( diskfreespace ( "." ) / (1024 * 1024) ) . 'Mb';
    $sys_info_array ['current_user'] = @get_current_user ();
    $sys_info_array ['timezone'] = date_default_timezone_get();
    $mysql_version =model()->query("select version()")->fetch();
    $sys_info_array ['mysql_version'] = $mysql_version['version()'];
    return $sys_info_array;
}

function getMenuName($menu_id){
    if(empty($menu_id)){
        return "未知";
    }else{
        $menu=model("MenuUrl")->find($menu_id);
        if(!empty($menu)){
            return $menu['menu_name'];
        }else{
            return "未知";
        }
    }
}

//根据传来的pics返回第一张图
function getImg($pics){
    if(!empty($pics)) {
        $arrpic = explode(",", $pics);
        $file = model('File')->find($arrpic[0]);
        if(!empty($file)){
            $imgurl=DS."uploads".DS.$file["savepath"];
            return $imgurl;
        }else{
            return false;
        }
    }else{
        return false;
    }
}