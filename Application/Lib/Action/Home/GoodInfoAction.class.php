<?php
class GoodInfoAction extends BaseAction{
    public function __construct(){
        parent::__construct();
        if (isset($_SESSION['user'])){
            $this->assign("isLogin",1);
        }
        else{
            $this->assign("isLogin",0);
//            $this->redirect("Login/index");
        }
        $this->assign("isBuycar",0);
        $this->assign("isLoginPage",0);
    }
    /**
    *商品详情-购物车的"立即结算",核对订单信息在OrderAction
    */
    //商品兑换页
    public function exchangeInfo(){
        $model = D('Goods');
        $goodId = I('gId');
        $_SESSION['search_pageno'] = $_GET['pageno'];
        $map['m_goods.id'] = array('eq',$goodId);
        $good = $model->where($map)->join('m_type ON m_goods.typeId=m_type.id')->field('m_goods.*,m_type.name typename,m_type.parentId parentId')->select();
        
        $colorIds = $good[0]['colorId'];
        $parendId = $good[0]['parentId'];
        if($parendId!=0){
            $model = D('Type');
            $map2['id'] = array('eq',$parendId); 
            $parentName = $model->where($map2)->find();
        }
        $imgStr = $good[0]['imgs'];
        $imgArr = explode(',',$imgStr);
        
        $_SESSION['gtypeId'] = $good[0]['typeId'];
//        var_dump($good[0]['typeId']);exit;
        $model = D('Type');
        $map3['m_type.id'] = array('eq',$good[0]['typeId']);
        $hotGoods = $model->where($map3)->join('m_goods ON m_goods.typeId=m_type.id')->field('m_goods.*,m_type.name typename,m_type.parentId parentId')->limit(C('LIST_NUM'))->select();
        /*$hotimgStr = $hotGoods[0]['imgs'];
        $hotimgArr = explode(',',$hotimgStr);*/
        foreach($hotGoods as $k=>$v){
            $hotimgStr = $hotGoods[$k]['imgs'];
            $hotimgArr = explode(',',$hotimgStr);
//                var_dump($hotimgArr);exit;
            $hotGoods[$k]['imgsArr'] = $hotimgArr;
        }
//        var_dump($hotGoods);exit;
        
        $model = D('Goodscolor');
        $map1['id'] = array('in',$colorIds);
        $colors = $model->where($map1)->select();
        $str = unserialize($good[0]['desc']);  //对数据库中序列化的字符串进行解码
        $good[0]['desc'] = htmlspecialchars_decode($str);  //html标签解码
        $good[0]['desc'] = str_replace('\\', "", $good[0]['desc']);  //html标签解码
        $count = $good[0]['stock'] - $good[0]['sales'];
        
        $model = D('Comment');
        $map5['goodsId'] = array('eq',$goodId);
        $apprSum = $model->where($map5)->count();

        $model = D('Comment');
        $map6['m_comment.goodsId'] = array('eq',$goodId);
        $appraises = $model->where($map5)->join('m_member ON m_member.id=m_comment.memberId')->field('m_comment.*,m_member.name memName')->select();
        $loginstains = 0;
        $desc = 0;
        $service = 0;
        foreach($appraises as $k=>$v){
            $appraises[$k]['apprscore'] = ceil(($appraises[$k]['loginstics']+$appraises[$k]['desc']+$appraises[$k]['service'])/3);
            $loginstains += $appraises[$k]['loginstics'];
            $desc += $appraises[$k]['desc'];
            $service += $appraises[$k]['service'];
        }
//        var_dump($good);
        $loginstains = $loginstains/$apprSum;
        $desc = $desc/$apprSum;
        $service = $service/$apprSum;
        $this->assign('parentName',$parentName);
        $this->assign('goods',$good);
        $this->assign('colors',$colors);
        $this->assign('count',$count);
        $this->assign('hotGoods',$hotGoods);
        $this->assign('imgArr',$imgArr);
        $this->assign('hotimgArr',$hotimgArr);
        $this->assign('apprSum',$apprSum);
        $this->assign('appraises',$appraises);
        $this->assign('loginstains',$loginstains);
        $this->assign('desc',$desc);
        $this->assign('service',$service);
        
        $this->display();
    }
	 /**
    *vip商品详情-购物车的"立即结算",核对订单信息在OrderAction
    */
    //vip商品兑换页
    public function vipexchangeInfo(){
        $model = D('Goods');
        $goodId = I('gId');
        $_SESSION['search_pageno'] = $_GET['pageno'];
        $map['m_goods.id'] = array('eq',$goodId);
        $good = $model->where($map)->join('m_type ON m_goods.typeId=m_type.id')->field('m_goods.*,m_type.name typename,m_type.id tid,m_type.parentId parentId')->select();
        
        $colorIds = $good[0]['colorId'];
        $parendId = $good[0]['parentId'];
        if($parendId!=0){
            $model = D('Type');
            $map2['id'] = array('eq',$parendId); 
            $parentName = $model->where($map2)->find();
        }
        // dump($parentName);
        $imgStr = $good[0]['imgs'];
        $imgArr = explode(',',$imgStr);
        
        $_SESSION['gtypeId'] = $good[0]['typeId'];
//        var_dump($good[0]['typeId']);exit;
        $model = D('Type');
        $map3['m_type.id'] = array('eq',$good[0]['typeId']);
        $map3['m_goods.status'] = array('eq',1);
        $hotGoods = $model->where($map3)->join('m_goods ON m_goods.typeId=m_type.id')->field('m_goods.*,m_type.name typename,m_type.parentId parentId')->limit(C('LIST_NUM'))->select();
        /*$hotimgStr = $hotGoods[0]['imgs'];
        $hotimgArr = explode(',',$hotimgStr);*/
        foreach($hotGoods as $k=>$v){
            $hotimgStr = $hotGoods[$k]['imgs'];
            $hotimgArr = explode(',',$hotimgStr);
//                var_dump($hotimgArr);exit;
            $hotGoods[$k]['imgsArr'] = $hotimgArr;
        }
//        var_dump($hotGoods);exit;
        
        $model = D('Goodscolor');
        $map1['id'] = array('in',$colorIds);
        $colors = $model->where($map1)->select();
        $str = unserialize($good[0]['desc']);  //对数据库中序列化的字符串进行解码
        $good[0]['desc'] = htmlspecialchars_decode($str);  //html标签解码
        $good[0]['desc'] = str_replace('\\', "", $good[0]['desc']);
        
        $count = $good[0]['stock'] - $good[0]['sales'];
        
        $model = D('Comment');
        $map5['goodsId'] = array('eq',$goodId);
        $apprSum = $model->where($map5)->count();

        $model = D('Comment');
        $map6['m_comment.goodsId'] = array('eq',$goodId);
        $appraises = $model->where($map5)->join('m_member ON m_member.id=m_comment.memberId')->field('m_comment.*,m_member.name memName')->select();
        $loginstains = 0;
        $desc = 0;
        $service = 0;
        foreach($appraises as $k=>$v){
            $appraises[$k]['apprscore'] = ceil(($appraises[$k]['loginstics']+$appraises[$k]['desc']+$appraises[$k]['service'])/3);
            $loginstains += $appraises[$k]['loginstics'];
            $desc += $appraises[$k]['desc'];
            $service += $appraises[$k]['service'];
        }
//        var_dump($good);
        $loginstains = $loginstains/$apprSum;
        $desc = $desc/$apprSum;
        $service = $service/$apprSum;
        $this->assign('parentName',$parentName);
        $this->assign('goods',$good);
        $this->assign('colors',$colors);
        $this->assign('count',$count);
        $this->assign('hotGoods',$hotGoods);
        $this->assign('imgArr',$imgArr);
        $this->assign('hotimgArr',$hotimgArr);
        $this->assign('apprSum',$apprSum);
        $this->assign('appraises',$appraises);
        $this->assign('loginstains',$loginstains);
        $this->assign('desc',$desc);
        $this->assign('service',$service);
        
        $this->display();
    }
    
