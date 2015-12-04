<?php
/**
 * 代金券控制器
 */
class VoucherinfoAction extends BaseAction{
	/**
     * 默认页
     */
    public function index(){ 
        $model = D('Voucherinfo');
        if(!empty($model)){ 
            $list = $model->where($map)->order('id desc')->select();
            foreach ($list as $key => $value) {
            	$goodsArr = explode(',',$value['suitId']);
            	$temp = array();
            	foreach ($goodsArr as $k => $v) {
            		$temp[$k]['goodsId'] = $v;
            		$temp[$k]['goodsName'] = getField($v,'name','Goods');
            	} 
            	//var_dump($goodsArr);
            	$list[$key]['goodsArr'] = $temp;
            	$curTime = 2;
            	if(time()<$value['startTime']){
            		$curTime = 0;
            	}
            	if(time()>=$value['startTime'] && time()<=$value['endTime']){
            		$curTime = 1;
            	}
            	if(time()>$value['endTime']){
            		$curTime = -1;
            	}
            	$list[$key]['statusTime'] = $curTime;
            }
            // echo time();
            // var_dump($list);
            $this->assign('list',$list);
        }
        $this->display();
    }
    /**
	 * 添加商品 
	 */
	public function add(){
		//商品类型
		$mapType['status'] = array('eq',1);
		$mapType['parentId'] = array('eq',0);
		$typelist = getTable('Type',$mapType,2);  
		//目录树
		foreach ($typelist as $key => $value) {
			$typelist[$key]['treePathName'] = getTreeTypeName($value['id']);
		} 
		$this->assign('goodsType',$typelist);

		//获取子模块商品列表
		$mGoods = M('Goods');
		$mapG['status'] = array('eq',1);
		$listGoods = $mGoods->where($mapG)->select();
		$this->assign('listgoods',$listGoods); 
 

		$this->display();
	} 
	//insert
	public function insert(){
		$postArr = $_POST;
		//var_dump($postArr);

		 $model = D('Voucherinfo');
        if(false === $model->create()){
            $this->error('表单提交数据错误');
            // $this->appReturn(-6000,'表单提交数据错误');
        }
		/*
        $typeId2 = I('typeId2');
        if(!empty($typeId2)){
        	$model->typeId = $typeId2; 
        }*/
        $model->suitId = I('goodsId');
        $model->condition = '';
        $model->meetmoney = $model->money;
        $model->startTime = strtotime($model->startTime.' 00:00:00');
        $model->endTime = strtotime($model->endTime.' 23:59:59');
        $model->createtime = time();
        $list = $model->add();
        if(false !== $list){
            $this->redirect("Voucherinfo/index");
            // $this->appReturn(2000,'数据插入成功');
        }else{
            $this->error('数据插入失败');
            // $this->appReturn(-6002,'数据插入失败');
        }
	}
    /**
     * 编辑商品 
     */
    public function edit(){
        //商品类型
        $mapType['status'] = array('eq',1);
        $mapType['parentId'] = array('eq',0);
        $typelist = getTable('Type',$mapType,2);  
        //目录树
        foreach ($typelist as $key => $value) {
            $typelist[$key]['treePathName'] = getTreeTypeName($value['id']);
        }
		
		//获取子模块商品列表
		$mGoods = M('Goods');
		$mapG['status'] = array('eq',1);
		$listGoods = $mGoods->where($mapG)->select();
		foreach($listGoods as $key=>$val){ 
			$listGoods[$key]['suitIdArr'] = explode(',', $val['suitId']); 
		}
		$this->assign('listgoods',$listGoods); 

        $id = I('id');
        $model = M('Voucherinfo');
        $map['id'] = array('eq',$id);
        $list = $model->where($map)->find(); 
        $list['money'] = sprintf("%.2f",$list['money']);
        //目录树 
        $list['treePathName'] = getTreeTypeName($list['typeId']);   
        $list['treePathNameArr'] = explode('/', $list['treePathName']); 
        $list['treePathId'] = getTreeTypeId($list['typeId']); 
        $list['treePathIdArr'] = explode('/', $list['treePathId']); 
        $list['suitIdArr'] = explode(',', $list['suitId']); 
        //var_dump($list);
        $this->assign('goodsType',$typelist);
        $this->assign('list',$list);
 

        $this->display();
    } 
    //update
    public function update(){
        $postArr = $_POST;
        //var_dump($postArr);

         $model = D('Voucherinfo');
        // if(false === $model->create()){
        //     $this->error('表单提交数据错误');
        //     // $this->appReturn(-6000,'表单提交数据错误');
        // }
        $data['name'] = I('name');
		/*
        $data['typeId'] = I('typeId1');
        $typeId2 = I('typeId2');
        if(!empty($typeId2)){
            $data['typeId'] = $typeId2; 
        }*/
        $data['suitId'] = I('goodsId');
        $data['condition'] = '';
        $data['meetmoney'] = sprintf("%.2f",$postArr['money']);
        $data['money'] = sprintf("%.2f",$postArr['money']);
        $data['startTime'] = strtotime($postArr['startTime']);
        $data['endTime'] = strtotime($postArr['endTime']." 23:59:59");
        $data['update'] = time();
        $map['id'] = array('eq',$postArr['id']);
        $list = $model->where($map)->save($data);
        // var_dump($model->getLastSql());
        // var_dump($list);exit;
        if(false !== $list){
            $this->redirect("Voucherinfo/index");
            // $this->appReturn(2000,'数据插入成功');
        }else{
            $this->error('数据修改失败');
            // $this->appReturn(-6002,'数据插入失败');
        }
    }
	//show
	public function show(){
		 $model = D('Voucherinfo');
		 $map['id'] = array('eq',$_REQUEST['id']);
		 $list = $model->where($map)->find(); 

    	$goodsArr = explode(',',$list['suitId']);
    	$temp = array();
    	foreach ($goodsArr as $k => $v) {
    		$temp[$k]['goodsId'] = $v;
    		$temp[$k]['goodsName'] = getField($v,'name','Goods');
    	} 
    	$list['goodsArr'] = $temp;
    	 //var_dump($list);

		 $this->assign('list',$list);
		 $this->display();
	}
	//sendvoucher
	public function sendvoucher(){
		//代金券
		$id = I('id');
		$mVoucherinfo = M('Voucherinfo');
		$mapVoucher['id'] = array('eq',$id);
		$dVoucherinfo = $mVoucherinfo->where($mapVoucher)->find();
		$this->assign('dVoucherinfo',$dVoucherinfo);

		//用户
		$mUser = M('Member');
		$mapUser['status'] = array('eq',1);
		$dUser = $mUser->where($mapUser)->select();
		$this->assign('dUser',$dUser);

		$this->display();
	}

	//sendinsert
	public function sendinsert(){
		$userid = I('userId');
		$voucherid = I('id'); 
		$model = M('Membervoucher'); 
		$data['mId'] = $userid;
		$data['vId'] = $voucherid;
		$data['createtime'] = time();
		$res = $model->add($data);
		if($res){
            $this->redirect("Voucherinfo/index");
		}else{
            $this->error('赠送代金券失败');
		}

	}

}
?>