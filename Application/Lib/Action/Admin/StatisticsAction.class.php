<?php
/**
 * 统计控制器
 */
class StatisticsAction extends BaseAction{
		/**
		 * 销量清空
		 */
		public function sales(){
			//获取上周时间区间
			 $flag = date("w");
			 switch($flag)
			 {
			  case 0:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-13,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-7,date("Y")));
			   break;
			  case 1:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-7,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-1,date("Y")));
			   break;
			  case 2:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-8,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-2,date("Y")));
			   break;
			  case 3:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-9,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-3,date("Y")));
			   break;
			  case 4:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-10,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-4,date("Y")));
			   break;
			  case 5:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-11,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-5,date("Y")));
			   break;
			  case 6:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-12,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-6,date("Y")));
			   break;
			 }
			 // echo $week_start.'<br>';
			 // echo $week_end.'<br>';

 			$mCountclassify = M('Countclassify');
 			$mapCount['createtime'] =  array(array('egt',$week_start),array('elt',$week_end)) ;
 			$dCountclassify = $mCountclassify->where($mapCount)->select();
 			 
			$this->assign('dataName',echartData($dCountclassify,"typeName",''));
			// $this->assign('dataScore',echartData($dCountclassify,"sales",''));

			// $str = "{name:'最高气温',type:'line',".
			// 		"data:[11, 11, 15, 13, 12, 13, 10],".
			// 		"markPoint : {data : [{type : 'max', name: '最大值'},{type : 'min', name: '最小值'}]},".
   //                  "markLine : {data : [{type : 'average', name: '平均值'}]}}";
			$newData = '';
			foreach ($dCountclassify as $key => $value) {
				$newData .= "{name:'".$value['typeName']."',type:'line',";
				$dStr = '';
				foreach ($dCountclassify as $k => $v) {
					if($v['id'] == $value['id']){
						$dStr .= $v[sales].',';
					}
				}
				$dStr = rtrim($dStr,',');
				$newData .= "data:[".$dStr."],".
					"markPoint : {data : [{type : 'max', name: '最大值'},{type : 'min', name: '最小值'}]},".
                    "markLine : {data : [{type : 'average', name: '平均值'}]}},";
			}
			$this->assign('dataScore',$newData);
			// var_dump($dCountclassify);
			// var_dump($newData);
			// var_dump(echartData($dCountclassify,"typeName",''));
			// var_dump(echartData($dCountclassify,"sales",''));

			$this->display();
		}
		/**
		 * 会员排行
		 */
		public function hotmember(){
			$model = M('Member');
			//男
			$mapMan['status'] = array('eq',1);
			$mapMan['sex'] = array('eq','男');
			$man = $model->where($mapMan)->order('score desc')->limit(10)->select(); 
			$this->assign('manName',echartData($man,"name",'No.'));
			$this->assign('manScore',echartData($man,"score",''));
			//女
			$mapWoman['status'] = array('eq',1);
			$mapWoman['sex'] = array('eq','女');
			$woman = $model->where($mapWoman)->order('score desc')->limit(10)->select(); 
			$this->assign('womanName',echartData($woman,"name",'No.'));
			$this->assign('womanScore',echartData($woman,"score",''));
 
 			//不分男女
			$mapAll['status'] = array('eq',1); 
			$all = $model->where($mapAll)->order('score desc')->limit(20)->select(); 
			$this->assign('dataName',echartData($all,"name",'No.'));
			$this->assign('dataScore',echartData($all,"score",''));
			$this->display();
		}
		/**
		 * 销量排行
		 */
		public function hotsales(){
			// $model = M('Goods');
			// $map['status'] = array('eq',1);
			// $list = $model->where($map)->order('sales desc')->limit(10)->select(); 
			// $this->assign('dataName',echartData($list,"goodsNum",''));
			// $this->assign('dataScore',echartData($list,"sales",'')); 
			// var_dump(echartData($list,"name",''));
			// var_dump(echartData($list,"sales",''));

			//获取上周时间区间
			 $flag = date("w");
			 switch($flag)
			 {
			  case 0:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-13,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-7,date("Y")));
			   break;
			  case 1:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-7,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-1,date("Y")));
			   break;
			  case 2:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-8,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-2,date("Y")));
			   break;
			  case 3:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-9,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-3,date("Y")));
			   break;
			  case 4:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-10,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-4,date("Y")));
			   break;
			  case 5:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-11,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-5,date("Y")));
			   break;
			  case 6:
			   $week_start = date("U",mktime(0,0,0,date("m"),date("d")-12,date("Y")));
			   $week_end = date("U",mktime(23,59,59,date("m"),date("d")-6,date("Y")));
			   break;
			 }
			 // echo $week_start.'<br>';
			 // echo $week_end.'<br>';

 			$mCountclassify = M('Countclassify');
 			$mapCount['createtime'] =  array(array('egt',$week_start),array('elt',$week_end)) ;
 			$dCountclassify = $mCountclassify->where($mapCount)->order('sales desc')->select();
 			
			$this->assign('dataName',echartData($dCountclassify,"typeName",'No.'));
			$this->assign('dataScore',echartData($dCountclassify,"sales",''));
			// var_dump(echartData($dCountclassify,"typeName",''));
			// var_dump(echartData($dCountclassify,"sales",''));

 			// $this->assign('dCountclassify',$dCountclassify);
 			// print_r($mCountclassify->getLastSql());
 			// var_dump($dCountclassify);
			$this->display();
		}
}
?>