    //团体认证
    public function pleaseGroup(){
        if (isset($_SESSION['user'])){
            $this->assign("isLogin",1);
        }
        else{
            $this->assign("isLogin",0);
            $this->redirect("Login/index");
        }
        $this->display();
    }
    //升级VIP
    public function shenjivip(){
        if (isset($_SESSION['user'])){
            $this->assign("isLogin",1);
        }
        else{
            $this->assign("isLogin",0);
            $this->redirect("Login/index");
        }
        $_SESSION['vip'] = true;
        $name = I('name');
        $model = D('Article');
        $title = I('title');
        $map['id'] = array('eq',19);
        $articles = $model->where($map)->find();
        $str = unserialize($articles['content']);
        $articles['content'] = htmlspecialchars_decode($str);  //html标签解码
        $articles['content'] = str_replace('\\', "", $articles['content']);  //html标签解码
        $this->assign('list',$articles);
        $this->display();
    }
    //我的购物车
    public function index(){
        $this->assign("isBuycar",1);  //头部：我的购物车文本
        if (isset($_SESSION['user'])){
            $this->assign("isLogin",1);
        }
        else{
            $this->assign("isLogin",0);
            $this->redirect("Login/index");
        }
        $this->display();
    }
    //核对订单信息
    public function order(){
        $model = D('Order');
        if (isset($_SESSION['orderId'])){
            $oId = $_SESSION['orderId'];
        }else{
            $oId = I('orderId');
        }
        $status = I('status',0);
        $map['m_order.status'] = array('eq',$status);
        $map['m_order.id'] = array('eq',$oId);
        $goods = $model->where($map)->join('m_orderinfo ON m_orderinfo.orderId = m_order.id')->join('m_goods ON m_goods.id = m_orderinfo.goodsId')->field('m_goods.id,m_orderinfo.goodsNum,m_orderinfo.goodsPrice,m_orderinfo.sumTotal,m_orderinfo.goodsColor,m_orderinfo.goodsTax,m_orderinfo.getScore,m_goods.name,m_goods.commodity,m_goods.imgs,m_order.money,m_order.freight,m_order.payment,m_order.taxrate as sumtaxrate,m_order.voucherId')->select();
        foreach($goods as $k=>$v){
            $sumMoney += $v['goodsPrice']*$v['goodsNum'];
        }
        $ZhiFu = new ZhiFuBehavior();
           $a=date("Y",time(0));
          $b=date("m",time(0));
          $c=date("d",time(0));
          $d=date("G",time(0));
          $e=date("i",time(0));
          $f=date("s",time(0));
          if($d<10){
                $d = "0".$d;
          }
          if($sumMoney==0){
                $sumMoney = 2;
          }
           $parameter = array(
                    "payUrl" =>$ZhiFu->{"JSPT_PAY_URL"},                             //支付地址
                    "version" =>$ZhiFu->{"PAY_VERSION"},                           //版本号
                    "charset" =>$ZhiFu->{"CHARSET"},                           //字符编码
                    "signMethod" =>$ZhiFu->{"SIGNMETHOD"},                     //签名方法  
                    "payType" =>"B2C",                           //支付类型
                    "transType" =>"01",                       //交易类型
                    "merId" =>$ZhiFu->{"MERID"},                              //商户编号
                    "backEndUrl" =>"http://anyooh.com/ZhiFu/zhifuSucc",                     //通知URL
                    "frontEndUrl" =>"http://anyooh.com/ZhiFu/zhifuSucc",                   //返回URL
                    "orderTime" =>$a.$b.$c.$d.$e.$f,  //"20150804132719",  //$a.$b.$c.$d.$e.$f,                       //交易时间  
                    "orderNumber" =>$_SESSION['orderNum'],                   //商户订单号
                    "orderAmount" =>$sumMoney*100,                   //交易金额
                    "orderCurrency" =>$ZhiFu->{"ORDERCURRENCY"},               //交易币种    
                    "defaultBankNumber" =>"999",       //银行编码
                    "customerIp" => $_SERVER["REMOTE_ADDR"],   //"36.46.251.85",   //$_SERVER["REMOTE_ADDR"],                     //持卡人IP
                    "merReserved1" =>"商户保留域1",                 //商户保留域1
                    "merReserved2" =>"商户保留域2",                 //商户保留域2
                    "merReserved3" =>"商户保留域3",                 //商户保留域3
                    "merSiteIP" =>" ",                       //商户网站IP
                    "gateWay" =>" ",                           //网关类型
                    "signkey" =>$ZhiFu->{"SIGNKEY"}  
          );
        
        $ZhiFu->send($parameter);
        $sign=$ZhiFu->getSign();
        $action = $ZhiFu->url;
        $this->assign('param',$parameter);
        $_SESSION['jyTime'] = $parameter['orderTime'];
        $this->assign('sign',$sign);
        $_SESSION['sumMoney'] = $parameter['orderAmount'];
        $this->assign('action',$action);

        $this->assign("isBuycar",1);
        $model = D('Province');
        $provinces = $model->select();
        $this->assign("pros",$provinces);
        $this->display();
        
    }
    //核对订单信息
    public function cashorder(){
        $this->assign("isBuycar",1);
        $model = D('Province');
        $provinces = $model->select();
        $this->assign("pros",$provinces);
        $this->display();
        
    }
    //核对订单信息
    public function viporder(){
        $this->assign("isBuycar",1);
        $model = D('Province');
        $provinces = $model->select();
        $this->assign("pros",$provinces);
        $this->display();
        
    }
    //支付
    public function payment(){
        
        
        if(!empty($_GET['orderNum'])){
            $_SESSION['orderNum'] = $_GET['orderNum'];
        }
        if(!empty($_GET['money'])||$_GET['money']==0){
            $_SESSION['shijiMoney'] = $_GET['money'];
        }
        $this->assign("isBuycar",1);
        $this->display();
    }
    //支付成功
    public function payover(){
        $this->assign("isBuycar",1);
        //更新订单状态,更新会员积分
        $model = D('Order');
        if(isset($_SESSION['memberId'])){
            $mId = $_SESSION['memberId'];
        }else{
            $mId = I('mId');
        }
        $orderNum = session('orderNum');  //获取session中保存的订单号
        $orderId = session('orderId');
        /*if($orderNum==""||$mId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }*/
        //根据订单号更新订单状态
        $map['orderNum'] = array('eq',$orderNum);
        $data['status'] = 1;
        $data['payTime'] = time(0);
        $res = $model->where($map)->save($data);
        $model = D('Orderinfo');
        $map4['memberId'] = array('eq',$mId);
        $map4['orderId'] = array('eq',$orderId);
        $orderInfos = $model->where($map4)->select();
        $goodIdArr = array();
        foreach($orderInfos as $k=>$v){
            $goodIdArr[$k] = $orderInfos[$k]["goodsId"];
            $gId = $orderInfos[$k]["goodsId"];
            $xGnum = $orderInfos[$k]["goodsNum"];
            $model = D('Goods');
            $mapQ['id'] = array('eq',$gId);
//            dump($gId);
            $goods =$model->where($mapQ)->find();
            $oldnum =$goods["stock"];
            $newnum = $oldnum - $xGnum;
            $dataw["stock"] = $newnum;
            $res = $model->where($mapQ)->save($dataw);
        }
//        dump($res); exit;
        //更新会员所拥有的积分和剩余钱数
        $model = D('Member');
        $map2['id'] = array('eq',$mId);
        $memberInfo = $model->where($map2)->find();
        $memScore = $memberInfo['score'];
        if($memberInfo['typeId']==43){
            $shijiMoney = $_SESSION['shijiMoney'];
            $data['balance'] = $memberInfo['balance']-$shijiMoney;
        }
        
        $score = session("shopScore");
        $data['score'] = $memScore+$score;
        $res1 = $model->where($map2)->save($data);
        //删除购物车里的商品
        $model = D('Buycar');
        $map3['goodsId'] = array('in',$goodIdArr);
        $map3['memberId'] = array('eq',$mId);
        $res2 = $model->where($map3)->delete();
        //删除用户积分兑换的商品
        $model = D('Exchange');
        $map4['goodsId'] = array('in',$goodIdArr);
        $map4['memberId'] = array('eq',$mId);
        $res2 = $model->where($map4)->delete();
        
        
        //返回一个热卖商品
        $model = D('Goods');
        $goods = $model->order('sales desc')->limit(1)->select();
        foreach($goods as $k=>$v){
            $hotimgStr = $goods[$k]['imgs'];
            $hotimgArr = explode(',',$hotimgStr);
//                var_dump($hotimgArr);exit;
            $goods[$k]['imgsArr'] = $hotimgArr;
        }
        $this->assign('goods',$goods);
        $this->assign('orderNum',$res);
        $this->assign('shopScore',$res1);
        $this->assign('orderId',$orderId);
        $this->display();
    }
    //商品详情页
    public function goodInfo(){
        $model = D('Goods');
        $goodId = I('gId');
        $_SESSION['searchPageNo'] = $_GET['pageno'];
        $map['m_goods.id'] = array('eq',$goodId);
        $good = $model->where($map)->join('m_type ON m_goods.typeId=m_type.id')->field('m_goods.*,m_type.name typename,m_type.parentId parentId')->select();
        foreach($good as $key=>$val){
			$good[$key]['name'] = str_replace(' ','&nbsp;',$val['name']);
			//$good[$key]['name'] = htmlspecialchars($val['name']);
		}
        $colorIds = $good[0]['colorId'];
        $parendId = $good[0]['parentId'];
        if($parendId!=0){
            $model = D('Type');
            $map2['id'] = array('eq',$parendId); 
            $parentName = $model->where($map2)->find();
        }
        $imgStr = $good[0]['imgs'];
        $imgArr = explode(',',$imgStr);
        
        $_SESSION['gtypeId'] = $good[0]['typeId'];
//        var_dump($good[0]['typeId']);exit;
        $model = D('Type');
        $map3['m_type.id'] = array('eq',$good[0]['typeId']);
        $map3['m_goods.status'] = array('eq',1);
        $hotGoods = $model->where($map3)->join('m_goods ON m_goods.typeId=m_type.id')->field('m_goods.*,m_type.name typename,m_type.parentId parentId')->limit(C('LIST_NUM'))->select();
        /*$hotimgStr = $hotGoods[0]['imgs'];
        $hotimgArr = explode(',',$hotimgStr);*/
        foreach($hotGoods as $k=>$v){
            $hotimgStr = $hotGoods[$k]['imgs'];
            $hotimgArr = explode(',',$hotimgStr);
//                var_dump($hotimgArr);exit;
            $hotGoods[$k]['imgsArr'] = $hotimgArr;
        }
//       dump($hotGoods);exit;
        
        $model = D('Goodscolor');
        $map1['id'] = array('in',$colorIds);
        $colors = $model->where($map1)->select();
        $str = unserialize($good[0]['desc']);  //对数据库中序列化的字符串进行解码
        $good[0]['desc'] = htmlspecialchars_decode($str);  //html标签解码
        $good[0]['desc'] = str_replace('\\', "", $good[0]['desc']);  //html标签解码
        $count = $good[0]['stock'] - $good[0]['sales'];
        
        $model = D('Comment');
        $map5['goodsId'] = array('eq',$goodId);
        $apprSum = $model->where($map5)->count();

        $model = D('Comment');
        $map6['m_comment.goodsId'] = array('eq',$goodId);
        $appraises = $model->where($map5)->join('m_member ON m_member.id=m_comment.memberId')->field('m_comment.*,m_member.name memName')->select();
        $loginstains = 0;
        $desc = 0;
        $service = 0;
        foreach($appraises as $k=>$v){
            $appraises[$k]['apprscore'] = ceil(($appraises[$k]['loginstics']+$appraises[$k]['desc']+$appraises[$k]['service'])/3);
            $loginstains += $appraises[$k]['loginstics'];
            $desc += $appraises[$k]['desc'];
            $service += $appraises[$k]['service'];
        }

        //设置活动期间时间段  20150731-20150901
        $mActive = M("sysconfig");
        $dActive = $mActive->where("id=81")->select();
        if($dActive[0]['status'] == '0'){  //在活动期间
                $is_active = 1;
        }else{      //非活动期间
                $is_active = 2;
        }
        $this->assign('is_active',$is_active);
        $loginstains = $loginstains/$apprSum;
        $desc = $desc/$apprSum;
        $service = $service/$apprSum;
        $this->assign('parentName',$parentName);
        $this->assign('goods',$good);
        $this->assign('colors',$colors);
        $this->assign('count',$count);
        $this->assign('hotGoods',$hotGoods);
        $this->assign('imgArr',$imgArr);
        $this->assign('hotimgArr',$hotimgArr);
        $this->assign('apprSum',$apprSum);
        $this->assign('appraises',$appraises);
        $this->assign('loginstains',$loginstains);
        $this->assign('desc',$desc);
        $this->assign('service',$service);
        
        $this->display();
    }
    
