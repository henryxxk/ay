<?php
/**
 * 商品控制器
 */
class GoodsAction extends BaseAction{  
    /**
     * 默认页
     */
    public function index(){
        $this->pageSize = C('PAGE_SIZE');
        $map = $this->_search();
        $behalf = I('tag');
        $map['status'] = array('egt',0);
        if(!empty($behalf) && $behalf == 'behalf'){
        	$map['commodity'] = array('eq',1); 
        }else{
        	//$map['commodity'] = array('eq',0); 
        }
		$typeOne = I('typeOne',0,'intval');
		$typeTwo = I('typeTwo',0,'intval');
		if($typeTwo>0){
			$map['typeId'] = array('eq',$typeTwo);
		}
        $tbName = $this->getActionName();
        $model = D($tbName);
        if(!empty($model)){
            //$this->_getList($model,$map);
            //获取全部数据 前端Dashboard会进行各种处理 分页 查询 排序 
            if(empty($_GET['p'])){
            		$p = 1;
            }else{
            		$p = $_GET['p'];
            }
            if (isset($_GET['key'])) {
                $map['name'] = array('like','%' . $_GET["key"] .'%');
            }
            $list = $model->where($map)->order('createtime desc')->page($p.',100')->select();
            foreach ($list as $key => $value) {
            	$list[$key]['imgArr'] = explode(',', $value['imgs']);
            }
            // var_dump($list);
            $this->assign('list',$list);

             import('ORG.Util.Page');// 导入分页类
	$count = $model->where($map)->order('createtime desc')->count();// 查询满足要求的总记录数
	$Page = new Page($count,100);// 实例化分页类 传入总记录数和每页显示的记录数
	$show = $Page->show();// 分页显示输出
	$this->assign('page',$show);// 赋值分页输出
 
	$mTypeOne = M('Type');
	$mapTypeOne['status'] = array('eq',1);
	$mapTypeOne['parentId'] = array('eq',0);
	$dTypeOne = $mTypeOne->where($mapTypeOne)->select();
             $this->assign('typeOne',$dTypeOne); 

	$this->assign('t1',$typeOne);
	$this->assign('t2',$typeTwo);
        }
        $this->display();
    }
	//获取子类
	public function typeTwoData(){
		$id = I('typeOneId');
		$mTypeOne = M('Type');
		$mapTypeOne['status'] = array('eq',1);
		$mapTypeOne['parentId'] = array('eq',$id);
		$dTypeOne = $mTypeOne->where($mapTypeOne)->select();
		$this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','data'=>$dTypeOne));
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
		// $taxratelist = getTable('Taxrate',$mapTaxrate,2); 
		$mTaxrate = M('Taxrate');
		$mapTa['parentId'] = array('eq',0);
		$taxratelist = $mTaxrate->where($mapTa)->select();
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
	$name = I('name'); 
        	$model->name = str_replace(' ', '&nbsp;', $name);
        	$model->unit->$unit;
        	$typeId2 = I('typeId2');
        	if(!empty($typeId2)){
        		$model->typeId = $typeId2; 
        	}
        	$commodity = I('commodity');
        	if($commodity != 1){
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
        	$img22 = I('imgs22');
        	if(!empty($img22)){
        		$model->imgs = $model->imgs.','.$img22;
        	}
        	$img33 = I('imgs33');
        	if(!empty($img33)){
        		$model->imgs = $model->imgs.','.$img33;
        	}
        	$img44 = I('imgs44');
        	if(!empty($img44)){
        		$model->imgs = $model->imgs.','.$img44;
        	} 
		$test = getField($model->taxrateId,'revenue','Taxrate');
		// dump($test);
        	$model->taxrate = (int)substr($test, 0,-1)/100;
        	$list = $model->add();
       	if(false !== $list){
       		//生成备案商品xml文件
       		// $this->createGoodsXml($_POST,1);
       		$this->goodsLog($list,1,$_POST);
            	$this->redirect("Goods/index");
            // $this->appReturn(2000,'数据插入成功');
        	}else{
            	$this->error('数据插入失败');
            	// $this->appReturn(-6002,'数据插入失败');
        	}
    }
    /**
     * 判断商品货号是否重复
     */
    public function decRepeat(){
    	$model = M('Goods');
    	$goodsNum = I('goodsNum');
    	$map['goodsNum'] = array('eq',$goodsNum);
    	// $map['status'] = array('eq',1);
    	$res = $model->where($map)->find();
    	if($res && $res !== null){
    		$this->ajaxReturn(array('code'=>2000,'msg'=>'goodsNum repeat!'));
    	}
    	$this->ajaxReturn(array('code'=>-6000,'msg'=>'goodsNum null!'));
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
		//海关税率表 
		// $taxratelist = getTable('Taxrate',$mapTaxrate,2); 
		// $this->assign('taxratelist',$taxratelist);
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
		// $taxratelist = getTable('Taxrate',$mapTaxrate,2); 
		$mTaxrate = M('Taxrate');
		$mapTa['parentId'] = array('eq',0);
		$taxratelist = $mTaxrate->where($mapTa)->select();
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
        $list['content'] = str_replace('\\', "", $list['content']);
		//目录树 
		$list['treePathName'] = getTreeTypeName($list['typeId']);   
		$list['treePathNameArr'] = explode('/', $list['treePathName']); 
		$list['treePathId'] = getTreeTypeId($list['typeId']); 
		$list['treePathIdArr'] = explode('/', $list['treePathId']); 
        $list['imgArr'] = explode(',', $list['imgs']); 
        // var_dump($list);
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
    	$id = I('id');
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
        $img22 = I('imgs22');
        if(!empty($img22)){
        	$model->imgs = $model->imgs.','.$img22;
        }
        $img33 = I('imgs33');
        if(!empty($img33)){
        	$model->imgs = $model->imgs.','.$img33;
        }
        $img44 = I('imgs44');
        if(!empty($img44)){
        	$model->imgs = $model->imgs.','.$img44;
        } 
		$test = getField($model->taxrateId,'revenue','Taxrate');
        $model->taxrate = (int)substr($test, 0,-1)/100;
        //var_dump($model);exit;
        $list = $model->save();
        if(false !== $list){
       		//生成备案商品xml文件
       		// $this->createGoodsXml($_POST,2);
       		$this->goodsLog($id,2,$_POST);
            $this->redirect("Goods/index");
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
            $this->assign('id',$id);
        }else{
            $this->error('获取编辑数据失败');
        }
        $this->display();
    }  
#-----------------------------------
#  代购模块
#----------------------------------  
    /**
     * 默认页
     */
    // public function index2(){
    //     $this->pageSize = C('PAGE_SIZE');
    //     $map = $this->_search();
    //     $map['commodity'] = array('eq',1); 
    //     $tbName = $this->getActionName();
    //     $model = D($tbName);
    //     if(!empty($model)){
    //         //$this->_getList($model,$map);
    //         //获取全部数据 前端Dashboard会进行各种处理 分页 查询 排序 
    //         $list = $model->where($map)->order($model->getPk().' desc')->select();
    //         // var_dump($list);
    //         $this->assign('list',$list);
    //     }
    //     $this->display();
    // }

#-----------------------
# 商品属性模块
#-----------------------	
	/**
	 * 商品属性列表公共部分
	 */
	public function goodsList(){ 
		$map['status'] = array('eq',1);

		//商品类型
		$model = D('Type');
		$typelist = $model->where($map)->select();  
		//目录树
		foreach ($typelist as $key => $value) {
			$typelist[$key]['treePathName'] = getTreeTypeName($value['id']);
		}
		//商品品牌
		$mBrand = M('Goodsbrand');
		$dBrand = $mBrand->where($map)->select();  
		//目录树
		foreach ($dBrand as $key => $value) {
			$dBrand[$key]['treePathName'] = getTreeTypeName($value['typeId']);
		}
		//商品颜色
		$mColor = M('Goodscolor');
		$dColor = $mColor->where($map)->select();  
		//目录树
		foreach ($dColor as $key => $value) {
			$dColor[$key]['treePathName'] = getTreeTypeName($value['typeId']);
		}
		//商品尺寸
		$mSize = M('Goodssize');
		$dSize = $mSize->where($map)->select();  
		//目录树
		foreach ($dSize as $key => $value) {
			$dSize[$key]['treePathName'] = getTreeTypeName($value['typeId']);
		} 

		//显示
		$this->assign('goodsType',$typelist);
		$this->assign('goodsBrand',$dBrand);
		$this->assign('goodsColor',$dColor);
		$this->assign('goodsSzie',$dSize);
	}
	/**
	 * 商品分类
	 */
	public function goodsType(){ 
		//$this->goodsList();
		$map['status'] = array('eq',1);
		$map['parentId'] = array('eq',0); 
		//商品类型
		$model = D('Type');
		$typelist = $model->where($map)->select();   
		foreach ($typelist as $key => $value) {
			$map['parentId'] = array('eq',$value['id']);
			$typelist[$key]['subArr'] = $model->where($map)->select();
			
		} 
		$this->assign('typelist',$typelist);
		$this->display();		
	}
	//添加商品分类
	public function addType(){
		$name = I('name');
		$parentId = I('parentId',0,'intval');

		if(!empty($name)){
			$data['name'] = $name;
		}
		$data['parentId'] = $parentId;
		$data['level'] = 1;
		if($parentId != 0){
			$data['level'] = 2;
		}
		$data['status'] = 1;
		$data['createtime'] = time();

		$model = M("Type");
		$res = $model->add($data);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'添加成功'));
		}
		$this->ajaxReturn(array('code'=>-6002,'msg'=>'添加失败'));
	}
	//保存商品分类
	public function saveGoodsType(){
		$id = I('id',0,'intval');
		$name = I('name');
		if(!empty($name)){
			$data['name'] = $name;
		}
		$model = M('Type');
		$map['id'] = array('eq',$id);
		$data['updatetime'] = time();
		$res = $model->where($map)->save($data);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'保存成功'));
		}
		$this->ajaxReturn(array('code'=>-6002,'msg'=>'保存失败'));
	}
	//删除商品分类
	public function deleteType(){
		$id = I('id',0,'intval'); 
		$model = M('Type');
		$map['id'] = array('eq',$id); 
		$data['status'] = -1;
		$res = $model->where($map)->save($data);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'删除成功'));
		}
		$this->ajaxReturn(array('code'=>-6002,'msg'=>'删除失败'));
	}
	//新增品牌
	public function addBtnGoodsbrand(){
		$bigTypeSel = I('bigTypeSel');
		$smallTypeSel = I('smallTypeSel');
		$goodsbrandText = I('goodsbrandText');

		$goodsbrandText = str_replace('，',',',$goodsbrandText);
		$goodsbrandText = str_replace(' ','',$goodsbrandText);
		$brandArr = explode(",",$goodsbrandText);
		$dataArr = array();
		foreach ($brandArr as $key => $value) { 
			$dataArr[$key]['typeId'] = $bigTypeSel;
			if(!empty($smallTypeSel)){
				$dataArr[$key]['typeId'] = $smallTypeSel;
			}
			$dataArr[$key]['name'] = $value; 
			$dataArr[$key]['createtime'] = time();
		}
 
		$model = M("Goodsbrand");
		$res = $model->addAll($dataArr);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'添加成功'));
		}
		$this->ajaxReturn(array('code'=>-6002,'msg'=>'添加失败'));
	}
	//新增颜色
	public function addBtnGoodscolor(){
		$bigTypeSel = I('bigTypeSel');
		$smallTypeSel = I('smallTypeSel');
		$goodsbrandText = I('goodsbrandText');

		$goodsbrandText = str_replace('，',',',$goodsbrandText);
		$goodsbrandText = str_replace(' ','',$goodsbrandText);
		$brandArr = explode(",",$goodsbrandText);
		$dataArr = array();
		foreach ($brandArr as $key => $value) { 
			$dataArr[$key]['typeId'] = $bigTypeSel;
			if(!empty($smallTypeSel)){
				$dataArr[$key]['typeId'] = $smallTypeSel;
			}
			$dataArr[$key]['name'] = $value; 
			$dataArr[$key]['createtime'] = time();
		}
 
		$model = M("Goodscolor");
		$res = $model->addAll($dataArr);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'添加成功'));
		}
		$this->ajaxReturn(array('code'=>-6002,'msg'=>'添加失败'));
	}
	//新增尺寸
	public function addBtnGoodssize(){
		$bigTypeSel = I('bigTypeSel');
		$smallTypeSel = I('smallTypeSel');
		$goodsbrandText = I('goodsbrandText');

		$goodsbrandText = str_replace('，',',',$goodsbrandText);
		$goodsbrandText = str_replace(' ','',$goodsbrandText);
		$brandArr = explode(",",$goodsbrandText);
		$dataArr = array();
		foreach ($brandArr as $key => $value) { 
			$dataArr[$key]['typeId'] = $bigTypeSel;
			if(!empty($smallTypeSel)){
				$dataArr[$key]['typeId'] = $smallTypeSel;
			}
			$dataArr[$key]['name'] = $value; 
			$dataArr[$key]['createtime'] = time();
		}
 
		$model = M("Goodssize");
		$res = $model->addAll($dataArr);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'添加成功'));
		}
		$this->ajaxReturn(array('code'=>-6002,'msg'=>'添加失败'));
	}



