<?php
class MemberAction extends BaseAction{
    /**
    *买家个人中心
    */
    public function index(){
		//用户信息
        $mMember = M('Member');
        $mapMember['status'] = array('eq',1);
        $mapMember['id'] = $_SESSION['memberId'];
        $dMember = $mMember->where($mapMember)->find(); 
        $dMember['vipdays'] = round(($dMember['endTime']-time(0))/3600/24);
        $this->assign('dMember',$dMember); 
        $pageNo = $_GET['pageno'];
        $pageSize = I("pageSize",3);
        if($pageNo==""){
            $pageNo = 1;
            $_GET['pageno'] = 1;
        }
        if($pageNo<=0){
            $pageNo = 1;
        }
        if($pageSize==""){
            $pageSize = 3;
        }
		//用户订单
        $model = M('Order o');
        //$mapJoin['m_order.memberId'] = array('eq',$_SESSION['memberId']); 
        $mapT['o.memberId'] = array('eq',$_SESSION['memberId']);
        $mapT['o.addressId'] = array('neq',0);
        $mapT['o.status'] = array('eq',0);
        if (!empty($_GET["status"])) {
        	$mapT['o.status'] = array('eq',$_GET["status"]);
        	
        }
        $count = $model->join('m_address a ON o.addressId=a.id')->order('o.createtime desc')->where($mapT)->field('o.*,a.specificAddr')->select();
        $uOrder = $model->join('m_address a ON o.addressId=a.id')->order('o.createtime desc')->where($mapT)->field('o.*,a.specificAddr')->page($pageNo,$pageSize)->select(); 

        // dump($count);
        // dump($model->getLastSql());
        $totalOrder = count($count);
        $totalPage = ceil($totalOrder/$pageSize); 
        if($pageNo>=$totalPage){
            $pageNo = $totalPage;
        }
        $mOrderinfo = M('Orderinfo');
		//遍历订单下商品
        $dGoods = array();
        foreach ($uOrder as $key => $value) {
            $mapTi['m_orderinfo.orderId'] = array('eq',$value['id']);
            $mapTi['m_orderinfo.memberId'] = array('eq',$_SESSION['memberId']);
            // $mapTi['m_goods.status'] = array('egt',1);
            $tmp = $mOrderinfo->join('m_goods ON m_goods.id=m_orderinfo.goodsId')->field('m_goods.*,m_orderinfo.goodsNum as OIgoodsNum,m_orderinfo.goodsPrice as OIgoodsPrice,m_orderinfo.goodsTax as OIgoodsTax,m_orderinfo.orderId as OrderId')->where($mapTi)->select(); 
			if($tmp){
				$dGoods[$key] = $tmp;
			}
        }
        // dump(count($dGoods));
//        dump($dGoods); exit;
        
		//如果存在商品 对商品进行归类
		if(count($dGoods)>0){
			//分点商品 自营 or 代购
			$list = array();
			foreach ($dGoods as $key => $value) {
				$selfArr = array();
				$behalfArr = array();
				$sum = 0;
                                            $goodStatus = 0;
				foreach ($value as $k => $v) {
					$v['subtotal'] = $v['OIgoodsPrice'] * $v['OIgoodsNum'];
					if($v['commodity'] == 0){//自营
						$selfArr[] = $v;
					}else if($v['commodity'] == 1){ //代购
						$behalfArr[] = $v;
					}
                                                     if($v["status"] != 1){
                                                              $goodStatus += 1;  //交易关闭
                                                     }
					$sum = $sum + $v['subtotal'];
                                                      $feight += $v['weight'];
				}
				if(count($selfArr)<1){
					$selfArr = '';
				}
				if(count($behalfArr)<1){
					$behalfArr = '';
				}
                                            /*if($value["status"] != 1){
                                                    $goodStatus = 1;  //交易关闭
                                            }else{
                                                    $goodStatus = 0;  //正常
                                            }*/
                                            
				$list[$key][] = array('orderId'=>$uOrder[$key]['id'],'orderNum'=>$uOrder[$key]['orderNum'],'orderTime'=>$uOrder[$key]['createtime'],'addressId'=>$uOrder[$key]['addressId'],'freight'=>$uOrder[$key]['freight'],'taxrate'=>$uOrder[$key]['taxrate'],'specificAddr'=>$uOrder[$key]['specificAddr'],'sum'=>$sum,'goodStatus'=>$goodStatus,'feight'=>$feight,'A'=>$selfArr,'B'=>$behalfArr);
			}  
			//status：表示订单状态，待付款0、待发货1、已发货2、交易成功3、交易关闭4、退款中订单5,待评价6，已完成7
			$countArr = array();
			foreach ($list as $key => $value) {
				$dfk = 0;
				$dfh = 0;
				$dpj = 0;
				$ywc = 0;
				foreach ($value as $k => $v) {
					foreach ($v['A'] as $subK => $subV) {
						if($subV['status'] == 1){
							$dfk += 1;
						}
						if($subV['status'] == 2){
							$dfh += 1;
						}
						if($subV['status'] == 6){
							$dpj += 1;
						}
						if($subV['status'] == 7){
							$ywc += 1;
						}
					}
					foreach ($v['B'] as $subK => $subV) {
						// var_dump($subV);
						if($subV['status'] == 1){
							$dfk += 1;
						}
						if($subV['status'] == 2){
							$dfh += 1;
						}
						if($subV['status'] == 6){
							$dpj += 1;
						}
						if($subV['status'] == 7){
							$ywc += 1;
						}
					}
				}
				$countArr[$key]['dfk'] = $dfk;
				$countArr[$key]['dfh'] = $dfh;
				$countArr[$key]['dpj'] = $dpj;
				$countArr[$key]['ywc'] = $ywc;
			}
		}	
		//统计
		$mapT['memberId'] = array('eq',$_SESSION['memberId']);
		$mapT['o.status'] = 0; 
		$a = $model->where($mapT)->count();
		$mapT['o.status'] = 1; 
		$b = $model->where($mapT)->count();
		$mapT['o.status'] = 6; 
		$c = $model->where($mapT)->count();
		$mapT['o.status'] = 7; 
		$d = $model->where($mapT)->count();
		$this->assign('a',$a);
		$this->assign('b',$b);
		$this->assign('c',$c);
		$this->assign('d',$d);

        // dump($list);
        $this->assign('list',$list);
        $this->assign('curP',$pageNum); 
        $this->assign('totalPage',$totalPage);
        $this->assign('status',$_GET["status"]);

        if(empty($_GET['again'])){
                $ZhiFu = new ZhiFuBehavior();
                $parasm = array();
                $geshu = 0;
                foreach($list as $k=>$v){
                    $geshu ++;
                    foreach($v as $q=>$w){
                          if(empty($w['sum'])){
                                $w['sum'] = 2;
                          }
                          $a=date("Y",time(0));
                          $b=date("m",time(0));
                          $c=date("d",time(0));
                          $d=date("G",time(0));
                          $e=date("i",time(0));
                          $f=date("s",time(0));
                          if($d<10){
                                $d = "0".$d;
                          }
                          $parameter = array();
                          $parameter[0] = array(
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
                                    "orderNumber" =>$w["orderNum"],                   //商户订单号
                                    "orderAmount" =>$w['sum'] * 100,                   //交易金额
                                    "orderCurrency" =>$ZhiFu->{"ORDERCURRENCY"},               //交易币种    
                                    "defaultBankNumber" =>"999",       //银行编码
                                    "customerIp" =>$_SERVER["REMOTE_ADDR"],   //"36.46.251.85",   //$_SERVER["REMOTE_ADDR"],                     //持卡人IP
                                    "merReserved1" =>"商户保留域1",                 //商户保留域1
                                    "merReserved2" =>"商户保留域2",                 //商户保留域2
                                    "merReserved3" =>"商户保留域3",                 //商户保留域3
                                    "merSiteIP" =>" ",                       //商户网站IP
                                    "gateWay" =>" ",                           //网关类型
                                    "signkey" =>$ZhiFu->{"SIGNKEY"}  
                          );

                    }
                    $ZhiFu->send($parameter[0]);
                    $sign=$ZhiFu->getSign();
                    $action = $ZhiFu->url;
                    $this->assign('param',$parameter);
                    $_SESSION['jyTime'] = $parameter[0]['orderTime'];
                    $this->assign('sign',$sign);
                    $_SESSION['sumMoney'] = $parameter[0]['orderAmount'];
                    $this->assign('action',$action);
                    $parasm[$geshu-1]['param'] = $parameter;
                    $parasm[$geshu-1]['sign'] = $sign;
                    $parasm[$geshu-1]['action'] = $action;
                }
                $this->assign('params',$parasm);
        }
        // dump($parasm);
        // dump($list);
        $this->display();
    }