    /**
    *根据商品id查询商品信息(以及每件商品对应的所有的颜色信息集合)
    */
    public function getGoodByGoodId(){
        $model = D('Goods');
        $goodId = I('gId');
        if($goodId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $map['m_goods.id'] = array('eq',$goodId);
        $good = $model->where($map)->join('m_type ON m_goods.typeId=m_type.id')->field('m_goods.*,m_type.name typename')->select();

        if(!$good){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'查询失败'));
        }
        $colorIds = $good[0]['colorId'];
        $model = D('Goodscolor');
        $map1['id'] = array('in',$colorIds);
        $colors = $model->where($map1)->select();
        if(!$colors){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'获取颜色值失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取颜色值成功','data'=>array('good'=>$good,'colors'=>$colors)));
    }
    /**
    *根据商品id查询商品评价信息集合
    */
    public function getGoodAppraise(){
        $model = D('Comment');
        $goodId = I('gId');
        if($goodId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $map['goodsId'] = array('eq',$goodId);
        $appraises = $model->where($map)->select();
        if(!$appraises){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取评价列表失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取评价列表成功','data'=>array('list'=>$appraises)));
    }
    /**
    *加入购物车(要在提交参数的时候判断是普通会员，还是VIP，团体等信息--根据session中保存的typeId判断即可)
    */
    public function addBuyCar(){
        $model = D('Buycar');
        $name = I('gname');
        $gId = I('gId');
        $gNum = I('gNum');
        $buyTime = time(0);
        $pNum = I('pNum');
        $pImg = I('img');
        $price = I('price');
        if(isset($_SESSION['memberId'])){
            $memberId = $_SESSION['memberId'];
        }else{
            $memberId = I('mId');
        }
        $commodity = I('commodity'); //商品模式
        $taxrate = I('taxrate');      //税率
        $goodColor = I('gColor');
        $score = I('score');
        $payStatus = 0;
        $createtime = time(0);
        $map['goodsId'] = array('eq',$gId);
        $map['memberId'] = array('eq',$memberId);
        $res = $model->where($map)->field('id,pNum,goodsScore,subTotal')->find();
        $debug = $model->getLastSql();
        
        if(!$res){  //没有该商品
            $data['name'] = $name;
            $data['goodsId'] = $gId;
            $data['goodsNum'] = $gNum;
            $data['buyTime'] = $buyTime;
            $data['pNum'] = $pNum;
            $data['pImg'] = $pImg;
            $data['price'] = $price;
            if(($price*$taxrate)>50){
                $data['subTotal'] = ($price + $price*$taxrate)*$pNum;  //小计
            }else{
                $data['subTotal'] = $price*$pNum;  //小计
            }
            $data['memberId'] = $memberId;
            $data['payStatus'] = $payStatus;
            $data['createtime'] = $createtime;
            $data['commodity'] = $commodity;
            $data['taxrate'] = $taxrate;
            $data['goodsColor'] = $goodColor;
            $data['goodsScore'] = $score;
            $res = $model->add($data);
//            var_dump("$res");
            if(!$res){
            		
                $this->ajaxReturn(array('code'=>-3000,'msg'=>'加入失败'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'加入成功'));  
        }else{ //有该商品
//            $this->ajaxReturn(array('code'=>1,'msg'=>$res,'aa'=>$debug));
            $bId = $res['id'];
            $num = $res['pNum'];
            $jifen = $res['goodsScore'];
            $total = $res['subTotal'];
            $total = $total+$pNum*$price;
            $xscore = $jifen + $score;
            $xnum = $num + $pNum;
//            var_dump("$bId $num $xnum");
            $map1['id'] = array('eq',$bId);
            $data['pNum'] = $xnum;
            $data['goodsScore'] = $xscore;
            $data['subTotal'] = $total;
            $res = $model->where($map1)->save($data);
//            var_dump("$res");
            if(!$res){
                $this->ajaxReturn(array('code'=>-3001,'msg'=>'更新失败'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'更新成功'));  
        }
    }
    /**
    *更新购物车里的商品信息（购物车里点击数量发生改变时）
    */
    public function updateBuyCars(){
        $model = D('Buycar');
        $bId = I('bId');
        $buyTime = time(0);
        $pNum = I('pNum');
        $price = I('price');
        $subTotal = I('total');
        if($bId==""||$buyTime==""||$pNum==""||$price==""||$subTotal==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $map['id'] = $bId;
        $data['buyTime'] = $buyTime;
        $data['pNum'] = $pNum;
        $data['price'] = $price;
        $data['subTotal'] = $subTotal;
        $res = $model->where($map)->save($data);
        if($res !== 0 && !$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'修改失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'修改成功')); 
    }
    /**
    *移除购物车里的商品(根据购物车里每条信息的id)
    */
    public function deleteBuyCarsByBid(){
        $model = D('Buycar');
        $bId = I('bId');
        if($bId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $map['id'] = $bId;
        $res = $model->where($map)->delete();
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'删除失败，没有该商品信息或已被删除'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'删除成功')); 
    }
    /**
    *移除用户兑换商品购物车(根据购物车里每条信息的id)
    */
    public function deleteExchangeByBid(){
        $model = D('Exchange');
        $bId = I('bId');
        if($bId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $map['id'] = $bId;
        $res = $model->where($map)->delete();
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'删除失败，没有该商品信息或已被删除'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'删除成功')); 
    }
    
    
    /**
    *收藏购物车里的商品
    */
    public function collBuyCarByGid(){
        $model = D('Favorites');
        $gId = I('gId');  //商品id
//        var_dump($_SESSION['memberId']);exit;
        if (isset($_SESSION['memberId'])){
            $this->assign("isLogin",1);
            $mId = $_SESSION['memberId'];  //登录的用户id
        }
        else{
            $this->assign("isLogin",0);
            $this->redirect("Login/index");
            return;
        }
        $createtime = time(0);  //时间
        if($gId==""||$mId==""||$createtime==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $data['memberId'] = $mId;
        $data['goodsId'] = $gId;
        $data['createtime'] = $createtime;
        $map['memberId'] = array('eq',$mId);
        $map['goodsId'] = array('eq',$gId);
        $res = $model->where($map)->find();
        if($res){
            $this->ajaxReturn(array('code'=>2002,'msg'=>'已收藏'));
        }
        $res = $model->add($data);
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'收藏失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'收藏成功')); 
    }
    
    /**
    *立即结算(插入一条订单)--一次性插入
    */
    public function immeditBuy(){
        $model = D('Order');
        /*$carIdArr = I('cargoodId');  //购物车id
        $buycarId = implode(',',$carIdArr); //要提交的$buycarId字符串
        $buycarId = rtrim($buycarId,',');  
        $goodsIdArr = I('goodsId');
        if (isset($_SESSION['memberId'])){
            $mId = $_SESSION['memberId'];
        }else{
            $mId = I('mId');
        }
        $pNumArr = I('pNum');
        $goodArr = array();
        foreach($goodsIdArr as $k=>$v){
            $goodArr[$k]['goodsId'] =  $goodsIdArr[$k];
            $goodArr[$k]['goodsNum'] = $pNumArr[$k];
        }
        $buycarInfo = json_encode($goodArr);  //购物车里的商品信息*/
        
        $No = date('YmdHis').$uId.rand(1000,9999);  //生成随机数-系统生成的订单编号
        $_SESSION['orderNum'] = $No;  //将订单编号保存到sessin中，下一个页面"提交订单"时要用
        $_SESSION['shopScore'] = I('score',0);  //购物积分
        if (isset($_SESSION['memberId'])){
            $mId = $_SESSION['memberId'];
        }else{
            $mId = I('mId');
        }
        $money = I('money');  //总金额
        $payment = I('payment'); //实收金额
        $freight = I('frei');  //运费
        $voucherId = I('vId'); //代金券Id

        if($mId==""||$money==""||$payment==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }

        $data['orderNum'] = $No;
        $data['memberId'] = $mId;
//        $data['buycarInfo'] = $buycarInfo;
//        $data['buycarId'] = $buycarId;
//        $data['voucherId'] = $vId;   //1张还是多张--代金券？--不确定
        $data['money'] = $money;
        $data['payment'] = $payment;
        $data['freight'] = $freight;
        $data['status'] = 0;
        $data['createtime'] = time(0);
        $res = $model->add($data);
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'结算失败'));
        }
        $_SESSION['orderId'] = null;
        $_SESSION['orderId'] = $res;
        $this->ajaxReturn(array('code'=>2000,'msg'=>'结算成功','res'=>$res)); 
    } 
    /**
    *立即结算(插入多条订单详情信息)--插入多条
    */
    public function immeditBuyInfo(){ 
        $dataArr = $_POST['name'];
        $tmp = json_decode($dataArr,true);
        $model = D('Orderinfo');
        if (isset($_SESSION['memberId'])){
            $uId = $_SESSION['memberId'];
        }else{
            $uId = I('mId');
        }
        foreach($dataArr as $key=>$val){
            $dataArr[$key]['memberId'] = $uId;
            $dataArr[$key]['orderId'] = $_SESSION['orderId'];
            $dataArr[$key]['createtime'] = time(0);
        }
        
//        if($orderNum==""||$uId==""||$goodsPrice==""||$gId==""||$goodsNum==""||$goodsTax==""||$goodsScore==""){
//            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
//        }
//        $goodsPrice = I('price');  //金额
//        $gId = I('gId');
//        $goodsNum = I('num'); 
//        $goodsTax = I('tax');
//        $goodsScore = I('score');
//        $createtime = time(0);
//        $data['orderId'] = $orderNum;
//        $data['memberId'] = $uId;
//        $data['goodsId'] = $gId;
//        $data['goodsNum'] = $goodsNum;
//        $data['goodsPrice'] = $goodsPrice;
//        $data['goodsTax'] = $goodsTax;
//        $data['createtime'] = $createtime;
//        $data['getScore'] = $goodsScore;
//        $res = $model->add($data);
//        var_dump($dataArr);
        $res = $model->addAll($dataArr);
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'结算成功'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'结算成功','orderId'=>$_SESSION['orderId'])); 
    }
    
    /**
    *根据当前登录用户id查询该用户购物车里的所有商品
    *根据当前登录用户id查询该用户是否拥有购物车里的商品对应的的优惠券，若没有，则显示：“使用优惠券：0.00”,若有，计算优惠券总和，显示：例如：“使用优惠券：-10.00”
    */
    public function getAllBuyCars(){
        $model = D('Buycar');
        if (isset($_SESSION['memberId'])){
            $memberId = $_SESSION['memberId'];
        }else{
            $memberId = I('mId');
        }
        if($memberId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $map['memberId'] = array('eq',$memberId);
        $map['payStatus'] = array('in','0');
        $goods = $model->where($map)->select();
        if(!$goods){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'购物车为空'));
        }
        foreach($goods as $k=>$v){
            $hotimgStr = $goods[$k]['pImg'];
            $hotimgArr = explode(',',$hotimgStr); 
            $goods[$k]['imgsArr'] = $hotimgArr;
            $gId = $goods[$k]['goodsId'];
            $mapQ['id'] = array('eq',$gId);
            $model = D('Goods');
            $goodOne = $model->where($mapQ)->find();
            $goods[$k]['isOrExchange'] = $goodOne['isOrNoExchange'];
            $goods[$k]['goodStatus'] = $goodOne['status'];
        }
        
        //查找该用户是有拥有代金券
        $model = D('Membervoucher');
        $map1['m_membervoucher.mId'] = array('in',$memberId);
        $map1['m_voucherinfo.startTime'] = array('elt',time(0));
        $map1['m_voucherinfo.endTime'] = array('egt',time(0));
        $vouInfos = $model->where($map1)->join('m_voucherinfo ON m_voucherinfo.id=m_membervoucher.vId')->distinct(true)->select();
        $voucher = 0;  //用户的代金券价格
        
        if($vouInfos){
             //如果有，判断代金券的适用商品里是否有购物车里的商品id
            foreach($goods as $k=>$v){
                $gId = $goods[$k]['goodsId'];
                $tmp = explode(',',$goods[$k]['pImg']);
                $goods[$k]['pImg'] = $tmp[0];
                foreach($vouInfos as $q=>$w){
                    $arr = explode(',',$vouInfos[$q]['suitId']);  //分割成数组
                    if(in_array($gId,$arr)){
                        $voucher += $vouInfos[$q]['money'];
                    }
                }
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'该用户有优惠券','data'=>array('goods'=>$goods,'voucher'=>$voucher)));
        }else{
            $voucher = '0.00';
            $this->ajaxReturn(array('code'=>2000,'msg'=>'该用户没有优惠券','data'=>array('goods'=>$goods,'voucher'=>$voucher)));
        }
        /*
        if(!$vouInfos){
            $voucher = '0.00';
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'该用户没有优惠券','data'=>array('goods'=>$goods,'voucher'=>$voucher)));
        }
//        $debug['b'] = var_exp$goods." ".$voucher;
//        $this->ajaxReturn(array('code'=>2000,'msg'=>'该用户有优惠券','data'=>array('goods'=>$goods,'vouchers'=>$vouInfos)));
        
        //如果有，判断代金券的适用商品里是否有购物车里的商品id
        foreach($goods as $k=>$v){
            $gId = $goods[$k]['goodsId'];
            $tmp = explode(',',$goods[$k]['pImg']);
            $goods[$k]['pImg'] = $tmp[0];
            foreach($vouInfos as $q=>$w){
                $arr = explode(',',$vouInfos[$q]['suitId']);  //分割成数组
                if(in_array($gId,$arr)){
                    $voucher += $vouInfos[$q]['money'];
                }
            }
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'该用户有优惠券','data'=>array('goods'=>$goods,'voucher'=>$voucher)));
         */
    }
    
    //查询订单详情
    public function getOrderInfo(){
        $model = D('Order');
        if (isset($_SESSION['orderId'])){
            $oId = $_SESSION['orderId'];
        }else{
            $oId = I('orderId');
        }
        if (isset($_SESSION['memberId'])){
            $memberId = $_SESSION['memberId'];
        }else{
            $memberId = I('mId');
        }
        if($oId==""||$memberId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $status = I('status',0);
        $map['m_order.status'] = array('eq',$status);
        $map['m_order.id'] = array('eq',$oId);
        $goods = $model->where($map)->join('m_orderinfo ON m_orderinfo.orderId = m_order.id')->join('m_goods ON m_goods.id = m_orderinfo.goodsId')->field('m_goods.id,m_goods.weight,m_orderinfo.goodsNum,m_orderinfo.goodsPrice,m_orderinfo.sumTotal,m_orderinfo.goodsColor,m_orderinfo.goodsTax,m_orderinfo.getScore,m_goods.name,m_goods.commodity,m_goods.imgs,m_order.money,m_order.freight,m_order.payment,m_order.taxrate as sumtaxrate,m_order.voucherId')->select();
        
        $sql = $model->getLastSql();
        if(!$goods){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'订单详情为空'));
        }
        foreach($goods as $k=>$v){
            $hotimgStr = $goods[$k]['imgs'];
            $hotimgArr = explode(',',$hotimgStr);
//                var_dump($hotimgArr);exit;
            $goods[$k]['imgsArr'] = $hotimgArr;
        }
