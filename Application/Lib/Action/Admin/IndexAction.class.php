<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends BaseAction {
    public function index(){
    	// 后台首页 查看系统信息 
        $info = array(
            PHP_OS,
            $_SERVER["SERVER_SOFTWARE"],
            php_sapi_name(),
            THINK_VERSION.' [ <a href="http://thinkphp.cn" target="_blank">查看最新版本</a> ]',
            ini_get('upload_max_filesize'),
            ini_get('max_execution_time').'秒',
            date("Y年n月j日 H:i:s"),
            gmdate("Y年n月j日 H:i:s",time()+8*3600),
            $_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            round((@disk_free_space(".")/(1024*1024)),2).'M',
            get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
            (1===get_magic_quotes_gpc())?'YES':'NO',
            (1===get_magic_quotes_runtime())?'YES':'NO',
            );
        $this->assign('info',$info); 
		$this->display();
    }
}