    //支付
    public function payment(){
        
        if(!empty($_POST['orderNum'])){
            $_SESSION['orderNum'] = $_POST['orderNum'];
        }
        if(!empty($_POST['money'])){
            $_SESSION['shijiMoney'] = $_POST['money'];
        }else{
            $_SESSION['shijiMoney'] = 200;
        }
        //更新运费
        $data['freight'] = I('zhifuyunfei');
        $res = M('Order')->where('orderNum='.$_POST['orderNum'])->save($data);

        $this->assign("isBuycar",1);
        $_GET['pageno'] = I('pageno');
        $_SESSION['zhifuMoney'] = (I('money')+I('zhifuyunfei')+I('taxrate'))*100;  //运费+税费+实收金额
        $this->ajaxReturn(array('code'=>2000,'msg'=>'success','orderNum'=>$_POST['orderNum']));
    }

    /*public function paymentRequest(){

    }*/

    /**
    * 个人中心订单信息
    */
    public function ajaxGetOrder(){
        $model = M('Order');
        $mapJoin['m_order.memberId'] = array('eq',$_SESSION['memberId']);
        // $list = $model->where($map)->select();
        // $mInfo = M('Orderinfo');
        // foreach ($list as $key => $value) {
        //     $mapinfo['m_orderinfo.orderId'] = array('eq',$value['id']);
        //     $list['goodsArr'] = $mInfo->join('m_goods ON m_goods.id=m_orderinfo.goodsId')->field('m_orderinfo.orderId,m_orderinfo.goodsNum as oNum,m_orderinfo.goodsPrice as oPrice,m_orderinfo.goodsTax as oTax,m_goods.*')->where($mapinfo)->select();
        // }
        $list = $model->join('RIGHT JOIN m_orderinfo ON m_orderinfo.orderId = m_order.id')->join('RIGHT JOIN m_goods ON m_goods.id = m_orderinfo.goodsId')->field('m_order.status as oStatus,m_order.orderNum as oNum,m_order.createtime as oCreatetime,m_orderinfo.orderId,m_orderinfo.goodsNum as oinNum,m_orderinfo.goodsPrice as oinPrice,m_orderinfo.goodsTax as oinTax,m_goods.*')->where($mapJoin)->select();
$debug = $model->getLastSql();
        //status：表示订单状态，待付款0、待发货1、已发货2、交易成功3、交易关闭4、退款中订单5,待评价6，已完成7
        $map['memberId'] = array('eq',$_SESSION['memberId']);
        $map['status'] = array('in',array(0,1,2,3,4,5,6,7));
        $count['all'] = $model->where($map)->count();
        $map['status'] = array('eq',0);
        $count['dfukuan'] = $model->where($map)->count();
        $map['status'] = array('eq',1);
        $count['dfahuo'] = $model->where($map)->count();
        $map['status'] = array('eq',5);
        $count['dpinglun'] = $model->where($map)->count();
        $map['status'] = array('eq',7);
        $count['wancheng'] = $model->where($map)->count(); 

        if($list){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'数据获取成功','data'=>$list,'count'=>$count));
        }else{
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'数据获取失败','debug'=>$debug));
        }
    }
    //我的订单
    public function myorder(){
		//订单信息
		$model = D('Order');
        $where['id'] = array('eq',$_REQUEST['id']);
        $list = $model->where($where)->find();
		//收货地址
		$mAddr = M('Address');
		$whereAddr['id'] = array('eq',$list['addressId']);
		$dAddr = $mAddr->where($whereAddr)->find();
		//订单商品
        $mOrderinfo = D('Orderinfo');
        $map['orderId'] = array('eq',$_REQUEST['id']);
        $dOrderinfo = $mOrderinfo->where($map)->select();
        $total['totalGoods'] = 0; 
        $total['totalPrice'] = 0;
        foreach ($dOrderinfo as $key => $value) {
	        $total['totalGoods'] += 1; 
        	$total['totalPrice'] += sprintf("%.2f", $value['goodsNum'] * $value['goodsPrice']);

        	$dOrderinfo[$key]['subtotal'] = sprintf("%.2f", $value['goodsNum'] * $value['goodsPrice']);
        }
		//订单评论
		$mComment = M('Comment');
		$mapComment['orderId'] = array('eq',$_REQUEST['id']);
		$dComment = $mComment->where($mapComment)->find();

        //合计 = 商品价格 + 运费 - 优惠券
         $total['totalPrice'] =  $total['totalPrice'] +  $list['freight'] -  $list['voucherId']; //coupon;  
//        dump($list);
//        dump($dAddr);
        
        if($list){
            $this->assign('list',$list); 
            $this->assign('dAddr',$dAddr); 
            $this->assign('dComment',$dAdCommentddr); 
            $this->assign('dOrderinfo',$dOrderinfo);
            $this->assign('total',$total); 
        	$this->display();
        }else{
			$this->display();
        }

    }
    //我的收藏
    public function mycoll(){ 
        //分页 
        $page = I('page',1,'intval');
        if($page<1){
            $page = 1;
        } 
        $pageSize = C('PAGE_SIZE');
        //
        $model = M('Favorites');
        $map['m_favorites.memberId'] = array('eq',$_SESSION['memberId']);
        $all = $model->join('m_goods ON m_goods.id=m_favorites.goodsId')->where($map)->count();
        $totalPage = ceil($all/$pageSize);
        // var_dump($totalPage);
        if($page>=$totalPage){
            $page = $totalPage;
        }
        $list = $model->join('m_goods ON m_goods.id=m_favorites.goodsId')->where($map)->page($page,$pageSize)->field('m_goods.*,m_favorites.id as fId,m_favorites.goodsId as gId,m_favorites.createtime as fCreatetime')->select();
        // print_r($model->getLastSql());
        foreach ($list as $key => $value) {
            $imgArr = explode(',', $value['imgs']);
            $list[$key]['imgTop'] = $imgArr[0];
        }
//        dump($list);exit;
        $this->assign("list",$list);
        $this->assign('totalPage',$totalPage); 
        $this->assign('curP',$page);
        $this->display();
    }
    //取消收藏
    public function cancelFavorites(){
        $id = I('id');
        $model = M('Favorites');
        $map['id'] = array('eq',$id); 
        $res = $model->where($map)->delete();
        if($res){
            $this->ajaxReturn(array('code'=>2000,'mgs'=>'操作成功'));
        }else{
            $this->ajaxReturn(array('code'=>-6002,'mgs'=>'操作失败'));
        } 
    }
    //添加购物车
    public function addChar(){
        $gId = I('gId');
        $mGoods = M('Goods');
        $mapGoods['id'] = array('eq',$gId);
        $dGoods = $mGoods->where($mapGoods)->find();
//        dump($gId);exit;

        $name =  $dGoods['name'];
        $gNum =  $dGoods['goodsNum'];
        $buyTime = time(0);
        $pNum = 1;
        $pImg =  $dGoods['imgs'];
        $price =  $dGoods['marketPrice'];
        if($_SESSION['typeId'] == 43){
            $price =  $dGoods['groupPrice'];
        }
        if($_SESSION['typeId'] == 44){
            $price =  $dGoods['vipPrice'];
        }
        $memberId = $_SESSION['memberId'];
        $commodity =  $dGoods['commodity']; //商品模式
        $taxrate =  $dGoods['taxrate'];      //税率
        $payStatus = 0;
        $createtime = time(0);
        
        $model = D('Buycar'); 
        $map['goodsId'] = array('eq',$gId);
        $map['memberId'] = array('eq',$memberId);
        $res = $model->where($map)->field('id,pNum,goodsScore,subTotal')->find();
        $debug = $model->getLastSql();

         //小计
        if(intval($price*$taxrate)>50){
            $xiaoji = ($price + $price*$taxrate)*$pNum;
        }else{
            $xiaoji = $price*$pNum;
        }
        
        if(!$res){  //没有该商品
            $data['name'] = $name;
            $data['goodsId'] = $gId;
            $data['goodsNum'] = $gNum;
            $data['buyTime'] = $buyTime;
            $data['pNum'] = $pNum;
            $data['pImg'] = $pImg;
            $data['price'] = $price;
            $data['subTotal'] = $xiaoji;  //小计
            $data['memberId'] = $memberId;
            $data['payStatus'] = $payStatus;
            $data['createtime'] = $createtime;
            $data['commodity'] = $commodity;
            $data['taxrate'] = $taxrate;
            $res = $model->add($data);
            if(!$res){
            		
                $this->ajaxReturn(array('code'=>-3000,'msg'=>'加入失败'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'加入成功'));
        }else{
            $bId = $res['id'];
            $num = $res['pNum'];
            $jifen = $res['goodsScore'];
            $total = $res['subTotal'];
            $total = $total+$xiaoji;
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
            $this->ajaxReturn(array('code'=>2000,'msg'=>'加入成功'));
        }
        
        
        // $debug = $model->getLastSql();
        // $debug = $data;
        if($res){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'加入成功','debug'=>$debug)); 
        }    
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'加入失败','debug'=>$debug));
    }
    //订单详情
    public function orderinfo(){ 
        //用户信息
        $mMember = M('Member');
        $mapMember['status'] = array('eq',1);
        $mapMember['id'] = $_SESSION['memberId'];
        $dMember = $mMember->where($mapMember)->find(); 
        $dMember['vipdays'] = round(($dMember['endTime']-$dMember['startTime'])/3600/24);
        $this->assign('dMember',$dMember); 
        
        $pageNo = $_GET['pageno'];
        // $pageNo = I("pageno");
        $pageSize = I("pageSize",3);
        if($pageNo==""){
            $pageNo = 1;
            $_GET['pageno'] = 1;
        }
        if($pageNo<=0){
            $pageNo = 1;
        }
        if($pageSize==""){
            $pageSize = 3;
        }
		//用户订单
        $model = M('Order o');
        //$mapJoin['m_order.memberId'] = array('eq',$_SESSION['memberId']); 
        $mapT['o.memberId'] = array('eq',$_SESSION['memberId']);
        $mapT['o.addressId'] = array('neq',0);
        if (!empty($_GET["status"])) {
            $mapT['o.status'] = array('eq',$_GET["status"]);
            
        }
        $count = $model->join('m_address a ON o.addressId=a.id')->order('o.createtime desc')->where($mapT)->field('o.*,a.specificAddr')->select();
        $uOrder = $model->join('m_address a ON o.addressId=a.id')->order('o.createtime desc')->where($mapT)->field('o.*,a.specificAddr')->page($pageNo,$pageSize)->select();

        $totalOrder = count($count);
        $totalPage = ceil($totalOrder/$pageSize); 
        if($pageNo>=$totalPage){
            $pageNo = $totalPage;
        }
        $mOrderinfo = M('Orderinfo');
		//遍历订单下商品
        $dGoods = array();
        foreach ($uOrder as $key => $value) {
            $mapTi['m_orderinfo.orderId'] = array('eq',$value['id']);
            $mapTi['m_orderinfo.memberId'] = array('eq',$_SESSION['memberId']);
            $tmp = $mOrderinfo->join('m_goods ON m_goods.id=m_orderinfo.goodsId')->field('m_goods.*,m_orderinfo.goodsNum as OIgoodsNum,m_orderinfo.goodsPrice as OIgoodsPrice,m_orderinfo.goodsTax as OIgoodsTax')->where($mapTi)->select(); 
			if($tmp){
				$dGoods[$key] = $tmp;
			}
        }
		//如果存在商品 对商品进行归类
		if(count($dGoods)>0){
			//分点商品 自营 or 代购
                                $list = array();
                                foreach ($dGoods as $key => $value) {
                                    $selfArr = array();
                                    $behalfArr = array();
                                    $sum = 0;
                                    $goodStatus = 0;
                                    foreach ($value as $k => $v) {
                                        $v['subtotal'] = $v['OIgoodsPrice'] * $v['OIgoodsNum'];
                                        if($v['commodity'] == 0){//自营
                                            $selfArr[] = $v;
                                        }else if($v['commodity'] == 1){ //代购
                                            $behalfArr[] = $v;
                                        }
                                         if($v["status"] != 1){
                                                  $goodStatus += 1;  //交易关闭
                                         }
                                        $sum = $sum + $v['subtotal'];
                                        $feight += $v['weight'];
                                    }
                                    if(count($selfArr)<1){
                                        $selfArr = '';
                                    }
                                    if(count($behalfArr)<1){
                                        $behalfArr = '';
                                    }
                                    $list[$key][] = array('orderId'=>$uOrder[$key]['id'],'orderNum'=>$uOrder[$key]['orderNum'],'orderTime'=>$uOrder[$key]['createtime'],'addressId'=>$uOrder[$key]['addressId'],'freight'=>$uOrder[$key]['freight'],'taxrate'=>$uOrder[$key]['taxrate'],'specificAddr'=>$uOrder[$key]['specificAddr'],'sum'=>$sum,'goodStatus'=>$goodStatus,'feight'=>$feight,'A'=>$selfArr,'B'=>$behalfArr);
                                }  
			//status：表示订单状态，待付款0、待发货1、已发货2、交易成功3、交易关闭4、退款中订单5,待评价6，已完成7
			$countArr = array();
			foreach ($list as $key => $value) {
				$dfk = 0;
				$dfh = 0;
				$dpj = 0;
				$ywc = 0;
				foreach ($value as $k => $v) {
					foreach ($v['A'] as $subK => $subV) {
						if($subV['status'] == 1){
							$dfk += 1;
						}
						if($subV['status'] == 2){
							$dfh += 1;
						}
						if($subV['status'] == 6){
							$dpj += 1;
						}
						if($subV['status'] == 7){
							$ywc += 1;
						}
					}
					foreach ($v['B'] as $subK => $subV) {
						// var_dump($subV);
						if($subV['status'] == 1){
							$dfk += 1;
						}
						if($subV['status'] == 2){
							$dfh += 1;
						}
						if($subV['status'] == 6){
							$dpj += 1;
						}
						if($subV['status'] == 7){
							$ywc += 1;
						}
					}
				}
				$countArr[$key]['dfk'] = $dfk;
				$countArr[$key]['dfh'] = $dfh;
				$countArr[$key]['dpj'] = $dpj;
				$countArr[$key]['ywc'] = $ywc;
			}
		}	
        
//        dump($list); exit;
		//统计
		$mapT['memberId'] = array('eq',$_SESSION['memberId']);
		$mapT['o.status'] = 0; 
		$a = $model->where($mapT)->count();
		$mapT['o.status'] = 1; 
		$b = $model->where($mapT)->count();
		$mapT['o.status'] = 6; 
		$c = $model->where($mapT)->count();
		$mapT['o.status'] = 7; 
		$d = $model->where($mapT)->count();
		$this->assign('a',$a);
		$this->assign('b',$b);
		$this->assign('c',$c);
		$this->assign('d',$d);
        $this->assign('list',$list);
        $this->assign('curP',$pageNum); 
        $this->assign('totalPage',$totalPage);
        $this->assign('status',$_GET["status"]);

        $ZhiFu = new ZhiFuBehavior();
        $parasm = array();
        $geshu = 0;
        foreach($list as $k=>$v){
            $geshu ++;
            foreach($v as $q=>$w){
                  if(empty($w['sum'])){
                        $w['sum'] = 2;
                  }
                  $a=date("Y",time(0));
                  $b=date("m",time(0));
                  $c=date("d",time(0));
                  $d=date("G",time(0));
                  $e=date("i",time(0));
                  $f=date("s",time(0));
                  if($d<10){
                        $d = "0".$d;
                  }
                  $parameter = array();
                  $parameter[0] = array(
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
                            "orderNumber" =>$w["orderNum"],                   //商户订单号
                            "orderAmount" => $w['sum'] * 100,                   //交易金额
                            "orderCurrency" =>$ZhiFu->{"ORDERCURRENCY"},               //交易币种    
                            "defaultBankNumber" =>"999",       //银行编码
                            "customerIp" =>"36.46.251.85",   //$_SERVER["REMOTE_ADDR"],                     //持卡人IP
                            "merReserved1" =>"商户保留域1",                 //商户保留域1
                            "merReserved2" =>"商户保留域2",                 //商户保留域2
                            "merReserved3" =>"商户保留域3",                 //商户保留域3
                            "merSiteIP" =>" ",                       //商户网站IP
                            "gateWay" =>" ",                           //网关类型
                            "signkey" =>$ZhiFu->{"SIGNKEY"}  
                  );

                
            }
            $ZhiFu->send($parameter[0]);
            $sign=$ZhiFu->getSign();
            $action = $ZhiFu->url;
            $this->assign('param',$parameter);
            $_SESSION['jyTime'] = $parameter[0]['orderTime'];
            $this->assign('sign',$sign);
            $_SESSION['sumMoney'] = $parameter[0]['orderAmount'];
            $this->assign('action',$action);
            $parasm[$geshu-1]['param'] = $parameter;
            $parasm[$geshu-1]['sign'] = $sign;
            $parasm[$geshu-1]['action'] = $action;
        }
        $this->assign('params',$parasm);

        $this->display();
    }
    
    /**
    *买家个人中心（根据session中保存的用户id获取买家信息及他的所有订单信息(从页面中传入)）
    */
    public function getMemInfo(){
        $model = D('Member');
        $mId = I('mId');
        if($mId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $map['id'] = array('eq',$mId);
        $member = $model->where($map)->find();
        if(!$member){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取用户信息失败'));
        }
        $model = D('Order');
        $map1['memberId'] = array('in',$mId);
        $orders = $model->where($map1)->select();
        if(!$orders){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'获取订单信息失败,说明没有该状态的订单'));
        }
        foreach($orders as $k=>$v){
            $orders[$k]['buycars'] = array();   //保存订单详情（即购物车里的商品信息）
            $order_cars = $orders[$k]['buycarId'];
            $arr = explode(',',$order_cars);
            $model = D('Buycar');
            $map2['id'] = array('in',$arr);
            $buycars = $model->where($map2)->select();
            $orders[$k]['buycars'] = $buycars;
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取用户信息成功','data'=>array('member'=>$member,'orders'=>$orders)));     
    }
    /**
    *我的收藏(根据用户id进行查找)
    */
    public function getMyFavourBymId(){
        $model = D('Favorites');
        $mId = I('mId');
        $page = I('page');
        $pageSize = 8;  //默认是8
        if($mId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        if($page==""){
            $page = 1;  //默认第一页
        }
        $map['memberId'] = array('in',$mId);
        $favours = $model->where($map)->select();
        if(!$favours){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'获取收藏信息失败，用户信息过期或没有登录'));
        }
        foreach($favours as $k=>$v){
            $favours[$k]['goodInfos'] = array();  //创建一个数组用来保存用户收藏的商品集合
            $goodId = $favours[$k]['goodsId'];
            $model = D('Goods');
            $map1['id'] = array('eq',$goodId);
            $good = $model->where($map1)->find();
            $favours[$k]['goodInfos'] = $good;
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取用户信息成功','data'=>array('list'=>$favours)));     
    }
    /**
    *删除某一条收藏（根据当前的收藏id）
    */
    public function deleteMyFavourByfId(){
        $model = D('Favorites');
        $fId = I('fId');
        if($fId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $map['id'] = array('eq',$fId);
        $res = $model->where($map)->delete();
        if(!$res){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'删除收藏信息失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'删除收藏信息成功'));
    }
    /**
    *我的代金券
    */
    public function getMyVoucherBymId(){
        $model = D('Membervoucher');
        $mId = I('mId');
        if($mId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $map['mId'] = array('in',$mId);
        $vouchers = $model->where($map)->select();
        if(!$vouchers){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取信息失败'));
        }
        foreach($vouchers as $k=>$v){
            $vouchers[$k]['vouchers'] = array();  //创建一个数组用来保存用户收藏的商品集合
            $vId = $vouchers[$k]['vId'];
            //查询代金券信息
            $model = D('Voucherinfo');
            $map1['id'] = array('eq',$vId);
            $vou = $model->where($map1)->find();
            $typeId = $vou['typeId'];
            //查询代金券名字
            $vou['typename'] = ""; 
            $model = D('Type');
            $map2['id'] = array('eq',$typeId);
            $type = $model->where($map2)->find();
            $vou['typename'] = $type['name'];
            $vouchers[$k]['vouchers'] = $vou;
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取信息成功','data'=>array('list'=>$vouchers)));
    }
    /**
    *积分兑换（插入一条积分兑换记录）--1.兑换商品成功时？？（什么时候兑换）；2.支付订单成功时（插入的数据："订单编号：55142503899 -10"）？？？？？？？？？？
    */
    public function addScoreExInfo(){
        $model = D('Scoreexchangelog');
        $mId = I('mId');
        $source = I('source');
        $exScore = I('score');
        $createtime = time(0);
        if($source==""||$exScore==""||$mId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $data['memberId'] = $mId;
        $data['source'] = $source;
        $data['exchangeScore'] = $exScore;
        $data['createtime'] = $createtime;
        $res = $model->add($data);
//        $debug = $model->getLastSql();
//        var_dump($debug); exit;
        if(!$res){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'添加失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'添加成功'));
    }
    /**
    *我的积分（根据当前用户id查询积分兑换记录）
    */
    public function getMyScoreExBymId(){
        $model = D('Member');
        $mId = I('mId');
        if($mId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $map['id'] = array('eq',$mId);
        $member = $model->where($map)->find();
        if(!$member){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取用户信息失败'));
        }
        $model = D('Scoreexchangelog');
        $map1['memberId'] = array('in',$mId);
        $scorelog = $model->where($map1)->select();
        if(!$scorelog){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'获取信息失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取信息成功','data'=>array('member'=>$member,'scorelog'=>$scorelog)));
    }
    /**
    *我的资料（查询）
    */
    public function getMemberBymId(){
        $model = D('Member');
        $mId = I('mId');
        if($mId==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $map['id'] = array('eq',$mId);
        $member = $model->where($map)->find();
        if(!$member){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取用户信息失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取信息成功','data'=>array('member'=>$member)));
    }
    /**
    *我的资料（更新）
    */
    public function updateMemberBymId(){
        $model = D('Member');
        $mId = I('mId');
        $imgUrl = I('imgUrl');
        $nickname = I('nickname');
        $tel = I('tel');
        $name = I('name');
        if($mId==""||$imgUrl==""||$nickname==""||$tel==""||$name==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $map['id'] = array('eq',$mId);
        $data['imgUrl'] = $imgUrl;
        $data['nickname'] = $nickname;
        $data['tel'] = $tel;
        $data['name'] = $name;
        $member = $model->where($map)->save($data);
        if(!$member){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'更新用户信息失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'更新用户信息成功'));
    }
    /**
    *修改密码
    */
    public function updatePwdBymId(){
        $model = D('Member');
        $mId = $_SESSION["memberId"];
        $oldPwd = I('oldPwd');
        $newPwd = I('newPwd','',md5);
        $repwd = I('rePwd','',md5);
        if($mId==""||$oldPwd==""||$newPwd==""||$repwd==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $map['id'] = array('eq',$mId);
        $member = $model->where($map)->find();
        if(!$member){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取用户信息失败'));
        }
        if($member['pwd']!=md5($oldPwd)){
            $this->ajaxReturn(array('code'=>-2002,'msg'=>'原密码不正确，请重新输入'));
        }
        if($newPwd!=$repwd){
            $this->ajaxReturn(array('code'=>-2003,'msg'=>'两次输入的密码不正确，请重新输入'));
        }
        
        $data['pwd'] = $newPwd;
        $member = $model->where($map)->save($data);
        if($member !== 0 && !$member){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'密码修改失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'密码修改成功'));
    }
    /**
    *发布商品(代购才能发布)
    */
    public function addGoodInfo(){
        $model = D('Goods');
        $name = I('name');
        $typeId = I('typeId');
        $imgs =I('imgs');
        $mPrice = I('mPrice');
        $vPrice = I('vPrice');
        $gPrice = I('gPrice');
        $stock = I('stock');
        $com = I('mId');  //当前登录的用户id
        $des = I('des');
        if($name==""||$typeId==""||$imgs==""||$mPrice==""||$vPrice==""||$gPrice==""||$stock==""||$des==""||$com==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $data['name'] = $name;
        $data['typeId'] = $typeId;
        $data['imgs'] = $imgs;
        $data['marketPrice'] = $mPrice;
        $data['groupPrice'] = $gPrice;
        $data['vipPrice'] = $vPrice;
        $data['stock'] = $stock;
        $data['desc'] = $des;
        $data['commodityUser'] = $com;
        $res = $model->add($data);
//        $debug = $model->getLastSql();
        if(!$res){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'发布失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'发布成功'));
    }
    /**
    *商品列表(根据当前登录的代购人id查询发布的商品--查看审核结果)
    */
    public function getGoodInfosBymId(){
        $model = D('Goods');
        $com = I('mId',$_SESSION['memberId']);  //当前登录的用户id
        if($com==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $map['commodityUser'] = array('in',$com);
        $goods = $model->where($map)->select();
        if(!$goods){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取商品列表失败'));
        } 
        $this->assign("list",$goods);
        //$this->ajaxReturn(array('code'=>2000,'msg'=>'获取商品列表成功','data'=>array('goods'=>$goods)));
    }
    /**
    *商品列表(更新商品的上架和下架情况)--传入参数status：1上架 2下架
    */
    public function updateGoodStatusBygId(){
        $model = D('Goods');
        $gId = I('gId');  //当前选择的商品id
        $status = I('status',1);
        if($gId==""||$status==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $map['id'] = array('eq',$gId);
        $data['status'] = $status;
        $data['updatetime'] = time(0);
        $res = $model->where($map)->save($data);
        $debug = $model->getLastSql();
        if(!$res){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'更新商品状态失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'更新商品状态成功','ee'=>$debug));
    }
    /**
    *投诉建议(添加一条数据)
    */
    public function addSuggess(){
        $model = D('Suggess');
        $mId = $_SESSION["memberId"];
        $title = I('title');
        $desc = I('desc');
        $name = I('name');
        $tel = I('tel');
        if($mId==""||$title==""||$desc==""||$name==""||$tel==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $data['memberId'] = $mId;
        $data['title'] = $title;
        $data['desc'] = $desc;
        $data['name'] = $name;
        $data['tel'] = $tel;
        $res = $model->add($data);
        if(!$res){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'新增建议失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'新增建议成功'));
    }
    /**
    *销售情况（传入代购发布成功并审核通过的商品id）--查询购物车里？？--total--总金额--查看过往销量？？
    */
    public function getSellInfos(){
        $model = D('Goods');
        $com = I('mId');  //当前登录的用户id
        if($com==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $map['commodityUser'] = array('in',$com);
        $map['checkstatus'] = array('eq',3);
        $goods = $model->where($map)->select();
        if(!$goods){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取商品列表失败'));
        }
//        $this->ajaxReturn(array('code'=>$goods));
        foreach($goods as $k=>$v){
            $goods[$k]['buycars'] = array(); //保存购物车里的信息
            $goods[$k]['total'] = "";           
            $goodId = $goods[$k]['id'];
            $model = D('Buycar');
            $map1['goodsId'] = array('eq',$goodId);
            $goodInfo = $model->where($map1)->select();
            $goodInfo['sum'] = "";      //每件商品总共的成交额
            foreach($goodInfo as $q=>$w){
                $goodInfo['sum'] += $goodInfo[$q]['subTotal']; 
                $goods[$k]['total'] = $goodInfo['sum'];
            }            
            $goods[$k]['buycars'] = $goodInfo;
            
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'查询成功','data'=>array('list'=>$goods)));
    }
    
    /**
    *文章(根据名称进行查找)
    */
    public function getArticleInfo(){
        $model = D('Type');
        $name = I('name');
        if($name==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
        }
        $map['m_type.name'] = array('eq',$name);
        $article = $model->where($map)->join('m_article ON m_article.typeId=m_type.id')->find();
        if(!$article){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'查询失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'查询成功','data'=>array('list'=>$article)));
    }
    /**
    *在线申请
    */
    public function submitApply(){
        $model = D('Memberagency');
        $mId = I('mId');  //当前登录的会员id
        $idenImg = I('img');
        $buyProve = I('prove');
        $createtime = time(0);
        if($mId==""||$idenImg==""||$buyProve==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $data['memberId'] = $mId;
        $data['idenImg'] = $idenImg;
        $data['buyProve'] = $buyProve;
        $data['createtime'] = $createtime;
        $res = $model->add($data);
        if(!$res){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'您的申请提交失败，请重新提交！'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'您的申请已提交，请耐心等待系统审核！'));
    }
    //我的代金券
    public function mycoupon(){
        //分页 
        $page = I('page',1,'intval');
        if($page<1){
            $page = 1;
        } 
        $pageSize = C('PAGE_SIZE');
        //
        //用户代金券关联表
        $mMemVoucher = M('Membervoucher');
        $mapMemVoucher['m_membervoucher.mId'] = array('eq',$_SESSION['memberId']);
        $all = $mMemVoucher->join('m_voucherinfo ON m_voucherinfo.id=m_membervoucher.vId')->field('m_membervoucher.vId,m_voucherinfo.*')->where($mapMemVoucher)->count();
        $totalPage = ceil($all/$pageSize); 
        if($page>=$totalPage){
            $page = $totalPage;
        } 
        $dMemVoucher = $mMemVoucher->join('m_voucherinfo ON m_voucherinfo.id=m_membervoucher.vId')->field('m_membervoucher.vId,m_voucherinfo.*')->where($mapMemVoucher)->page($page,$pageSize)->select();
        // print_r($mMemVoucher->getLastSql());
        // var_dump($dMemVoucher);
        $this->assign('list',$dMemVoucher);
        $this->assign('totalPage',$totalPage); 
        $this->assign('curP',$page);
        $this->display();
    }

    /**
     * 优惠券
     */
    public function showgoods(){
        $mvid = I('id');
        $model = M('Membervoucher');
        $map['m_membervoucher.id'] = array('eq',$mvid);
        $res = $model->where($map)->join('m_voucherinfo ON m_voucherinfo.id=m_membervoucher.vId')->field('m_membervoucher.id as memId,m_membervoucher.createtime as memCreatetime,m_voucherinfo.*')->find();

        $gIds = explode(',', $res['suitId']);
        $mGoods = M('Goods');
        $mapGoods['id'] = array('in',$gIds);
        $list = $mGoods->where($mapGoods)->select();
        $this->assign('list',$list);
		$this->display();

    }    
}
?>