<?php
// 本类由系统自动生成，仅供测试用途
class ZhiFuAction extends Action {
	public function index(){
	    	$this->assign('name','aaa');
	    	$this->display();
	}

	public function test(){
		$this->redirect('GoodInfo/payover');
		// $this->ajaxReturn(array('code'=>0,'msg'=>'success'));
	}

	public function zhifuSucc(){
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
			$goods =$model->where($mapQ)->find();
			$oldnum =$goods["stock"];
			$newnum = $oldnum - $xGnum;
			$dataw["stock"] = $newnum;
			$res = $model->where($mapQ)->save($dataw);
		}
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
		            $goods[$k]['imgsArr'] = $hotimgArr;
		}
		$this->assign('goods',$goods);
		$this->assign('orderNum',$res);
		$this->assign('shopScore',$res1);
		$this->assign('orderId',$orderId);
		$this->redirect('GoodInfo/payover');
	}

	
}
?>