#--------------------



	//新增商品分类添加
	public function goodsTypeAdd(){
		$model = D('Type');
		$map['status'] = array('eq',1);
		$map['level'] = array('eq',1);
		$list = $model->where($map)->select();  
		// var_dump($list);
		$this->assign('goodsType',$list);
		$this->display();		
	}
	//商品分类入库
	public function goodsTypeAddInsert(){ 
		$parentId = I('parentId');
		$parentId2 = I('parentId2',0,'intval');
		$data['name'] = I('name');
		//级别
		if($parentId == 0){
			$data['parentId'] = $parentId;
			$data['level'] = 1;
			$data['treePath'] = 0;
		}else
		if(($parentId > 0) && ($parentId2 == 0)){
			$data['parentId'] = $parentId;
			$data['level'] = 2;
			$data['treePath'] = '0-'.$parentId1;
		}else
		if($parentId2 > 0){
			$data['parentId'] = $parentId2;
			$data['level'] = 3;
			$data['treePath'] = '0-'.$parentId2.'-'.$parentId2;
		}
 
		$model = D('Type');
		$res = $model->add($data);	
		if($res){
			$this->redirect('Goods/goodsType');
		}else{
			$this->error('添加失败');
		}
	}	 
	//新增商品分类修改
	public function goodsTypeEdit(){
		$id = I('id');
		//$model = D('Type');
		$map['id'] = array('eq',$id);
		$map['status'] = array('eq',1);
		$list = getTable('Type',$map);//$model->where($map)->find();  
		//目录树 
		$list['treePathName'] = getTreeTypeName($list['id']); 
		$list['treePathNameArr'] = explode('/', $list['treePathName']); 
		$list['treePathId'] = getTreeTypeId($list['id']); 
		$list['treePathIdArr'] = explode('/', $list['treePathId']); 

		$where['status'] = array('eq',1); 
		$goodsType = getTable('Type',$where,2);//$model->where($map)->select(); 
  
		$where['status'] = array('eq',1); 
		$where['level'] = array('eq',1); 
		$goodsTypeOne = getTable('Type',$where,2);//$model->where($map)->select(); 

		// var_dump($treePathName);
		 //var_dump($list);
		// var_dump($goodsType);
		$this->assign('list',$list);
		$this->assign('goodsTypeOne',$goodsTypeOne);
		$this->display();		
	}
	//商品分类入库
	public function goodsTypeUpdate(){ 
		$id = I('id');
		$map['id'] = array('eq',$id);
		$parentId = I('parentId');
		$parentId2 = I('parentId2',0,'intval');
		$name = I('name');

		//级别 
		if($parentId == 0){
			$data['parentId'] = $parentId;
			$data['level'] = 1;
			$data['treePath'] = 0;
		}else
		if(($parentId > 0) && ($parentId2 == 0)){
			$data['parentId'] = $parentId;
			$data['level'] = 2;
			$data['treePath'] = '0-'.$parentId1;
		}else
		if($parentId2 > 0){
			$data['parentId'] = $parentId2;
			$data['level'] = 3;
			$data['treePath'] = '0-'.$parentId2.'-'.$parentId2;
		}
		// if(!empty($parentId)){
		// 	$data['parentId'] = $parentId;
		// 	$data['level'] = 1;
		// 	$data['treePath'] = 0;
		// }
		// if($parentId2 > 0){
		// 	$data['parentId'] = $parentId2;
		// 	$data['level'] = 2;
		// 	$data['treePath'] = '0-'.$parentId2;
		// }
		if(!empty($name)){
			$data['name'] = $name;
		}
 		$data['updatetime'] = time();

		$model = D('Type');
		$res = $model->where($map)->save($data);	
		if($res){
			$this->redirect('Goods/goodsType');
		}else{
			$this->error('修改失败');
		}
	}	
	/**
	 * 商品品牌
	 */ 
	public function goodsBrand(){
		$this->goodsList();
		$this->display();
	}
	//添加
	public function goodsBrandAdd(){
		$model = D('Goodsbrand');
		$map['status'] = array('egt',0); 
		$list = $model->where($map)->select();  
		$this->assign('goodsBrand',$list);
		
		$model = D('Type');
		$map['status'] = array('eq',1);
		$map['level'] = array('eq',1);
		$list = $model->where($map)->select(); 
		$this->assign('goodsType',$list);

		$this->display();		
	}
	//新增入库
	public function goodsBrandInsert(){ 
		$typeId1 = I('typeId1');
		$typeId2 = I('typeId2');
		if(!empty($typeId2)){
			$data['typeId'] = $typeId2;
		}else{
			$data['typeId'] = $typeId1;
		}
		$data['name'] = I('name');
		$data['imgs'] = I('imgs');
		$data['status'] = 1;
 
		$model = D('Goodsbrand');
		$res = $model->add($data);	
		if($res){
			$this->redirect('Goods/goodsBrand');
		}else{
			$this->error('添加失败');
		}
	}	 
	//修改
	public function goodsBrandEdit(){
		$id = I('id');
		$map['id'] = array('eq',$id);
		$map['status'] = array('egt',0);
		$list = getTable('Goodsbrand',$map);//$model->where($map)->find();  
		//目录树 
		$list['treePathName'] = getTreeTypeName($list['typeId']); 
		$list['treePathNameArr'] = explode('/', $list['treePathName']); 
		$list['treePathId'] = getTreeTypeId($list['typeId']); 
		$list['treePathIdArr'] = explode('/', $list['treePathId']); 
		$this->assign('list',$list); 

		$model = D('Type');
		$where['status'] = array('eq',1);
		$where['level'] = array('eq',1);
		$list = $model->where($where)->select();  		
		$this->assign('goodsType',$list);

		$this->display();		
	}
	//更新入库
	public function goodsBrandUpdate(){ 
		$id = I('id');
		$map['id'] = array('eq',$id);
		$typeId1 = I('typeId1');
		$typeId2 = I('typeId2');
		$name = I('name');
		$imgs = I('imgs');

		//非空处理
		if(!empty($typeId2)){
			$data['typeId'] = $typeId2;
		}else{
			$data['typeId'] = $typeId1;
		}
		if(!empty($name)){
			$data['name'] = $name;
		}
		if(!empty($imgs)){
			$data['imgs'] = $imgs;
		}
		$data['updatetime'] = time();
 
		$model = D('Goodsbrand');
		$res = $model->where($map)->save($data);	 
		if($res){
			$this->redirect('Goods/goodsBrand');
		}else{
			$this->error('修改失败');
		}
	}	
