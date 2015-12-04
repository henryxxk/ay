<?php
/**
* 定时任务
*/
class CronAction extends Action{
	/**
	* 定时代购商品统计
	*/	
	public function countDay(){
		//代购用户
		// $mMember = M('Member');
		// $mapMember['isReplaceBuy'] = array('eq',3); //代购
		// $dMember = $mMember->where($map)->select();
		//代购用户的商品
		$model = M('Goods');
		$map['status'] = array('eq',1); //只统计上架商品
		$map['commodity'] = array('eq',1); //只统计代购商品
		// foreach ($dMember as $key => $value) {
			// $map['commodityUser'] = array('eq',$value['id']);
			$list = $model->where($map)->select();
		// }
		// var_dump($list);
		$mOrder = M('Orderinfo');
		$data = array();	
		foreach ($list as $key => $value) {
			$data[$key]['mId'] = $value['commodityUser'];
			$data[$key]['gId'] = $value['id'];
			$data[$key]['sales'] = $value['sales'];
			//商品订单成交额统计
			$mapOrder['goodsId'] = array('eq',$value['id']);
			$order = $mOrder->where($mapOrder)->select();
			$sum = 0;
			$tmp = array();
			foreach ($order as $k => $v) {
				$sum = sprintf("%.2f",$sum + $v['goodsNum'] * $v['goodsPrice']);	//单价 * 数量 
				$tmp[] = $sum .'  '. $v['goodsNum']  .' * '. $v['goodsPrice'];
			}
			$data[$key]['tmp'] = $tmp;
			$data[$key]['bargain'] = $sum;
			$data[$key]['createtime'] = time();		
		}	
// var_dump($data);
		$mCount = M('Countgoods');
		$mCount->addAll($data);
	}
}
?>