<?php
/**
 * 积分控制器
 */
class ScoreAction extends BaseAction{  
    /**
     * 默认页
     */
    public function index(){ 
        $tag = I('tag',1,'intval');
        $map['isOrNoExchange'] = array('eq',$tag);
        $map['status'] = array('in',"1,2");
        $model = D("Goods");
        if(!empty($model)){
            $list = $model->where($map)->order('id desc')->select();
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
        //海关计量单位
        $unitlist = getTable('Unit',array(),2); 
        $this->assign('Unit',$unitlist);  //UNIT_CODE UNIT_NAME
        //海关产国
        $countrylist = getTable('Country',array(),2); 
        $this->assign('countrylist',$countrylist); //COUNTRY_CODE COUN_C_NAME  COUN_E_NAME
        //海关币制
        $currlist = getTable('Curr',array(),2); 
        $this->assign('currlist',$currlist); //CURR_CODE CURR_SYMB  CURR_NAME
        //海关税率表 
        $taxratelist = getTable('Taxrate',$mapTaxrate,2); 
        $this->assign('taxratelist',$taxratelist);
		//代购用户
		$mapUser['status'] = array('eq',1);
		$mapUser['isReplaceBuy'] = array('eq',3);
		$userlist = getTable('Member',$mapUser,2); 
		$this->assign('userlist',$userlist);
		//VIP级别
		$mapViplevel['status'] = array('eq',1); 
		$listViplevel = getTable('Viplevel',$mapViplevel,2); 
		$this->assign('listViplevel',$listViplevel);

		$this->display();
	} 
	/**
     * 插入表数据
     */
    public function insert(){
        $model = D('Goods');
        if(false === $model->create()){
            $this->error('表单提交数据错误');
            // $this->appReturn(-6000,'表单提交数据错误');
        }
        $typeId2 = I('typeId2');
        if(!empty($typeId2)){
        	$model->typeId = $typeId2; 
        }
        $commodity = I('commodity');
        if($commodity != 2){
        	$model->commodityUser = 0;
        }
        $isOrNoExchange = I('isOrNoExchange');
        if($isOrNoExchange == 1 || $isOrNoExchange == 2){
        	$model->groupPrice = 0.00;
        	$model->vipPrice = 0.00;
        }
        if($isOrNoExchange == 0){
        	$model->score = 0;
        	$model->viplevelId = 0;
        }else if($isOrNoExchange == 1 || $isOrNoExchange == 3){
        	$model->viplevelId = 0;
        }

        $content = I('content');
        $model->desc = serialize($content);
        $model->createtime = time();
        $list = $model->add();
        if(false !== $list){
            //生成备案商品xml文件
            $this->createGoodsXml($_POST,1);
            $this->redirect("Score/index");
            // $this->appReturn(2000,'数据插入成功');
        }else{
            $this->error('数据插入失败');
            // $this->appReturn(-6002,'数据插入失败');
        }
    }
    /**
     * 编辑页面
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
		$this->assign('goodsType',$typelist); 
        //海关计量单位
        $unitlist = getTable('Unit',array(),2); 
        $this->assign('Unit',$unitlist);  //UNIT_CODE UNIT_NAME
        //海关产国
        $countrylist = getTable('Country',array(),2); 
        $this->assign('countrylist',$countrylist); //COUNTRY_CODE COUN_C_NAME  COUN_E_NAME
        //海关币制
        $currlist = getTable('Curr',array(),2); 
        $this->assign('currlist',$currlist); //CURR_CODE CURR_SYMB  CURR_NAME
        //海关税率表 
        $taxratelist = getTable('Taxrate',$mapTaxrate,2); 
        $this->assign('taxratelist',$taxratelist);
		//代购用户
		$mapUser['status'] = array('eq',1);
		$mapUser['isReplaceBuy'] = array('eq',3);
		$userlist = getTable('Member',$mapUser,2); 
		$this->assign('userlist',$userlist);
		
		//获取vip级别
		$mapViplevel['status'] = array('eq',1);
		$Viplevellist = getTable('Viplevel',$mapViplevel,2); 
		$this->assign('Viplevellist',$Viplevellist);  

		//编辑
    	$id = I('id',0,'intval');
        $model = D('Goods');
        $where['id'] = array('eq',$id);
        $list = $model->where($where)->find();
        $list['content'] = unserialize($list['desc']);
        $list['content'] = htmlspecialchars_decode($list['content']);
		//目录树 
		$list['treePathName'] = getTreeTypeName($list['typeId']);   
		$list['treePathNameArr'] = explode('/', $list['treePathName']); 
		$list['treePathId'] = getTreeTypeId($list['typeId']); 
		$list['treePathIdArr'] = explode('/', $list['treePathId']);
        //var_dump($list);
        if($list){
            $this->assign('list',$list);
        }else{
            $this->error('获取编辑数据失败');
        }
        $this->display();
    }  
    /**
     * 更新表数据
     */
    public function update(){ 
        $model = D('Goods');
        if(false === $model->create()){
            $this->error('表单提交数据错误');
            // $this->appReturn(-6000,'表单提交数据错误');
        } 
        $typeId2 = I('typeId2');
        if(!empty($typeId2)){
        	$model->typeId = $typeId2; 
        }
        $content = I('content');
        $model->desc = serialize($content);
        //$model->desc = urldecode($content);
        $model->updatetime = time();
        //var_dump($model);exit;
        $list = $model->save();
        if(false !== $list){
            //生成备案商品xml文件
            $this->createGoodsXml($_POST,2);
            $this->redirect("Score/index");
            // $this->appReturn(2000,'数据编辑成功');
        }else{
            $this->error('数据编辑失败');
            // $this->appReturn(-6002,'数据编辑失败');
        }
    }
     /**
     * 详情页面
     */
    public function show(){  
		//编辑
    	$id = I('id',0,'intval');
        $model = D('Goods');
        $where['id'] = array('eq',$id);
        $list = $model->where($where)->find();
        $list['content'] = unserialize($list['desc']);
        $list['content'] = htmlspecialchars_decode($list['content']);
		//目录树 
		$list['treePathName'] = getTreeTypeName($list['typeId']);   
		$list['treePathNameArr'] = explode('/', $list['treePathName']); 
		$list['treePathId'] = getTreeTypeId($list['typeId']); 
		$list['treePathIdArr'] = explode('/', $list['treePathId']);
        $list['imgArr'] = explode(',', $list['imgs']); 
        //var_dump($list);
        if($list){
            $this->assign('list',$list);
        }else{
            $this->error('获取编辑数据失败');
        }
        $this->display();
    }  


	/**
	 * 删除
	 */
	public function goodsDelete(){
		$table = I('table');
        $id = I('id',0,'intval');
        if($id < 1){
            $this->ajaxReturn(array('code'=>-6001,'msg'=>'请求参数错误'));
        }
        $map['id'] = array('eq',$id);
        $mType = M($table);
        if(!$mType){
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'数据表错误'.$mType));
        }
        $data['status'] = -1;
        if($mType->where($map)->save($data)){
            // $this->redirect('Auth/ruleIndex');
            $this->ajaxReturn(array('code'=>2000,'msg'=>'删除成功'));
        }else{
            // $this->error('删除失败');
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'删除失败'));
        }
    } 

    /**
    * 用户积分兑换记录
    **/
    public function index2(){
    	$model = M('Scoreexchangelog');
    	$list = $model->order('createtime desc')->select();
    	$this->assign('list',$list);
    	$this->display();
    }
    /**
     * 状态改变
     */
    public function statuschange(){
        $id = I('id',0,'intval');
        $status = I('status',0,'intval');
        $model = D('Goods');
        if(!empty($model)){ 
                $data['id'] =  array('eq',$id);
                // $data['status'] = 1;
                // if($status != 0){
                $data['status'] = $status;
                // }
                $data['updatetime'] = time();
                $list = $model->save($data);
                $debug = $model->getLastSql();
                $this->ajaxReturn(array('code'=>2000,'msg'=>'ok','debug'=>$debug));
                if(false !== $list){
                    $this->appReturn(2000,'数据删除成功'.$debug);
                }else{
                    $this->appReturn(-6002,'数据删除失败'.$debug);
                } 
        }// end if(!empty($model))
    }
    /**
    * 按积分浏览设置
    */
    public function setting(){ 
        $model = M('Sysconfig');
        $map['their'] = array('eq','code');
        $res = $model->where($map)->select();
        $this->assign('list',$res);
        $this->display();
    }
    //
    public function CodeSetting(){
        $id = I('id',0,'intval');
        $map['id'] = array('eq',$id);
        $map['their'] = array('eq','code');
        $data['title'] = I('code1',0,'intval');
        $data['content'] = I('code2');
        $data['updatetime'] = time();
        // if(empty($data['content'])){
        //     $data['title'] = $data['title'].'以上';
        // }
        $model = M('Sysconfig');
        $res = $model->where($map)->save($data);
        $debug = $model->getLastSql();
        if($res){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'修改成功','debug'=>$debug));
        }
        $this->ajaxReturn(array('code'=>-6002,'msg'=>'修改失败','debug'=>$debug));
    }




}
?>