/**
	 * 商品颜色
	 */ 
	public function goodsColor(){
		$this->goodsList();
		$this->display();
	}
	//添加
	public function goodsColorAdd(){
		$model = D('Goodscolor');
		$map['status'] = array('egt',0); 
		$list = $model->where($map)->select();  
		$this->assign('goodsColor',$list);
		
		$model = D('Type');
		$map['status'] = array('eq',1);
		$map['level'] = array('eq',1);
		$list = $model->where($map)->select(); 
		$this->assign('goodsType',$list);

		$this->display();		
	}
	//新增入库
	public function goodsColorInsert(){ 
		$typeId1 = I('typeId1');
		$typeId2 = I('typeId2');
		if(!empty($typeId2)){
			$data['typeId'] = $typeId2;
		}else{
			$data['typeId'] = $typeId1;
		}
		$data['name'] = I('name'); 
		$data['status'] = 1; 

		$model = D('Goodscolor');
		$res = $model->add($data);	
		if($res){
			$this->redirect('Goods/goodsColor');
		}else{
			$this->error('添加失败');
		}
	}	 
	//修改
	public function goodsColorEdit(){
		$id = I('id');
		$map['id'] = array('eq',$id);
		$map['status'] = array('egt',0);
		$list = getTable('Goodscolor',$map);//$model->where($map)->find();  
		//目录树 
		$list['treePathName'] = getTreeTypeName($list['typeId']); 
		$list['treePathNameArr'] = explode('/', $list['treePathName']); 
		$list['treePathId'] = getTreeTypeId($list['typeId']); 
		$list['treePathIdArr'] = explode('/', $list['treePathId']); 
		$this->assign('list',$list); 

		$model = D('Type');
		$where['status'] = array('eq',1);
		$where['level'] = array('eq',1);
		$list = $model->where($where)->select();  		
		$this->assign('goodsType',$list);

		$this->display();		
	}
	//更新入库
	public function goodsColorUpdate(){ 
		$id = I('id');
		$map['id'] = array('eq',$id);
		$typeId1 = I('typeId1');
		$typeId2 = I('typeId2');
		$name = I('name'); 

		//非空处理
		if(!empty($typeId2)){
			$data['typeId'] = $typeId2;
		}else{
			$data['typeId'] = $typeId1;
		}
		if(!empty($name)){
			$data['name'] = $name;
		} 
		$data['updatetime'] = time();

		$model = D('Goodscolor');
		$res = $model->where($map)->save($data);	 
		if($res){
			$this->redirect('Goods/goodsColor');
		}else{
			$this->error('修改失败');
		}
	}	

