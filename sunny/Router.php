<?php
/**
 * Created by sunny.
 * User: sunny
 * For Darling
 * Date: 2016/11/24
 * Time: 16:14
 */
namespace sunny;

class Router
{
    //请求类
    protected $request;
    //整个路由路径
    public static $module;
    public static $controller;
    public static $action;
    protected static $route;
    //单例
    protected static $_instance;

    public function __construct()
    {
        $this->request=Request::instance();
        if(Config::get('URL_MODE')==1) {
            $vars=$this->request->parseVar($_SERVER['REQUEST_URI']);
            self::$module = $vars['g'];
            self::$controller = ucfirst($vars['c']);
            self::$action = $vars['a'];;
        }else {
            //统一转换为小写
            self::$module = $this->request->input($_GET, 'g');
            self::$controller = ucfirst($this->request->input($_GET, 'c'));
            self::$action = $this->request->input($_GET, 'a');
            //获取之后去掉g,c,a的参数
            unset($_GET['g']);unset($_GET['c']);unset($_GET['a']);
        }
        if(empty(self::$module)){
            self::$module=Config::get('default_module');
        }
        if(empty(self::$controller)){
            self::$controller='Index';
        }
        if(empty(self::$action)){
            self::$action='index';
        }
    }

    /**
     * 运用新的方式解析路由
     * @author sunnier <xiaoyao_xiao@126.com>
     */
    public static function parseRoute(){
        $route=Route::getInstance();

        return $route->getRest();
    }

    //创建__clone方法防止对象被复制克隆
    public function __clone(){
        trigger_error('Clone is not allow!',E_USER_ERROR);
    }

    //单例方法,用于访问实例的公共的静态方法
    public static function getInstance(){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function dispatch($params='')
    {
        //使用新的方式解析路由
        //引入新增的文件
        $classname='\\app\\'.self::$module.'\\controller\\'.self::$controller;
        self::invokeMethod(array($classname,self::$action));
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param string|array $method 方法
     * @param array        $vars   变量
     * @return mixed
     */
    public static function invokeMethod($method,$args=[])
    {
        if (is_array($method)) {
            $class   = is_object($method[0]) ? $method[0] : new $method[0]();
            $reflect = new \ReflectionMethod($class, $method[1]);
        } else {
            // 静态方法
            $reflect = new \ReflectionMethod($method);
        }
        return $reflect->invokeArgs(isset($class) ? $class : null, $args);
    }

}