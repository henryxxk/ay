<?php
/**
 * 系统设置控制器
 */
class SysconfigAction extends BaseAction{ 	
	/**
	 * 商品分类
	 */
	public function banner(){ 
		$model = M('Sysconfig');
		//Banner数据
		$map['their'] = array('eq','banner'); //数据库中手动指定的ID
		$list = $model->where($map)->order('sort desc')->select();  
		$this->assign('list',$list); 
		//hatword数据
		$hatword = $model->where('id=3')->find();
		$this->assign('hatword',$hatword);	
		//subModel数据 首页子模块
		$mType = M('Type');
		$mapType['status'] = array('eq',1);
		$mapType['parentId'] = array('eq',0);
		$listType = $mType->where($mapType)->select();
		$this->assign('submodelList',$listType);
		//获取子模块列表
		$mapT['status'] = array('eq',1);
		$mapT['isIndex'] = array('eq',1);
		$listsubModel = $mType->where($mapT)->select();
		$this->assign('listsubModel',$listsubModel);
		//获取子模块商品列表
		$mGoods = M('Goods');
		$mapG['status'] = array('eq',1);
		$listGoods = $mGoods->where($mapG)->select();
		$this->assign('listgoods',$listGoods);
		// print_r($mGoods->getLastSql());
		// var_dump($listGoods);
		// var_dump($listsubModel); 
		//hatBrand数据
		$map['their'] = array('eq','hatBrand'); //数据库中手动指定的ID
		$listHatBrand = $model->where($map)->order('sort desc')->select();  
		$this->assign('listHatBrand',$listHatBrand); 



		$this->display();	
	}
	//入库
	public function bannerAdd(){
		// $id = I('id');
		// $map['id'] = array('eq',$id);

		$data['their'] = I('their');
		$data['imgs'] = I('imgs');
		$data['title'] = I('title');
		$data['url'] = I('url');
		$data['sort'] = I('sort',0,'intval');

		$str = I('content');
		if(!empty($str)){
			$str = str_replace('，',',',$str);	//处理中文逗号
			$str = str_replace(' ','',$str);	//处理空格 
			$strArr = explode(',', $str); 
			$newStr = '';
			foreach ($strArr as $key => $value) {
				if($key<6){
					$newStr .= $value.',';
				}else{
					break;
				}
			}
			$newStr = rtrim($newStr,',');
			$data['content'] = $newStr;
		}
		$data['createtime'] = time();

		$model = M('Sysconfig');
		$res = $model->add($data);
		$debug = $model->getLastSql();
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','data'=>$data,'debug'=>$debug));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败','data'=>$data,'debug'=>$debug));
		} 
	}
	public function banneredit(){
		$id = I('id');
		$map['id'] = array('eq',$id); 

		$model = M('Sysconfig');
		$res = $model->where($map)->find(); 
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','data'=>$res));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
		} 
	} 
	//update
	public function bannerUpdate(){
		$id = I('id');
		$map['id'] = array('eq',$id);

		$data['their'] = I('their');
		$imgs = I('imgs');
		if(!empty($imgs)){
			$data['imgs'] = $imgs;
		}
		$title = I('title');
		if(!empty($title)){
			$data['title'] = $title;
		}
		$url = I('url');
		if(!empty($url)){
			$data['url'] = $url;
		}
		$str = I('content');
		if(!empty($str)){
			$str = str_replace('，',',',$str);	//处理中文逗号
			$str = str_replace(' ','',$str);	//处理空格 
			$strArr = explode(',', $str); 
			$newStr = '';
			foreach ($strArr as $key => $value) {
				if($key<8){
					$newStr .= $value.',';
				}else{
					break;
				}
			}
			$newStr = rtrim($newStr,',');
			$data['content'] = $newStr;
		}
		$data['sort'] = I('sort',0,'intval');
		$data['updatetime'] = time();

		$model = M('Sysconfig');
		$res = $model->where($map)->save($data);
		$debug = $model->getLastSql();
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','data'=>$data,'debug'=>$debug));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败','data'=>$data,'debug'=>$debug));
		} 
	}
	/**
	 * 热词编辑
	 */
	public function edithatword(){
		$str = I('content');
		$str = str_replace('，',',',$str);	//处理中文逗号
		$str = str_replace(' ','',$str);	//处理空格
		$strArr = explode(',', $str); 
		$newStr = '';
		foreach ($strArr as $key => $value) {
			if($key<5){
				$newStr .= $value.',';
			}else{
				break;
			}
		}
		$newStr = rtrim($newStr,',');
		$data['content'] = $newStr;
		$data['updatetime'] = time();
		$model = M('Sysconfig');
		$res = $model->where('id=3')->save($data);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','data'=>$newStr,'size'=>count($strArr)));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
		} 
	}


	/**
	 * vip 和 积分兑换 的静态banner图片设置
	 */
	public function banner2(){ 
		$model = M('Sysconfig');
		//Banner数据
		//$map['their'] = array('eq','banner2'); //数据库中手动指定的ID
		$map['id'] = array('eq',9);
		$list = $model->where($map)->find();  
		//var_dump($list);
		$this->assign('list',$list); 
		$map['id'] = array('eq',10);
		$list2 = $model->where($map)->find();  
		//var_dump($list2);
		$this->assign('list2',$list2); 
 
		$this->display();	
	}
	/**
	 * 子模块新增
	 */
	public function subModelType(){
		$id = I('id',0,'intval');
		$img = I('img');
		$isIndex = I('isIndex');
		$sort = I('scort',0,'intval');
		$url = I('url');
		$content = I('content');

		$model = M('Type');
		$map['id'] = array('eq',$id);
		$data['img'] = $img;
		$data['isIndex'] = 1;
		$data['scort'] = $sort;
		$data['url'] = $url;
		$data['remark'] = $content;
		$data['updatetime'] = time();
		$res = $model->where($map)->save($data);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'添加成功'));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'添加失败'));
		}
	}
	/**
	 * 子模块编辑
	 */
	public function subModelTypeEdit(){
		$id = I('id',0,'intval'); 
		$model = M('Type');
		$map['id'] = array('eq',$id);
		$list = $model->where($map)->find();
		$list['selectId'] = getField2($list['name'],'id','Type',true,'name');
		$goodsArr = explode(',', $list['remark']);
		$tmp = array();
		foreach ($goodsArr as $key => $value) {
			$tmp[$key]['id'] = getField($value,'id','Goods');
			$tmp[$key]['name'] = getField($value,'name','Goods');
		}
		$list['remarkArr'] = $tmp;
		if($list){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'获取成功','data'=>$list));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'获取失败'));
		}
	}
	//删除子模块中的指定商品
	public function subModelTypeGoodsDel(){
		$gid = I('gid',0,'intval'); 
		$id = I('id',0,'intval'); 
		$model = M('Type');
		$map['id'] = array('eq',$id);
		$list = $model->where($map)->find();
		$debug[] = $model->getLastSql();
		$oldGoodsArr = explode(',', $list['remark']);
		$tmp = array();
		foreach ($oldGoodsArr as $key => $value) {
			if($value != $gid){
				$tmp[] = $value;
			}
		}
		$data['remark'] = implode(',', $tmp);
		$data['updatetime'] = time();
		$res = $model->where($map)->save($data);
		$debug[] = $model->getLastSql();
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'修改成功','bug'=>$debug));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'修改失败','bug'=>$debug));
		}
	}
	//修改子模块
	public function subModelTypeEditUpdate(){ 
		$id = I('id',0,'intval');
		$img = I('img');
		$sort = I('scort',0,'intval');
		$url = I('url');
		$content = I('remark');

		$model = M('Type');
		$map['id'] = array('eq',$id);
		if(!empty($img)){
			$data['img'] = $img;
		}
		$data['isIndex'] = 1;
		if(!empty($sort)){
			$data['scort'] = $sort;
		}
		if(!empty($url)){
			$data['url'] = $url;
		}
		if(!empty($content)){
			$data['remark'] = $content;
		}
		$data['updatetime'] = time();
		$res = $model->where($map)->save($data);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'修改成功'));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'修改失败'));
		}
	}
	 
	/**
	 * 删除
	 */
	public function delete2(){
		$id = I('id');
		$model = M('Type');
		$map['id'] = array('eq',$id);
		$data['isIndex'] = 0;
		$data['url'] = '';
		$data['scort'] = 0;
		$data['img'] = '';
		$data['remark'] = '';
		$res = $model->where($map)->save($data);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'删除成功'));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'删除失败'));
		}
	}

	/**
	* 关税设置
	*/
	public function tariffindex(){
		$model = M('Taxrate');
		if(empty($_GET['p'])){
	                    $p = 1;
	            }else{
	                    $p = $_GET['p'];
	            }
		$list = $model->page($p.',10')->select();
		$this->assign('list',$list);
		 import('ORG.Util.Page');// 导入分页类
	             $count = $model->count();// 查询满足要求的总记录数
	             $Page = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
	             $show = $Page->show();// 分页显示输出
	             $this->assign('page',$show);// 赋值分页输出
		$this->display();
	}
	/**
	* vip活动的开关
	*/
	public function vipOpen(){
		$model = M('Sysconfig');
		$their = 'vipCode';
		$status = I('status');
		$map['their'] = array('eq',$their);
		$res = $model->where($map)->find();
		if($res && $res !== null){
			$dataVip['status'] = $status;
			$dataVip['updatetime'] = time(0);
			$res = $model->where('id='.$res['id'])->save($dataVip);
			if($res && $res !== null){
				$this->ajaxReturn(array('code'=>0,'msg'=>'update vip success!'));
			}else{
				$this->ajaxReturn(array('code'=>-6001,'msg'=>'update vip error!'));
			}
		}else{
			$data['their'] = $their;
			$data['createtime'] = time(0);
			$data['status'] = $status;
			$res = $model->add($data);
			if($res && $res !== null){
				$this->ajaxReturn(array('code'=>0,'msg'=>'update vip success!'));
			}else{
				$this->ajaxReturn(array('code'=>-6000,'msg'=>'add vipCode error!'));
			}
		}
	}

}
?>