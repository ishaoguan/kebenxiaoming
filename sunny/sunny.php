<?php
/**
 * Created by sunny.
 * User: sunny
 * For Darling
 * Date: 2016/11/24
 * Time: 9:13
 */
date_default_timezone_set('PRC');
define('SUNNY_VERSION', '0.0.1beta');
define('SUNNY_START_TIME', microtime(true));
define('SUNNY_START_MEM', memory_get_usage());
define('EXT', '.php');
define('DS', DIRECTORY_SEPARATOR);
defined('SUNNY_PATH') or define('SUNNY_PATH', __DIR__ . DS);

defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
defined('PUBLIC_PATH') or define('PUBLIC_PATH', ROOT_PATH.'public'.DS);
defined("TPL_PATH") or define("TPL_PATH",SUNNY_PATH."tpl".DS);
defined('VENDOR_PATH') or define('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);
defined('ROUTE_PATH') or define('ROUTE_PATH', ROOT_PATH . 'route' . DS);
defined('CONF_PATH') or define('CONF_PATH', ROOT_PATH.'config'.DS); // 配置文件目录
defined('ENV_PREFIX') or define('ENV_PREFIX', 'PHP_'); // 环境变量的配置前缀

// 载入Loader类
require SUNNY_PATH . 'Loader.php';

defined('APP_DEBUG') or define('APP_DEBUG', true); // 是否开启调试
// 环境常量
define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);

// 加载环境变量配置文件
if (is_file(ROOT_PATH . '.env')) {
    $env = parse_ini_file(ROOT_PATH . '.env', true);
    foreach ($env as $key => $val) {
        $name = ENV_PREFIX . strtoupper($key);
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $item = $name . '_' . strtoupper($k);
                putenv("$item=$v");
            }
        } else {
            putenv("$name=$val");
        }
    }
}
// 注册自动加载
\sunny\Loader::register();
// 注册错误和异常处理机制
\sunny\Error::register();
//载入根目录的配置
\sunny\Config::set(include CONF_PATH."config.php");
//载入帮助类
require SUNNY_PATH."helper.php";
// 加载行为扩展文件
if (is_file(APP_PATH . 'tags' . EXT)) {
    \sunny\Hook::import(include APP_PATH . 'tags' . EXT);
}
// 注册核心类的静态代理
sunny\Facade::bind([
    sunny\facade\Route::class    => sunny\Route::class,
]);
$Router=\sunny\Router::getInstance();
if(\sunny\Config::get('URL_MODE')===1) {
    //载入路由
    require ROUTE_PATH . "route.php";
    $res = \sunny\Router::parseRoute();
    $Router->dispatch($res);
}else {
    $Router->dispatch();
}