//        var_dump($goods); 
        
        //查找该用户是有拥有代金券
        $model = D('Membervoucher');
        $map1['m_membervoucher.mId'] = array('eq',$memberId);
        $vouInfos = $model->where($map1)->join('m_voucherinfo ON m_voucherinfo.id=m_membervoucher.vId')->select();
        $voucher = 0;  //用户的代金券价格

        if(!$vouInfos){
            $voucher = '0.00';
            $this->ajaxReturn(array('code'=>2000,'msg'=>'该用户没有优惠券','data'=>array('goods'=>$goods,'voucher'=>$voucher)));
        }
        //如果有，判断代金券的适用商品里是否有购物车里的商品id
        foreach($goods as $k=>$v){
            $gId = $goods[$k]['goodsId'];
            $tmp = explode(',',$goods[$k]['pImg']);
            $goods[$k]['pImg'] = $tmp[0];
            foreach($vouInfos as $q=>$w){
                $arr = explode(',',$vouInfos[$q]['suitId']);  //分割成数组
                if(in_array($gId,$arr)){
                    $voucher += $vouInfos[$q]['money'];
                }
            }
            $sumMoney += $v['goodsPrice'];
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'该用户有优惠券','data'=>array('goods'=>$goods,'voucher'=>$voucher),'sql'=>$sql)); 
    }
    
    //更新订单收获地址
    public function updateOrderAddr(){
        $model = D('Order');
        if (isset($_SESSION['orderId'])){
            $oId = $_SESSION['orderId'];
        }else{
            $oId = I('orderId');
        }
        $addrId = I('addr');
        $map['id'] = array('eq',$oId);
        $data['addressId'] = $addrId;
        $data['updatetime'] = time(0);
        $res = $model->where($map)->save($data);
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'更新地址失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'更新地址成功'));
    }
    
    
    /**
    *团体认证（申请团体认证）
    */
    public function upGradeVIP(){
        $model = D('Membergroup');

        $nameArr = I('name');
        $telArr = I('tel');
        $memArr = array();  // array(''=>'',''=>'');
        foreach($nameArr as $k=>$v){
            $memArr[$k]['name'] = $nameArr[$k];
            $memArr[$k]['tel'] = $telArr[$k];
        }
        $membergroup = json_encode($memArr);
        $memberId = I('mId');
        $organ = I('organi');
        $contacts = I('contacts');
        $phone = I('phone');
        $remark = I('remark');
        $createtime = time(0);
        if($memberId==""||$organ==""||$contacts==""||$phone==""||$remark==""||$createtime==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $data['memberId'] = $memberId;
//        $data['membergroup'] = $membergroup;
        $data['organization'] = $organ;
        $data['contacts'] = $contacts;
        $data['phone'] = $phone;
        $data['remark'] = $remark;
        $data['createtime'] = $createtime;
        $res = $model->add($data);
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'申请失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'您的申请已成功提交，等待系统审核！')); 
    }
    /**
    *点击团体认证时，根据当前登录的用户id判断该用户是否已经提交过团体认证申请
    */
    public function isOrNoSubmitApp(){
        $memberId = I('mId');
        if($memberId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $model = D('Membergroup');
        $map['memberId'] = array('eq',$memberId);
        $res = $model->where($map)->find();
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'没有提交过申请'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'已经提交过申请')); 
    }
    /**
	*	商品兑换 加商品到购物车中
	*/
	public function buy1(){ 
		$model = M('Exchange');
		$data['memberId'] = $_SESSION['memberId'];
		$map['status'] = array('eq',1);  
		$data['goodsId'] = I('gId');
		$data['goodsNum'] = I('num');
        $data['goodsScore'] = I('score');
//        $data['commodity'] = I('commodity');  //判断是否是自营--不知是否需要
        $data['goodsColor'] = I('color');
        $data['createtime'] = time();
        $map['memberId'] = array('eq',$_SESSION['memberId']);
        $map['goodsId'] = array('eq',I('gId'));
        $exchange = $model->where($map)->find();
		$debug = $model->getLastSql();
        if($exchange){
            $num = $exchange['goodsNum']+I('num');
            $score = $exchange['goodsScore'] +I('score');
            $eId = $exchange['id'];
            $map1['id'] = array('eq',$eId);
            $data1['goodsNum'] = $num;
            $data1['goodsScore'] = $score;
            $res = $model->where($map1)->save($data1);
            if($res){
                $this->ajaxReturn(array('code'=>2000,'msg'=>"更新成功",'bug'=>$debug));
            }else{
                $this->ajaxReturn(array('code'=>-6002,'msg'=>"更新失败",'bug'=>$debug));
            }
        }else{
            $res = $model->add($data);
            if($res){
                $this->ajaxReturn(array('code'=>2000,'msg'=>"添加成功",'bug'=>$debug));
            }else{
                $this->ajaxReturn(array('code'=>-6002,'msg'=>"添加失败",'bug'=>$debug));
            }
        }

	}
	
    /**
	*	vip商品兑换 加商品到购物车中
	*/
	public function buy2(){ 
		$model = M('Exchange');
		$data['memberId'] = $_SESSION['memberId'];
		$map['status'] = array('eq',2);  
		$data['goodsId'] = I('gId');
		$data['goodsNum'] = I('num');
        $data['goodsScore'] = I('score');
		$data['status'] = 2;
//        $data['commodity'] = I('commodity');  //判断是否是自营--不知是否需要
        $data['goodsColor'] = I('color');
        $data['createtime'] = time();
        $map['memberId'] = array('eq',$_SESSION['memberId']);
        $map['goodsId'] = array('eq',I('gId'));
        $exchange = $model->where($map)->find();
		$debug = $model->getLastSql();
        if($exchange){
            $num = $exchange['goodsNum']+I('num');
            $score = $exchange['goodsScore'] +I('score');
            $eId = $exchange['id'];
            $map1['id'] = array('eq',$eId);
            $data1['goodsNum'] = $num;
            $data1['goodsScore'] = $score;
            $res = $model->where($map1)->save($data1);
            if($res){
                $this->ajaxReturn(array('code'=>2000,'msg'=>"更新成功",'bug'=>$debug));
            }else{
                $this->ajaxReturn(array('code'=>-6002,'msg'=>"更新失败",'bug'=>$debug));
            }
        }else{
            $res = $model->add($data);
            if($res){
                $this->ajaxReturn(array('code'=>2000,'msg'=>"添加成功",'bug'=>$debug));
            }else{
                $this->ajaxReturn(array('code'=>-6002,'msg'=>"添加失败",'bug'=>$debug));
            }
        }

	}
	/**
	* 商品兑换
	*/
	public function cashindex(){ 
		$model = M('Exchange');
		$map['memberId'] = array('eq',$_SESSION['memberId']);  
		$map['status'] = array('eq',1);  
		$list = $model->where($map)->select();
        foreach($list as $k=>$v){
            $gId = $list[$k]['goodsId'];
            $mapQ['id'] = array('eq',$gId);
            $model = D('Goods');
            $goodOne = $model->where($mapQ)->find();
            $list[$k]['isOrExchange'] = $goodOne['isOrNoExchange'];
        }
        
		$this->assign('list',$list); 
		
		$this->display();
		/*
		if($list){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'获取成功','data'=>$list));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'获取失败'));
		}*/	
	}
		
	/**
	* vip商品兑换
	*/
	public function cashindex2(){ 
		$model = M('Exchange');
		$map['memberId'] = array('eq',$_SESSION['memberId']); 
		$map['status'] = array('eq',2);  
		$list = $model->where($map)->select();
        foreach($list as $k=>$v){
            $gId = $list[$k]['goodsId'];
            $mapQ['id'] = array('eq',$gId);
            $model = D('Goods');
            $goodOne = $model->where($mapQ)->find();
            $list[$k]['isOrExchange'] = $goodOne['isOrNoExchange'];
        }
        
		$this->assign('list',$list);
		//var_dump($list);
		$this->display();
		/*
		if($list){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'获取成功','data'=>$list));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'获取失败'));
		}*/	
	}
	//ajax cashindex
	
	public function ajaxcashindex(){ 
		$model = M('Exchange');
		$map['memberId'] = array('eq',$_SESSION['memberId']);  
		$list = $model->where($map)->select();  
		if($list){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'获取成功','data'=>$list));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'获取失败'));
		} 
	}
	

     /**
    *立即结算(插入一条订单)--一次性插入
    */
    public function immeditBuyScore(){
        $postData = $_POST;
        //$this->ajaxReturn(array('code'=>2000,'msg'=>'OK','data'=>$postData));
        $model = D('Order');
        /*$carIdArr = I('cargoodId');  //购物车id
        $buycarId = implode(',',$carIdArr); //要提交的$buycarId字符串
        $buycarId = rtrim($buycarId,',');  
        $goodsIdArr = I('goodsId');
        if (isset($_SESSION['memberId'])){
            $mId = $_SESSION['memberId'];
        }else{
            $mId = I('mId');
        }
        $pNumArr = I('pNum');
        $goodArr = array();
        foreach($goodsIdArr as $k=>$v){
            $goodArr[$k]['goodsId'] =  $goodsIdArr[$k];
            $goodArr[$k]['goodsNum'] = $pNumArr[$k];
        }
        $buycarInfo = json_encode($goodArr);  //购物车里的商品信息*/
        
        $No = 'DH'.date('YmdHis').$uId.rand(1000,9999);  //生成随机数-系统生成的订单编号
        $_SESSION['orderNum'] = $No;  //将订单编号保存到sessin中，下一个页面"提交订单"时要用
        $_SESSION['shopScore'] = I('goodsScore',0);  //积分
        if (isset($_SESSION['memberId'])){
            $mId = $_SESSION['memberId'];
        }else{
            $mId = I('mId');
        }

        $data['orderNum'] = $No;
        $data['memberId'] = $mId;
//        $data['buycarInfo'] = $buycarInfo;
//        $data['buycarId'] = $buycarId;
//        $data['voucherId'] = $vId;   //1张还是多张--代金券？--不确定
//        $data['money'] = $money;
//        $data['payment'] = $payment;
//        $data['freight'] = $freight;
        $data['status'] = 0;
        $data['createtime'] = time(0);

        $res = $model->add($data);
		$bug = $model->getLastSql();
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'结算成功','bug'=>$bug));
        }
        $_SESSION['orderId'] = $res;
        $this->ajaxReturn(array('code'=>2000,'msg'=>'结算成功','res'=>$res,'bug'=>$bug)); 
        
    } 
    /**
    *立即结算(插入多条订单详情信息)--插入多条
    */
    public function immeditBuyInfoScore(){  
        $dataArr = $_POST['name'];
        $tmp = json_decode($dataArr,true);
        $model = D('Orderinfo');
        if (isset($_SESSION['memberId'])){
            $uId = $_SESSION['memberId'];
        }else{
            $uId = I('mId');
        }
        foreach($dataArr as $key=>$val){
            $dataArr[$key]['memberId'] = $uId;
            $dataArr[$key]['orderId'] = $_SESSION['orderId'];
            $dataArr[$key]['createtime'] = time(0);
        } 
        //$this->ajaxReturn(array('code'=>2000,'msg'=>'OK','data'=>$dataArr));

//        if($orderNum==""||$uId==""||$goodsPrice==""||$gId==""||$goodsNum==""||$goodsTax==""||$goodsScore==""){
//            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
//        }
//        $goodsPrice = I('price');  //金额
//        $gId = I('gId');
//        $goodsNum = I('num'); 
//        $goodsTax = I('tax');
//        $goodsScore = I('score');
//        $createtime = time(0);
//        $data['orderId'] = $orderNum;
//        $data['memberId'] = $uId;
//        $data['goodsId'] = $gId;
//        $data['goodsNum'] = $goodsNum;
//        $data['goodsPrice'] = $goodsPrice;
//        $data['goodsTax'] = $goodsTax;
//        $data['createtime'] = $createtime;
//        $data['getScore'] = $goodsScore;
//        $res = $model->add($data);
//        var_dump($dataArr);
        $res = $model->addAll($dataArr);
		$bug = $model->getLastSql();
//        dump( $bug);
//            exit;
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'结算成功','bug'=>$bug));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'结算成功','orderId'=>$_SESSION['orderId'],'bug'=>$bug)); 
    }

    /**
    *立即结算(插入一条订单)--一次性插入
    */
    public function immeditBuyVip(){
	$postData = $_POST; 
        $model = D('Order'); 
        $No = 'VIP'.date('YmdHis').$uId.rand(1000,9999);  //生成随机数-系统生成的订单编号
        $_SESSION['orderNum'] = $No;  //将订单编号保存到sessin中，下一个页面"提交订单"时要用
//        $_SESSION['shopScore'] = I('goodsScore',0);  //购物积分
        if (isset($_SESSION['memberId'])){
            $mId = $_SESSION['memberId'];
        }else{
            $mId = I('mId');
        }

        $data['orderNum'] = $No;
        $data['memberId'] = $mId;
//        $data['buycarInfo'] = $buycarInfo;
//        $data['buycarId'] = $buycarId;
//        $data['voucherId'] = $vId;   //1张还是多张--代金券？--不确定
//        $data['money'] = $money;
//        $data['payment'] = $payment;
//        $data['freight'] = $freight;
        $data['status'] = 0;
        $data['createtime'] = time(0);

        $res = $model->add($data);
		$bug = $model->getLastSql();
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'结算成功','bug'=>$bug));
        }
        $_SESSION['orderId'] = $res;
        $this->ajaxReturn(array('code'=>2000,'msg'=>'结算成功','res'=>$res,'bug'=>$bug)); 
    } 
    /**
    *立即结算(插入多条订单详情信息)--插入多条
    */
    public function immeditBuyInfoVip(){  
        $dataArr = $_POST['name'];
        $tmp = json_decode($dataArr,true);
        $model = D('Orderinfo');
        if (isset($_SESSION['memberId'])){
            $uId = $_SESSION['memberId'];
        }else{
            $uId = I('mId');
        }
        foreach($dataArr as $key=>$val){
            $dataArr[$key]['memberId'] = $uId;
            $dataArr[$key]['orderId'] = $_SESSION['orderId'];
            $dataArr[$key]['createtime'] = time(0);
        }  
        $res = $model->addAll($dataArr);
		$bug = $model->getLastSql();
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'结算成功','bug'=>$bug));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'结算成功','orderId'=>$_SESSION['orderId'],'bug'=>$bug)); 
    }


}
?>