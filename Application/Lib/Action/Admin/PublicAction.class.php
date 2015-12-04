<?php
class PublicAction extends Action {
    /**
     * 公共头页面
     */
    public function header(){
        $this->display();
    } 
    /**
     * 公共菜单
     */
    public function menu(){
        $this->display();
    } 
    /**
     * 公共尾部页面
     */
    public function footer() {
        $this->display();
    }
    /**
     * 用户登录页面
     */
    public function login() {
        $this->display();
    } 
    /**
     * 用户登出
     */
    public function logout() {
        if(isset($_SESSION['UID'])) {
            unset($_SESSION['UID']);
            unset($_SESSION);
            session_destroy();
			$this->redirect(__URL__.'/login/');
            //$this->success('登出成功！',__URL__.'/login/');
        }else {
            $this->error('已经登出！');
        }
    }

    /**
     * 登录检测
     */
    public function checkLogin() {
        $loginname = I('name');
        $loginpwd = I('pass');
        $remberme = I('rememberMe');
        if(empty($loginname)) {
            $this->error('帐号不可为空！');
        }elseif (empty($loginpwd)){
            $this->error('密码不可为空！');
        }
        // elseif (empty($_POST['verify'])){
        //     $this->error('验证码必须！');
        // }
        //生成认证条件
        $map =   array();
        // 支持使用绑定帐号登录
        $map['name']   = $loginname;
        $map["status"]  =   array('gt',0);
        // if(session('verify') != md5($_POST['verify'])) {
        //     $this->error('验证码错误！');
        // }
        $mMember = M('Admin');
        $dMember = $mMember->where($map)->find(); 
        //使用用户名、密码和状态的方式进行认证
        if(false === $dMember) {
            $this->error('帐号不存在或已禁用！');
        }else {
            if($dMember['pwd'] != md5($loginpwd)) {
                $this->error('密码错误！');
            }
            $_SESSION['UID']   =   $dMember['id'];
            $_SESSION['loginName']  =   $dMember['name'];
            $_SESSION['lastLoginTime']      =   $dMember['lastLoginTime'];
            $_SESSION['lastLoginIp']    =   $dMember['lastLoginIp']; 

            //保存登录信息 
            $data = array();
            $where['id'] = array('eq',$dMember['id']);
            $data['lastLoginTime']   =   time();
            // $data['login_count'] =   array('exp','login_count+1');
            $data['lastLoginIp']    =   get_client_ip(); 
            $data['updatetime'] = time();
            @ $mMember->where($where)->save($data);
            if($remberme == 'remember-me'){
                cookie('remberme','remember-me');
                cookie('hgmsN',$loginname);
                cookie('hgmsP',$loginpwd);
            }else{
                cookie('remberme',null);
                cookie('hgmsN',null);
                cookie('hgmsP',null);
            } 
            $this->redirect('/Admin/Index/index');
            //$this->success('登录成功！','/Admin/Index/index');

        }
    } 
    // 检查用户是否登录
    protected function checkUser() {
        if(!isset($_SESSION['UID'])) {
            $this->error('没有登录','/Admin/Public/login');
        }
    }
    // 更换密码
    public function changePwd() {
        $this->checkUser();
        //对表单提交处理进行处理或者增加非表单数据
        // if(md5($_POST['verify'])    != $_SESSION['verify']) {
        //     $this->error('验证码错误！');
        // }
        $map    =   array();
        $map['loginpwd']= pwdHash($_POST['oldpassword']);
        if(isset($_POST['loginname'])) {
            $map['loginname']  =   $_POST['loginname'];
        }elseif(isset($_SESSION['UID'])) {
            $map['id']      =   $_SESSION['UID'];
        }
        //检查用户
        $Admin    =   M("Admin");
        if(!$Admin->where($map)->field('id')->find()) {
            $this->error('旧密码不符或者用户名错误！');
        }else {
            $Admin->loginpwd = pwdHash($_POST['loginpwd']);
            $Admin->updatetime = time();
            $Admin->save();
            $this->success('密码修改成功！');
         }
    }















     

    // 后台首页 查看系统信息
    public function main() {
        $info = array(
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式'=>php_sapi_name(),
            'ThinkPHP版本'=>THINK_VERSION.' [ <a href="http://thinkphp.cn" target="_blank">查看最新版本</a> ]',
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
            '服务器时间'=>date("Y年n月j日 H:i:s"),
            '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '剩余空间'=>round((@disk_free_space(".")/(1024*1024)),2).'M',
            'register_globals'=>get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
            'magic_quotes_gpc'=>(1===get_magic_quotes_gpc())?'YES':'NO',
            'magic_quotes_runtime'=>(1===get_magic_quotes_runtime())?'YES':'NO',
            );
        $this->assign('info',$info);
        $this->display();
    }

    
 
    public function verify() {
        $type	 =	 isset($_GET['type'])?$_GET['type']:'gif';
        import("ORG.Util.Image");
        Image::buildImageVerify(4,1,$type);
    }

    // 修改资料
    public function change() {
        $this->checkUser();
        $User	 =	 D("User");
        if(!$User->create()) {
            $this->error($User->getError());
        }
        $result	=	$User->save();
        if(false !== $result) {
            $this->success('资料修改成功！');
        }else{
            $this->error('资料修改失败!');
        }
    }
}