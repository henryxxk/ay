<?php

/**

 * 文章控制器

 */

class ArticleAction extends BaseAction{   

    /**

     * 默认页

     */

    public function index(){

        $model = D('Article');

        if(!empty($model)){

        	$map['status'] = array('egt',0);
            if(empty($_GET['p'])){
                    $p = 1;
            }else{
                    $p = $_GET['p'];
            }
            $list = $model->where($map)->order('sort asc,createtime desc')->page($p.',10')->select();

            foreach ($list as $key => $value) {

	        	$str = unserialize($value['content']);

	        	$list[$key]['content'] = htmlspecialchars_decode($str);

            }

            //var_dump($list);

            $this->assign('list',$list);
            import('ORG.Util.Page');// 导入分页类
            $count = $model->where($map)->order('sort asc,createtime desc')->count();// 查询满足要求的总记录数
            $Page = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
            $show = $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出

        }

        $this->display();

    }

	/**

	 * 添加商品 

	 */

	public function add(){

		//商品类型

		$mapType['status'] = array('eq',2);

		$mapType['parentId'] = array('eq',0);

		$typelist = getTable('Type',$mapType,2);  

		//目录树

		foreach ($typelist as $key => $value) {

			$typelist[$key]['treePathName'] = getTreeTypeName($value['id']);

		}

		$this->assign('articleType',$typelist);

 

		$this->display();

	} 

	/**

     * 插入表数据

     */

    public function insert(){

        $model = D('Article');

        if(false === $model->create()){

            $this->error('表单提交数据错误');

            // $this->appReturn(-6000,'表单提交数据错误');

        } 

        $content = I('content');

        $model->content = serialize($content);

        $model->createtime = time();

        $list = $model->add();

        if(false !== $list){

            $this->redirect("Article/index",array('menu'=>'article'));

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

		$mapType['status'] = array('eq',2);

		$mapType['parentId'] = array('eq',0);

		$typelist = getTable('Type',$mapType,2);  

		//目录树

		foreach ($typelist as $key => $value) {

			$typelist[$key]['treePathName'] = getTreeTypeName($value['id']);

		}

		$this->assign('articleType',$typelist);

		 

		//编辑

    	$id = I('id',0,'intval');

        $model = D('Article');

        $where['id'] = array('eq',$id);

        $list = $model->where($where)->find();

        $str = unserialize($list['content']);

        $list['content'] = htmlspecialchars_decode($str);

        $list['content'] = str_replace('\\', "", $list['content']);  //html标签解码

        

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

        $model = D('Article');

        if(false === $model->create()){

            $this->error('表单提交数据错误');

            // $this->appReturn(-6000,'表单提交数据错误');

        } 

        $content = I('content');

        $model->content = serialize($content);

        //$model->desc = urldecode($content);

        if($model->typeId == 28){

        	$model->sort = 1;

        }

        $model->updatetime = time();

        //var_dump($model);exit;

        $list = $model->save();

        if(false !== $list){

            $this->redirect("Article/index",array('menu'=>'article'));

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

		//商品类型

		$mapType['status'] = array('eq',2);

		$mapType['parentId'] = array('eq',0);

		$typelist = getTable('Type',$mapType,2);  

		//目录树

		foreach ($typelist as $key => $value) {

			$typelist[$key]['treePathName'] = getTreeTypeName($value['id']);

		}

		$this->assign('articleType',$typelist);

		 

		//编辑

    	$id = I('id',0,'intval');

        $model = D('Article');

        $where['id'] = array('eq',$id);

        $list = $model->where($where)->find();

        $str = unserialize($list['content']);

        $list['content'] = htmlspecialchars_decode($str);

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





}

?>