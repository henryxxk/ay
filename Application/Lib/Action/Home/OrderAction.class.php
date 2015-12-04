<?php

class OrderAction extends Action{

    /**

    *订单详情（核对订单信息--支付成功后查看订单--我的订单）--买家中心在MemberAction

    */

    public function index(){

        $this->display();

    }

    /**

    *获取所有省信息

    */

    public function getAllProvinces(){

        $model = D('Province');

        $provinces = $model->select();

        if(!$provinces){

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取信息失败'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取信息成功','data'=>array('list'=>$provinces)));

    }

    /**

    *根据省id查询对应的城市

    */

    public function getAllCityByPid(){

        $model = D('City');

        $pId = I('pId');

        if($pId==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        $map['provincecode'] = array('in',$pId);

        $citys = $model->where($map)->select();

        if(!$citys){

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取信息失败'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取信息成功','data'=>array('list'=>$citys)));

    }

    /**

    *根据城市id查询对应的地区

    */

    public function getAllAreaByCid(){

        $model = D('Area');

        $cId = I('cId');

        if($cId==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        $map['citycode'] = array('in',$cId);

        $areas = $model->where($map)->select();

        if(!$areas){

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取信息失败'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取信息成功','data'=>array('list'=>$areas)));

    }

    /**

    *保存收货人信息（使用新收货地址时）

    *管理收货地址（新增收货地址）

    */

    public function addConsignee(){

        $model = D('Address');

        if (isset($_SESSION['memberId'])){

            $mId = $_SESSION['memberId'];

        }else{

            $mId = I('mId');

        }

        $name = I('name');

        $provinceId = I('proId');

        $cityId = I('cityId');

        $areaId = I('areaId');

        $specificAddr = I('specAddr');  //省市区连起来的名称

        $addr = I('addr');   //详细地址或街道地址

        $tel = I('tel');

        $postcode = I('postcode');

        $status = 1;

        $createtime = time(0);

        if($mId==""||$name==""||$provinceId==""||$cityId==""||$areaId==""||$addr==""||$tel==""||$postcode==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        $data['mId'] = $mId;

        $data['name'] = $name;

        $data['provinceId'] = $provinceId;

        $data['cityId'] = $cityId;

        $data['areaId'] = $areaId;

        $data['specificAddr'] = $specificAddr;  

        $data['addr'] = $addr;

        $data['tel'] = $tel;

        $data['postcode'] = $postcode;

        $data['status'] = $status;

        $data['createtime'] = $createtime;

        $res = $model->add($data);

//        var_dump($res);exit;

        if(!$res){      //$res表示新增的数据的id

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'保存失败'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'保存成功','res'=>$res));

    }

    /**

    *保存收货人信息后（使用新收货地址时，更新地址id字段）

    */

    public function updateOrderAddrByAddId(){

        //更新一下订单表里的地址id字段

        $model = D('Order');

        $aId = I('aId');

        if($aId==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        $map['addressId'] = array('eq',$aId);

        $res = $model->save($map);

        if(!$res){

            $this->ajaxReturn(array('code'=>-3001,'msg'=>'更新订单地址失败'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'更新订单地址成功')); 

    }

    

    /**

    *使用保存的地址时(根据选中的地址id更新订单信息里的地址id)

    */

    public function updateConsignee(){

        $model = D('Order');

        $addr = I('addrId');

//        $_SESSION['orderNum'] = '201505142054329921';

        $orderId = session('orderNum');  //获取session中保存的订单号

        if($addr==""||$orderId==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        $map['orderNum'] = array('eq',$orderId);

        $data['addressId'] = $addr;

        $res = $model->where($map)->save($data);

        if(!$res){

            $this->ajaxReturn(array('code'=>-3001,'msg'=>'更新订单地址失败'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'更新订单地址成功'));

    }

    

    /**

    *根据当前登录的用户id查询保存的收货地址

    *管理收货地址中（已保存的收货地址）

    */

    public function getAddrInfoBymId(){

        $model = D('Address');

        if (isset($_SESSION['memberId'])){

            $mId = $_SESSION['memberId'];

        }else{

            $mId = I('mId');

        }

        if($mId==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        $map['mId'] = array('in',$mId);

        $addrs = $model->where($map)->select();

        if(!$addrs){

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取地址失败，即之前没有保存过地址'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取地址成功','data'=>array('list'=>$addrs)));

    }

    /**

    *管理收货地址中（根据id更新已保存的收货地址）

    */

    public function updateAddrInfoByaId(){

        $model = D('Address');

        $aId = I('aId');

        $name = I('name');

        $provinceId = I('proId');

        $cityId = I('cityId');

        $areaId = I('areaId');

        $specificAddr = I('specAddr');  //省市区连起来的名称

        $addr = I('addr');   //详细地址或街道地址

        $tel = I('tel');

        $postcode = I('postcode');

        $status = 1;

        $updatetime = time(0);

        if($aId==""||$name==""||$provinceId==""||$cityId==""||$areaId==""||$addr==""||$tel==""||$postcode==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        $map['id'] = array('eq',$aId);

        $data['name'] = $name;

        $data['provinceId'] = $provinceId;

        $data['cityId'] = $cityId;

        $data['areaId'] = $areaId;

        $data['specificAddr'] = $specificAddr;  

        $data['addr'] = $addr;

        $data['tel'] = $tel;

        $data['postcode'] = $postcode;

        $data['status'] = $status;

        $data['updatetime'] = $updatetime;

        $res = $model->where($map)->save($data);

//        var_dump($res);exit;

        if(!$res){      //$res表示新增的数据的id

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'修改失败'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'修改成功','res'=>$res));

    }

    /**

    *管理收货地址中（根据id删除已保存的收货地址）

    */

    public function deleteAddrInfoByaId(){

        $model = D('Address');

        $aId = I('aId');

        if($aId==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        $map['id'] = array('in',$aId);

        $addrs = $model->where($map)->delete();

        if(!$addrs){

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'删除收货地址失败，即之前没有保存过地址或已被删除'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'删除收货地址成功'));

    }



    /**

    *提交订单(将"实名信息备案"部分提交)（提交完订单后切记不能清空session('orderNum')！！）

    */

    public function submitOrder(){

        
        // $this->ajaxReturn(array('code'=>0,'msg'=>$parameter,'sign'=>$sign,'action'=>$action,'yaoshi'=>md5($sign[1])));


        $model = D('Ordermember');

        $_SESSION['shijiMoney'] = I('money');

        if (isset($_SESSION['memberId'])){

            $mId = $_SESSION['memberId'];

        }else{

            $mId = I('mId');

        }

        if (isset($_SESSION['orderId'])){

            $oId = $_SESSION['orderId'];

        }else{

            $oId = I('orderId');

        }

        $orderNum = $_SESSION['orderNum']; //获取session中保存的订单号，例：2525252525

        /*$organ = I('organ');

        $contacts = I('contacts');

        $phone = I('phone');

        $mess = I('mess');*/

        $mNameArr = I('mName');

        $idenArr = I('iden');

        $memArr = array();

        foreach($mNameArr as $k=>$v){

            $memArr[$k][$v] = $idenArr[$k];

        }

        $memInfo = json_encode($memArr);

        $data['memberId'] = $mId;

        $data['orderId'] = $oId;

        $data['memInfo'] = $memInfo;

        /*$data['organization'] = $organ;

        $data['contacts'] = $contacts;

        $data['phone'] = $phone;

        $data['message'] = $mess;*/

        $data['createtime'] = time(0); 

        $res = $model->add($data);

        //更新订单运费
        $orData['freight'] = I('freight');
        $res = M('Order')->where('id='.$_SESSION['orderId'])->save($orData);

        if(!$res){

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'提交订单失败','freight'=>I('freight'),'sql'=>M('Order')->getLastSql(),'old'=>$_SESSION['orderId']));

        }
        $_SESSION['zhifuMoney'] = I('money')*100;

        $this->ajaxReturn(array('code'=>2000,'msg'=>'提交订单成功','post'=>$_POST["orderAmount"]));

    }

    /**

    *更新订单中的商品总金额和实际金额（包含运费计算，所以要更新）--不知道是什么时候更新？？？

    */

    public function updateMoney(){
            $_SESSION["zhifuMoney"] = I('money')*100;
            $this->ajaxReturn(array('code'=>2000,'msg'=>'success','money'=>$_SESSION['zhifuMoney']));
    }

    /**

    *支付成功以后，更新会员所拥有的积分，更新订单状态（已发货）以及支付时间。。。"更新之前需要？？(选择支付方式？？)"

    *执行完以后，插入一条获得积分的记录(去MemberAction的addScoreExInfo中插入一条信息)

    */

    public function paySuccess(){

        $model = D('Order');

        if(isset($_SESSION['memberId'])){

            $mId = $_SESSION['memberId'];

        }else{

            $mId = I('mId');

        }

        $orderNum = session('orderNum');  //获取session中保存的订单号

        if($orderNum==""||$mId==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        //根据订单号更新订单状态

        $map['orderNum'] = array('eq',$orderNum);

        $data['status'] = 2;

        $data['payTime'] = time(0);

        $res = $model->where($map)->save($data);

        if(!$res){

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'更新订单信息失败'));

        }

        //根据订单号读取订单中的商品信息

        /*$orderInfo = $model->where($map)->find();

        if(!$orderInfo){

            $this->ajaxReturn(array('code'=>-3001,'msg'=>'读取订单信息失败'));

        }

        $goodsIdArr = json_decode($orderInfo['buycarInfo'],ture);

        $score = 0;   //订单中的商品所拥有的积分

        $model = D('Goods');

        foreach($goodsIdArr as $k=>$v){

            $goodId = $v['goodsId'];

            $map1['id'] = array('eq',$goodId);

            $goods = $model->where($map1)->find();

            if(!$goods){

                $this->ajaxReturn(array('code'=>-6000,'msg'=>'数据库Order中保存的buycarInfo(商品信息)错误'));

            }

            $score += $goods['score'];

        }*/

        

        

        //更新会员所拥有的积分

        $model = D('Member');

        $map2['id'] = array('eq',$mId);

        $memberInfo = $model->where($map2)->find();

        if(!$memberInfo){

            $this->ajaxReturn(array('code'=>-3002,'msg'=>'读取会员信息失败'));

        }

        $memScore = $memberInfo['score'];

        $score = session("shopScore");

        $data['score'] = $memScore+$score;

        $res = $model->where($map2)->save($data);

        if(!$res){

            $this->ajaxReturn(array('code'=>-3003,'msg'=>'更新会员积分失败'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'更新会员积分成功'));  

    }

    /**

    *支付成功以后，根据当前用户id更新用户的可用余额（前提是：该用户必须是会员！！）

    */

    public function updateMemberBymId(){

        $model = D('Member');

        $mId = I('mId');

        $money = I('money');  //支付的价格

        if($mId==""||$money==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        //先查询该用户的剩余余额

        $map['id'] = array('eq',$mId);

        $member = $model->where($map)->find();

        if(!$member){

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取信息失败，请重新登录'));

        }

        $bala = $member['balance'];

        $balance = $bala - $money;

//        var_dump($balance);

        $data['balance'] = $balance;

        $res = $model->where($map)->save($data);

        if(!$res){

            $this->ajaxReturn(array('code'=>-3001,'msg'=>'更新会员余额失败'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'更新会员余额成功'));

    }



    /**

    *查看订单（支付成功以后,通过之前提交订单时保存在session中的订单编号来查看订单）

    *订单详情（通过当前选中的订单编号来查看订单）

    */

    public function lookOrderInfoByorderNum(){

        $model = D('Order');

        $orderId = I('orderNum');  //获取session中保存的订单号

        if($orderId==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));

        }

        $map['orderNum'] = array('eq',$orderId);

        $orderInfo = $model->where($map)->find();

        if(!$orderInfo){

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取订单信息失败'));

        }

        $addrId = $orderInfo['addressId'];

        $carId = $orderInfo['buycarId'];

        $model = D('Address');

        $map1['id'] = array('eq',$addrId);

        $addressInfo = $model->where($map1)->find();

        if(!$addressInfo){

            $this->ajaxReturn(array('code'=>-3001,'msg'=>'获取地址信息失败'));

        }

        $model = D('Buycar');

        $map2['id'] = array('eq',$carId);

        $buycars = $model->where($map2)->select();

        if(!$buycars){

            $this->ajaxReturn(array('code'=>-3001,'msg'=>'获取订单详情失败'));

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取信息成功','data'=>array('orderInfo'=>$orderInfo,'addr'=>$addressInfo,'buycars'=>$buycars)));

    }

    /**

    *根据当前登录的用户id及订单状态status查询订单(全部订单)(我的订单)--其中，用户id从session中获取，需要从页面中传入（考虑到手机端开发）

    */

    public function lookOrderInfoBymIdOrStatus(){

        $model = D('Order');

        $mId = I('mId');

        $status = I('status');

        $startime = I('createtime');  //时间戳

        $endtime = I('updatetime');   //时间戳

        if($mId==""||$status==""){

            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));

        }

        if($startime!=""&&$endtime!=""){

            $map['createtime'] = array('between',array($startime,$endtime));

        }

        $map['memberId'] = array('in',$mId);

        $map['status'] = array('in',$status);

        $orders = $model->where($map)->select();

        if(!$orders){

            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取订单信息失败,说明没有该状态的订单'));

        }

        foreach($orders as $k=>$v){

            $orders[$k]['buycars'] = array();   //保存订单详情（即购物车里的商品信息）

            $order_cars = $orders[$k]['buycarId'];

            $arr = explode(',',$order_cars);

            $model = D('Buycar');

            $map1['id'] = array('in',$arr);

            $buycars = $model->where($map1)->select();

            $orders[$k]['buycars'] = $buycars;

        }

        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取订单信息成功','data'=>array('list'=>$orders)));

    }

  

    

}

?>