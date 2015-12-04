<?php
class LoginAction extends Action{
    public function __construct(){
        parent::__construct();
        if (isset($_SESSION['user'])){
            $this->assign("isLogin",1);
        }
        else{
            $this->assign("isLogin",0);
        }
        $this->assign("isLoginPage",1);
    }
    public function index(){
        $this->display();
    }
    //登录验证方法
    public function loginIn(){ 
        $name = I('name');
        $password = I('password','','md5'); //md5($_POST['password'])

        if($name==""||$password==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $model = D('Member');
        $map['nickname'] = array('eq',$name);
        
        $map['pwd'] = array('eq',$password);
        $res = $model->where($map)->find();
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'登录失败'));
        }
        if ($res['status'] == '-1') {
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'登录失败'));
        }
        $_SESSION['user'] = $name;  //Thinkphp  TP
        $_SESSION['memberId'] = $res['id'];
        $_SESSION['typeId'] = $res['typeId'];
        $_SESSION['sumScore'] = $res['score'];
        $_SESSION['sumGrade'] = $res['grade'];
        $_SESSION['isAttestation'] = $res['isAttestation'];
        $this->ajaxReturn(array('code'=>2000,'msg'=>'登录成功','data'=>array('membername'=>$name,'memberId'=>$memberId,'typeId'=>$typeId)));
    }

    public function verify() {

        import('ORG.Util.Image');

        Image::buildImageVerify(4, 1, "png", 100, 22, "userVerify");
        // Image::buildImageVerify();

    }
    
    public function register(){
        $this->display();
    }
    //注册
    public function registerIn(){
        $mModel = D('Member');
        if($_SESSION["userVerify"] != md5($_POST["verify"])) {
           $this->ajaxReturn(array('code'=>-5000,'msg'=>'验证码错误'));
         }
        if(intval(I('code'))!==intval($_SESSION['code'])){
            $this->ajaxReturn(array('code'=>-4000,'msg'=>'手机验证码错误'));
        }
        $map['nickname'] = I('uname');
        $map['tel'] = I('tel');
        $map['pwd'] = I('password','','md5');
        $map['createtime'] = time(0);
        if($map['nickname']==""||$map['tel']==""||$map['pwd']==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $dModel = $mModel->where('nickname ="' .I('uname') .'" or tel="' .I('tel') . '"')->select();
        if ($dModel != null) {
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'nick or tel reged'));
        }
        $res = $mModel->add($map);
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'注册失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'注册成功'));
    }
    /**
    *退出（跳到登录页）
    */
    public function dropOut(){
        session(null);
        $this->redirect('Login/index');  //成功的跳转    
    }

    // 忘记密码
    public function forgetPass(){
        $this->display();
    }
    //判断验证码
    public function checkCode(){
        if(intval(I('code'))!==intval($_SESSION['code'])){
                    $this->ajaxReturn(array('code'=>-4000,'msg'=>'验证码错误'));
        }
        $_SESSION['tel'] = I('tel');
        $this->ajaxReturn(array('code'=>2000,'msg'=>'成功'));
    }
    // 重置密码
    public function resetPass(){
        $this->display();
    }
    //更新密码
    public function resetPwd(){
        $model = D('Member');
        $map['tel'] = array('eq',$_SESSION['tel']);
        $data['pwd'] = I('pwd','','md5');
        $res = $model->where($map)->save($data);
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'重置密码失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'重置密码成功'));
    }
    // 重置成功
    public function resetSuccess(){
        $this->display();
    }

    /**
    * 短信接口
    * @param string $c 内容
    * @param string $m 手机号
	* http://smsapi.c123.cn/OpenPlatform/OpenApi/action=sendOnce&ac=1001@501174130001&authkey=38F7076E2937D97243C695AA50216DFD&cgid=52&csid=1001@501174130001&c=11111&m=13468600000
    */
    protected function sengSms($c='',$m=''){
        if(empty($c) || empty($m)){
            return false;
        }
    //把这里替换为你的企业短信信息
        $curlPost = 'action=sendOnce&ac=1001@501174130001&authkey=38F7076E2937D97243C695AA50216DFD&cgid=4833&csid=1001@501174130001&c='.urlencode($c).'&m='.$m;
        $ch = curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,'http://smsapi.c123.cn/OpenPlatform/OpenApi');//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 1);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch); 
        $obj = simplexml_load_string($data); 
        Log::write('01.sendSms(1 or 0):'.$obj['result']);
        //Log::write('02.sendSms_content:'.serialize($obj));
        if($obj['result'] == 1){
            return true;
        }else{
            return false;
        }  
    } 
    /**
    * 发送用户验证码
    * 发送短信验证码接口(验证码有效期5分钟)
    * http://120.25.247.237/App/Base/sendCheckcode/phone/13468600000
    */
    public function sendCheckcode(){
        if ($_SESSION["userVerify"] != md5($_POST["verify"])) {
                $this->ajaxReturn(array('code'=>-4001,'msg'=>'验证码错误'));
        }

        $phone = I('phone'); 
        if(!preg_match("/^1[1-9]{1}[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$phone)){ 
            $this->ajaxReturn(array('code'=>-6001,'msg'=>'请求参数错误'));
          //return false;   
        }
        if(empty($_SESSION['registerPhone'])||$_SESSION['registerPhone']==null){
                $_SESSION['registerPhone'] = $phone;
                $_SESSION['registerTime'] = time();
                $_SESSION['registerTimeEnd'] = time() + intval(60);
        }else{
                if($_SESSION['registerPhone']!=$phone){
                        $_SESSION['registerPhone'] = $phone;
                        $_SESSION['registerTime'] = time();
                        $_SESSION['registerTimeEnd'] = time() + intval(60);
                }else{
                        if($_SESSION['registerTimeEnd']<time()){
                                $_SESSION['registerTimeEnd'] = null;
                                $_SESSION['registerPhone'] = null;
                                $_SESSION['registerTime'] = null;
                        }else{
                                $this->ajaxReturn(array('code'=>-6003,'msg'=>'请1分钟后重新发送','start'=>$_SESSION['registerTime'],'end'=>$_SESSION['registerTimeEnd'],'now'=>time()));
                        }
                }
                
        }
        
        $codeStr = rand(100000,999999);
        $content = "您好，您的验证码是".$codeStr."";

        //发送验证码
        $sendCode = $this->sengSms($content,$phone);
        Log::write('03.发送完成返回的状态sendCode：'.$sendCode);
        //保存验证码
        // $saveCode = $this->checkcodeWrite($codeStr,$phone);
        $_SESSION['code'] = $codeStr;
        if(!$sendCode){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'手机验证码发送成功','yzm'=>'','end'=>$_SESSION['registerTimeEnd'],'sessionTel'=>$_SESSION['registerPhone'],'start'=>$_SESSION['registerTime']));
        }
        $this->ajaxReturn(array('code'=>-6002,'msg'=>'手机验证码发送失败'));
    }
    /**
    * 获取发送给用户的验证码
    * @param string $m 用户手机号
    * bool $this->getCheckcode('13468600000');
    */
    protected function getCheckcode($m=''){ 
        if(empty($m)){
            return false;
        }
        if(!preg_match("/^1[1-9]{1}[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$m)){  
          return false; 
        }
        $model = M('Checkcode');
        $map['mobile'] = array('eq',$m);
        $map['isuser'] = array('eq',1);
        $map['expiretime'] = array('egt',time());
        $res = $model->where($map)->order('createtime desc')->find();
        if($res){
            $map2['id'] = array('eq',$res['id']);
            @ $model->where($map2)->setField('isuser',2);
            return $res['code'];
        }
        Log::write('验证码数据获取失败'.$model->getLastSql());
        return false;
    }


    
}
?>