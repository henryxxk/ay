<?php

/**

 * 商品海关备案控制器

 */

class FilingAction extends BaseAction{  

	/**

	* 设置企业海关备案基本信息

	*/

	public function setting(){



		$mReportGhead = D('ReportGoodshead');

		$map['id'] = array('eq',1);	

		$list = $mReportGhead->where($map)->find();

        // var_dump($list);

		$this->assign('list',$list);

		$this->display();

	}

	//setupdate

	public function setupdate(){

		$postData = $_POST;



		$mReportGhead = D('ReportGoodshead');

		$map['id'] = array('eq',1);		

		// m_report_goodshead		

        $data['shopNum'] = $postData['shopNum'];
        $data['shopKey'] = $postData['shopKey'];
        $data['appkey'] = $postData['appkey'];

        $data['customsCode'] = $postData['customsCode'];

		$data['cbeCode'] = $postData['cbeCode'];

		$data['cbeName'] = $postData['cbeName'];

		$data['applyCode'] = $postData['applyCode'];

		$data['applyName'] = $postData['applyName'];

		$data['agentCode'] = $postData['agentCode'];

		$data['agentName'] = $postData['agentName'];

		$data['accessType'] = $postData['accessType'];

		$data['ecpCode'] = $postData['ecpCode'];

		$data['ecpName'] = $postData['ecpName'];

		$data['ieType'] = $postData['ieType'];

		$data['appUid'] = $postData['appUid'];

		$data['appUname'] = $postData['appUname'];

		$dReportGhead = $mReportGhead->where($map)->save($data);

 		// var_dump($postData);exit;

        if(false !== $dReportGhead){

            $this->redirect("Filing/setting",array('menu'=>'filing'));

            // $this->appReturn(2000,'数据插入成功');

        }else{

            $this->error('数据插入失败');

            // $this->appReturn(-6002,'数据插入失败');

        }

	}

	/**

	* createGoods xml

	*/

	public function createGoods(){

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

    	var_dump($_POST);exit;

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

        //备案商品头

        $head = getTable('ReportGoodshead',array(),2);



        $list = $model->add();

        if(false !== $list){

            $this->redirect("Goods/index");

            // $this->appReturn(2000,'数据插入成功');

        }else{

            $this->error('数据插入失败');

            // $this->appReturn(-6002,'数据插入失败');

        } 

    }

    /**

	* createOrder xml

	*/

	public function createOrder(){

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

     * 默认页

     */

    public function index(){

        $this->pageSize = C('PAGE_SIZE');

        $map = $this->_search();

        $behalf = I('tag');

        $map['status'] = array('eq',1);

        if(!empty($behalf) && $behalf == 'behalf'){

        	$map['commodity'] = array('eq',1); 

        }else{

        	//$map['commodity'] = array('eq',0); 

        }

        $tbName = $this->getActionName();

        $model = D($tbName);

        if(!empty($model)){

            //$this->_getList($model,$map);

            //获取全部数据 前端Dashboard会进行各种处理 分页 查询 排序 

            $list = $model->where($map)->order($model->getPk().' desc')->select();

            foreach ($list as $key => $value) {

            	$list[$key]['imgArr'] = explode(',', $value['imgs']);

            }

            // var_dump($list);

            $this->assign('list',$list);

        }

        $this->display();

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

        //var_dump($model);exit;

        $list = $model->save();

        if(false !== $list){

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

        // var_dump($list);

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







}

?>