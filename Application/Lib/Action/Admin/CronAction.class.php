<?php
/**
* 定时任务
*/
class CronAction extends Action{
	//获取分类id
	public function getTypeId(){ 
		$mType = M('Type');
		//大类
		$mapType['status'] = array('eq',1);
		$mapType['parentId'] = array('eq',0);
		$parentType = $mType->where($mapType)->select();
		//子类
		foreach ($parentType as $key => $value) {
			$mapType['parentId'] = array('eq',$value['id']);
			$tmp = $mType->where($mapType)->field('id')->select(); 
			$str = '';
			foreach ($tmp as $k => $v) {
				$str .= $v['id'].',';
			}
			$str = rtrim($str,','); 
			$tmp = $value['id'].','.$str;
			$parentType[$key]['subId'] = $tmp; 
		}
		return $parentType;
	}
	/**
	* 每天统计大类下商品的销售情况
	*/
	public function countClassify(){
		$mGoods = M('Goods');
 		$mapGoods['status'] = array('eq',1);

 		$parentType = $this->getTypeId();

 		$mCountclassify = M('Countclassify');
 		$list = array();
 		foreach ($parentType as $key => $value) {
 			$mapGoods['typeId'] = array('in',$value['subId']);
 			$list[$key]['typeId'] = $value['id'];
 			$list[$key]['typeName'] = $value['name'];
 			$list[$key]['typeIdStr'] = $value['subId'];
 			$list[$key]['goodsCount'] = $mGoods->where($mapGoods)->count();;
 			$list[$key]['sales'] = $mGoods->where($mapGoods)->sum('sales');
 			$topGoods = $mGoods->where($mapGoods)->order('sales desc')->limit(1)->find();
 			$list[$key]['topSales'] = $topGoods['sales'];
 			$list[$key]['topSalesId'] = $topGoods['id'];
 			$list[$key]['topSalesName'] = $topGoods['name'];
 			//$list[$key]['salesGoods'] = $topGoods;
 			//$list[$key]['sales'] = $mGoods->where($mapGoods)->order('sales desc')->limit(10)->select();
 			$list[$key]['createtime'] = time();
 			@ $res = $mCountclassify->add($list[$key]);
 		}
		//var_dump($list);
	}
}
?>