/**
	 * 商品颜色
	 */ 
	public function goodsSize(){
		$this->goodsList();
		$this->display();
	}
	//添加
	public function goodsSizeAdd(){
		$model = D('Goodssize');
		$map['status'] = array('egt',0); 
		$list = $model->where($map)->select();  
		$this->assign('goodsSize',$list);
		
		$model = D('Type');
		$map['status'] = array('eq',1);
		$map['level'] = array('eq',1);
		$list = $model->where($map)->select(); 
		$this->assign('goodsType',$list);

		$this->display();		
	}
	//新增入库
	public function goodsSizeInsert(){ 
		$typeId1 = I('typeId1');
		$typeId2 = I('typeId2');
		if(!empty($typeId2)){
			$data['typeId'] = $typeId2;
		}else{
			$data['typeId'] = $typeId1;
		}
		$data['name'] = I('name'); 
		$data['status'] = 1; 

		$model = D('Goodssize');
		$res = $model->add($data);	
		if($res){
			$this->redirect('Goods/goodsSize');
		}else{
			$this->error('添加失败');
		}
	}	 
	//修改
	public function goodsSizeEdit(){
		$id = I('id');
		$map['id'] = array('eq',$id);
		$map['status'] = array('egt',0);
		$list = getTable('Goodssize',$map);//$model->where($map)->find();  
		//目录树 
		$list['treePathName'] = getTreeTypeName($list['typeId']); 
		$list['treePathNameArr'] = explode('/', $list['treePathName']); 
		$list['treePathId'] = getTreeTypeId($list['typeId']); 
		$list['treePathIdArr'] = explode('/', $list['treePathId']); 
		$this->assign('list',$list); 

		$model = D('Type');
		$where['status'] = array('eq',1);
		$where['level'] = array('eq',1);
		$list = $model->where($where)->select();  		
		$this->assign('goodsType',$list);

		$this->display();		
	}
	//更新入库
	public function goodsSizeUpdate(){ 
		$id = I('id');
		$map['id'] = array('eq',$id);
		$typeId1 = I('typeId1');
		$typeId2 = I('typeId2');
		$name = I('name'); 

		//非空处理
		if(!empty($typeId2)){
			$data['typeId'] = $typeId2;
		}else{
			$data['typeId'] = $typeId1;
		}
		if(!empty($name)){
			$data['name'] = $name;
		} 
		$data['updatetime'] = time();

		$model = D('Goodssize');
		$res = $model->where($map)->save($data);	 
		if($res){
			$this->redirect('Goods/goodsSize');
		}else{
			$this->error('修改失败');
		}
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
       		$this->goodsLog($id,3);
            $this->ajaxReturn(array('code'=>2000,'msg'=>'删除成功'));
        }else{
            // $this->error('删除失败');
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'删除失败'));
        }
    } 


	//通过代购商品审核
	public function tongguo(){
		$id = I('id',0,'intval');
		$model = M('Goods');
		$map['id'] = array('eq',$id);
		$data['checkstatus'] = 3;
		$res = $model->where($map)->save($data);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功'));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
		}
	}
	//通过代购商品审核
	public function bohui(){
		$id = I('id',0,'intval');
		$model = M('Goods');
		$map['id'] = array('eq',$id);
		$data['checkstatus'] = 2;
		$res = $model->where($map)->save($data);
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功'));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
		}
	}

	/**
	 * ajax 税率
	 */
	public function getTaxrate(){
		$parentId = I('id',0,'intval');
		if($parentId == 0){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'','data'=>array()));
		}
		$model = M('Taxrate');
		$map['parentId'] = array('eq',$parentId);
		$list = $model->where($map)->select();
		if($list){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','data'=>$list));
		}
		$this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
	}



}
?>