<?php
/**
 * Created by PhpStorm.
 * User: ADMIN
 * Date: 2018-08-10
 * Time: 15:39
 */
namespace utils;
class UtilsCom
{

    /**
     * json加密(中文不转码)
     * @param type $data
     * @return type
     */
    public static function jsonCn($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 目录路径处理
     * @param type $dir
     * @return type
     */
    function dirPath($dir)
    {
        $dir = str_replace('\\', '/', trim($dir));
        return $dir ? trim($dir, '/') . '/' : '';
    }


    /**
     * 创建目录
     * 适用于递归创建文件目录
     * @param type $dir
     * @param type $mode
     * @return type
     */
    function dirCreate($dir, $mode = 0755)
    {
        $dir = $this->dirPath($dir);
        $dirArr = explode('/', $dir);
        $dirCur = '';
        foreach ($dirArr as $v) {
            $dirCur .= $v . '/';
            if (@is_dir($dirCur))
                continue;
            @mkdir($dirCur, $mode);
            @chmod($dirCur, $mode);
        }
        return is_dir($dir);
    }

    /**
     * 字符串加星编码
     * @param type $str
     * @param type $replace
     * @return type
     */
    function stringEscape($str, $replace = '********')
    {
        $t = strlen($str);
        $h = ceil($t / 2);
        $d = ceil($h / 2);
        $l = substr($str, 0, $d);
        $r = substr($str, -$d);
        return $l . $replace . $r;
    }


    /**
     * 创建短链
     * @param type $url
     * @param type $api
     * @return type
     */
    function createShortUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://dwz.cn/create.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('url' => $url));
        $strRes = curl_exec($ch);
        curl_close($ch);
        $arrResponse = json_decode($strRes, true);
        $url = $arrResponse['status'] == 0 ? $arrResponse['tinyurl'] : $url;
        return $url;
    }

    /**
     * 获取Sphinx实例
     * @return \SphinxClient
     */
    function SphinxApi()
    {
        require_once(COMMON_SYS_DIR . 'sphinx/sphinxapi.php');
        $config = include(COMMON_SYS_DIR . 'sphinx/config.php');
        $Sphinx = new SphinxClient();
        $Sphinx->SetServer($config['host'], $config['port']);
        $Sphinx->SetMatchMode(SPH_MATCH_ANY);
        $Sphinx->SetConnectTimeout(5);
        $Sphinx->SetArrayResult(true);
        return $Sphinx;
    }

    /**
     * 获取Redis实例
     */
    function RedisApi()
    {
        require_once(COMMON_SYS_DIR . 'redis/Predis/Autoloader.php');
        $config = include(COMMON_SYS_DIR . 'redis/config.php');
        Predis\Autoloader::register();
        $Redis = new Predis\Client(array(
            'host' => $config['host'],
            'port' => $config['port'],
        ));
        if ($config['auth'])
            $Redis->auth($config['auth']);
        return $Redis;
    }

    /**
     * 获取BeanStail实例
     */
    function BeanstalkApi()
    {
        require_once(COMMON_SYS_DIR . 'beanstalk/Client.php');
        $config = include(COMMON_SYS_DIR . 'beanstalk/config.php');
        $Beanstalk = new Beanstalk\Client($config);
        $Beanstalk->connect();
        return $Beanstalk;
    }

    //下划线替换驼峰
    function humpToLine($str){
        $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
            return '_'.strtolower($matches[0]);
        },$str);
        return $str;
    }

    function read_dir($dir)
    {
        $files = [];
        $dir_list = scandir($dir);
        foreach ($dir_list as $file) {
            if ($file != '..' && $file != '.') {
                $file = iconv("gbk", "UTF-8", $file); // 用于获取中文目录
                if (is_dir($dir . '/' . $file)) {
                    $files[] = read_dir($dir . '/' . $file);
                } else {
                    $files[] = $file;
                }
            }
        }
        return $files;
    }

    /**适合多文件
     *
     * @param $dir
     * @return array
     */
    function read_dir_queue($dir)
    {
        $files = [];
        $queue = [$dir];
        while ($data = $this->func_new_each($queue)) {
            $path = $data['value'];
            if (is_dir($path) && $handle = opendir($path)) {
                while ($file = iconv("gbk", 'utf-8', readdir($handle))) {
                    if ($file == '.' || $file == '..') continue;
                    $real_path = $files[] = $path . '\\' . $file;
                    if (is_dir($real_path)) $queue[] = $real_path;
                }
            }
            closedir($handle);
        }
        return $files;
    }

    /**
     * 用于替换php7.2 each() 函数
     * @param $array
     * @return array|bool
     */
    function func_new_each(&$array){
        $res = array();
        $key = key($array);
        if($key !== null){
            next($array);
            $res[1] = $res['value'] = $array[$key];
            $res[0] = $res['key'] = $key;
        }else{
            $res = false;
        }
        return $res;
    }

    /**
     * 多维数组转为一维数组方法
     *
     * @param array $array
     * @return array
     */
    function array_flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) {
            $return[] = $a;
        });
        return $return;
    }




}