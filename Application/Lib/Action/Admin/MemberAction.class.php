<?php
/**
 * 用户控制器
 */
class MemberAction extends BaseAction{ 
	/**
	 * 添加商品 
	 */
	// public function add(){
	// 	//商品类型
	// 	$mapType['status'] = array('eq',2);
	// 	$mapType['parentId'] = array('eq',0);
	// 	$typelist = getTable('Type',$mapType,2);  
	// 	//目录树
	// 	foreach ($typelist as $key => $value) {
	// 		$typelist[$key]['treePathName'] = getTreeTypeName($value['id']);
	// 	}
	// 	$this->assign('articleType',$typelist);
 
	// 	$this->display();
	// } 
	/**
     * 插入表数据
     */
    // public function insert(){
    //     $model = D('Member');
    //     if(false === $model->create()){
    //         $this->error('表单提交数据错误');
    //         // $this->appReturn(-6000,'表单提交数据错误');
    //     } 
    //     $content = I('content');
    //     $model->content = serialize($content);
    //     $model->createtime = time();
    //     $list = $model->add();
    //     if(false !== $list){
    //         $this->redirect("Article/index",array('menu'=>'article'));
    //         // $this->appReturn(2000,'数据插入成功');
    //     }else{
    //         $this->error('数据插入失败');
    //         // $this->appReturn(-6002,'数据插入失败');
    //     }
    // }
    /**
     * 编辑页面
     */
  //   public function edit(){ 
		// //商品类型
		// $mapType['status'] = array('eq',2);
		// $mapType['parentId'] = array('eq',0);
		// $typelist = getTable('Type',$mapType,2);  
		// //目录树
		// foreach ($typelist as $key => $value) {
		// 	$typelist[$key]['treePathName'] = getTreeTypeName($value['id']);
		// }
		// $this->assign('articleType',$typelist);
		 
		// //编辑
  //   	$id = I('id',0,'intval');
  //       $model = D('Member');
  //       $where['id'] = array('eq',$id);
  //       $list = $model->where($where)->find();
  //       $str = unserialize($list['content']);
  //       $list['content'] = htmlspecialchars_decode($str);
		// //目录树 
		// $list['treePathName'] = getTreeTypeName($list['typeId']);   
		// $list['treePathNameArr'] = explode('/', $list['treePathName']); 
		// $list['treePathId'] = getTreeTypeId($list['typeId']); 
		// $list['treePathIdArr'] = explode('/', $list['treePathId']);
  //       //var_dump($list);
  //       if($list){
  //           $this->assign('list',$list);
  //       }else{
  //           $this->error('获取编辑数据失败');
  //       }
  //       $this->display();
  //   }  
    /**
     * 更新表数据
     */
    // public function update(){ 
    //     $model = D('Member');
    //     if(false === $model->create()){
    //         $this->error('表单提交数据错误');
    //         // $this->appReturn(-6000,'表单提交数据错误');
    //     } 
    //     $content = I('content');
    //     $model->content = serialize($content);
    //     //$model->desc = urldecode($content);
    //     if($model->typeId == 28){
    //     	$model->sort = 1;
    //     }
    //     $model->updatetime = time();
    //     //var_dump($model);exit;
    //     $list = $model->save();
    //     if(false !== $list){
    //         $this->redirect("Article/index",array('menu'=>'article'));
    //         // $this->appReturn(2000,'数据编辑成功');
    //     }else{
    //         $this->error('数据编辑失败');
    //         // $this->appReturn(-6002,'数据编辑失败');
    //     }
    // }
     /**
     * 详情页面
     */
    public function show(){   
		//详情
    	$id = I('id',0,'intval');
        $model = D('Member');
        $where['id'] = array('eq',$id);
        $list = $model->where($where)->find();
        //团体
        if($list['isAttestation'] == 1){
            $mMembergroup = M('Membergroup');
            $mapMemgroup['memberId'] = $list['id'];
            $groups = $mMembergroup->where($mapMemgroup)->find();
            $list['groupMem'] = json_decode($groups['membergroup'],true);
            $list['organization'] = $groups['organization'];
            $list['contacts'] = $groups['contacts'];
            $list['phone'] = $groups['phone'];
            $list['remark'] = $groups['remark'];
        }
        //代购
        if($list['isReplaceBuy'] == 3){
            $mMemberagency = M('Memberagency');
            $mapMemberagency['memberId'] = $list['id'];
            $dMemberagency = $mMemberagency->where($mapMemberagency)->find();
            $list['idenCode'] = $dMemberagency['idenCode'];
            $list['buyProve'] = $dMemberagency['buyProve'];

        }
        //var_dump($list);
        if($list){
            $this->assign('list',$list);
        }else{
            $this->error('获取编辑数据失败');
        }
        $this->display();
    }  
#-------------------
# vip
#-------------------

    public function vipIndex(){
        $model = D('Member');
        if(!empty($model)){
            $map['typeId'] = array('eq',43);
            $list = $model->where($map)->order($model->getPk().' desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['day'] = round(($value['endTime']-$value['startTime'])/3600/24);
            }
            //var_dump($list);
            $this->assign('list',$list);
        }
        $this->display('Member/vipIndex');
    }
     /**
     * VIP详情页面
     */
    public function vipShow(){   
        //详情
        $id = I('id',0,'intval');
        $model = D('Member');
        $where['id'] = array('eq',$id);
        $list = $model->where($where)->find();  
        //var_dump($list);
        if($list){
            $this->assign('list',$list);
        }else{
            $this->error('获取编辑数据失败');
        }
        $this->display();
    }  

#-------------------
# 团体
#-------------------

    public function groupIndex(){
        $model = D('Member');
        if(!empty($model)){
            //$map['typeId'] = array('eq',44);
            $map['m_member.isAttestation'] = array('in','1,2,3');
            if(empty($_GET['p'])){
                    $p = 1;
            }else{
                    $p = $_GET['p'];
            }
            $list = $model->where($map)->join('m_membergroup ON m_membergroup.memberId=m_member.id')->field('m_member.*,m_membergroup.organization,m_membergroup.contacts,m_membergroup.phone')->page($p.',10')->select();
            foreach ($list as $key => $value) {
                $list[$key]['day'] = round(($value['endTime']-$value['startTime'])/3600/24);
            }
            //var_dump($list);
            $this->assign('list',$list);
            import('ORG.Util.Page');// 导入分页类
            $count = $model->where($map)->join('m_membergroup ON m_membergroup.memberId=m_member.id')->field('m_member.*,m_membergroup.organization,m_membergroup.contacts,m_membergroup.phone')->count();// 查询满足要求的总记录数
            $Page = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
            $show = $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出
        }
        $this->display('Member/groupIndex');
    }
     /**
     * 团体详情页面
     */
    public function groupShow(){   
        //详情
        $id = I('id',0,'intval');
        $model = D('Member');
        $where['id'] = array('eq',$id);
        $list = $model->where($where)->find();   
        //团体
        if(in_array($list['isAttestation'],array(1,2,3))){
            $mMembergroup = M('Membergroup');
            $mapMemgroup['memberId'] = $list['id'];
            $groups = $mMembergroup->where($mapMemgroup)->find();
            $list['groupMem'] = json_decode($groups['membergroup'],true);
            $list['organization'] = $groups['organization'];
            $list['contacts'] = $groups['contacts'];
            $list['phone'] = $groups['phone'];
            $list['remark'] = $groups['remark'];
        }
        //var_dump($list);
        if($list){
            $this->assign('list',$list);
        }else{
            $this->error('获取编辑数据失败');
        }
        $this->display();
    } 
     /**
     * 团体审核
     */
    public function groupShowCheck(){   
        //详情
        $id = I('id',0,'intval');
        $model = D('Member');
        $where['id'] = array('eq',$id);
        $list = $model->where($where)->find();   
        //团体
        if(in_array($list['isAttestation'],array(1,2,3))){
            $mMembergroup = M('Membergroup');
            $mapMemgroup['memberId'] = $list['id'];
            $groups = $mMembergroup->where($mapMemgroup)->find();
            $list['groupMem'] = json_decode($groups['membergroup'],true);
            $list['organization'] = $groups['organization'];
            $list['contacts'] = $groups['contacts'];
            $list['phone'] = $groups['phone'];
            $list['remark'] = $groups['remark'];
        }
        //var_dump($list);
        if($list){
            $this->assign('list',$list);
        }else{
            $this->error('获取编辑数据失败');
        }
        $this->display();
    } 
    //
    public function groupShowCheckUpdate(){   
        //详情
        $id = I('id',0,'intval');
        $isAttestation = I('isAttestation');
        if(!empty($isAttestation)){
            $data['isAttestation'] = $isAttestation;
        }
        $attestationRemark = I('attestationRemark');
        if(!empty($attestationRemark)){
            $data['attestationRemark'] = $attestationRemark;
        }
        if($isAttestation == 1){
            $data['attestationRemark'] = '';
            $data['typeId'] = 44;
        }
        $isReplaceBuy = I('isReplaceBuy');
        if(!empty($isReplaceBuy)){
            $data['isReplaceBuy'] = $isReplaceBuy;
        }
        $replaceBuyRemark = I('replaceBuyRemark');
        if(!empty($replaceBuyRemark)){
            $data['replaceBuyRemark'] = $replaceBuyRemark;
        }
        $data['updatetime'] = time();
        $model = D('Member');
        $where['id'] = array('eq',$id);
        $res = $model->where($where)->save($data); 
        $debug = $model->getLastSql();  
        if($res){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','debug'=>$debug));
        }else{
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败','debug'=>$debug));
        }
    }

#-------------------
# 代购
#-------------------

    public function behalfIndex(){
        $model = D('Member');
        if(!empty($model)){
            //$map['typeId'] = array('eq',45);
            $map['isReplaceBuy'] = array('in','1,3');
            $list = $model->where($map)->order($model->getPk().' desc')->select();
            foreach ($list as $key => $value) {
                $list[$key]['day'] = round(($value['endTime']-$value['startTime'])/3600/24);
            }
            //var_dump($list);
            $this->assign('list',$list);
            import('ORG.Util.Page');// 导入分页类
            $count = $model->where($map)->order($model->getPk().' desc')->count();// 查询满足要求的总记录数
            $Page = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
            $show = $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出
        }
        $this->display('Member/behalfIndex');
    }
     /**
     * 代购详情页面
     */
    public function behalfShow(){   
        //详情
        $id = I('id',0,'intval');
        $model = D('Member');
        $where['id'] = array('eq',$id);
        $list = $model->where($where)->find();  
        //代购
        if($list['isReplaceBuy'] == 3){
            $mMemberagency = M('Memberagency');
            $mapMemberagency['memberId'] = $list['id'];
            $dMemberagency = $mMemberagency->where($mapMemberagency)->find();
            $list['idenCode'] = $dMemberagency['idenCode'];
            $list['buyProve'] = $dMemberagency['buyProve'];

        }
        //var_dump($list);
        if($list){
            $this->assign('list',$list);
        }else{
            $this->error('获取编辑数据失败');
        }
        $this->display();
    } 
     /**
     * 代购审核
     */
    public function behalfShowCheck(){   
        //详情
        $id = I('id',0,'intval');
        $model = D('Member');
        $where['id'] = array('eq',$id);
        $list = $model->where($where)->find();  
        //代购
        if($list['isReplaceBuy'] == 3){
            $mMemberagency = M('Memberagency');
            $mapMemberagency['memberId'] = $list['id'];
            $dMemberagency = $mMemberagency->where($mapMemberagency)->find();
            $list['idenCode'] = $dMemberagency['idenCode'];
            $list['buyProve'] = $dMemberagency['buyProve'];

        }
        //var_dump($list);
        if($list){
            $this->assign('list',$list);
        }else{
            $this->error('获取编辑数据失败');
        }
        $this->display();
    } 
    //
    public function behalfShowCheckUpdate(){   
        //详情
        $id = I('id',0,'intval');
        $data['isReplaceBuy'] = I('isReplaceBuy');
        $data['replaceBuyRemark'] = I('replaceBuyRemark');
        $data['updatetime'] = time();
        $model = D('Member');
        $where['id'] = array('eq',$id);
        $res = $model->where($where)->save($data);   
        if($res){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功'));
        }else{
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
        }
    } 
    /**
     * vip等级
     */
    public function viplevel(){
        $model=M('viplevel');
        $map['status'] = array('egt',0);
        $list = $model->where($map)->select();
        $this->assign('list',$list);
        $this->display();
    }
    //
    public function updateVip(){
        $id = I('id',0,'intval');
        if($id<1){
            $this->ajaxReturn(array('code'=>-6001,'msg'=>'请求参数错误'));
        }
        $map['id'] = array('eq',$id);
        $name = I('name');
        if(!empty($name)){
            $data['name'] = $name;
        }
        $code = I('code');
        $data['code'] = $code;
        $status = I('status',0,'intval'); 
        $data['status'] = $status; 
        $imgs = I('imgs');
        if(!empty($imgs)){
            $data['imgs'] = $imgs;
        }
        $desc = I('desc');
        $data['desc'] = $desc;
        $data['updatetime'] = time();

        $model = M('Viplevel');
        $res = $model->where($map)->save($data);
        if($res){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功'));
        }else{
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
        }
    }

    /**
     * vip费用
     */
    public function vipcost(){
        $model = M('Sysconfig');
        $map['id'] = array('eq',1);    //数据库中手动指定字段ID 1
        $list = $model->where($map)->find();
        $this->assign('list',json_decode(urldecode($list["content"]),true));
        $this->display();
    }
    //
    public function updateVipCost(){
        $id = I('id',0,'intval');
        if($id<1){
            $this->ajaxReturn(array('code'=>-6001,'msg'=>'请求参数错误'));
        }
        $map['id'] = array('eq',$id);
        $content = I('content');
        if(!empty($content)){
            $data['content'] = $content;
        }
        $data['updatetime'] = time();

        $model = M('Sysconfig');
        $res = $model->where($map)->save($data);
        if($res){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','data'=>$content));
        }else{
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
        }
    }
	
    /**
     * 积分消耗设置
     */
    public function vipcode(){
        $model = M('Sysconfig');
        $map['id'] = array('eq',2);    //数据库中手动指定字段ID 1
        $list = $model->where($map)->find();
        $this->assign('list',$list);
        $this->display();
    }
    //
    public function updateVipCode(){
        $id = I('id',0,'intval');
        if($id<1){
            $this->ajaxReturn(array('code'=>-6001,'msg'=>'请求参数错误'));
        }
        $map['id'] = array('eq',$id);
        $content = I('content');
        if(!empty($content)){
            $data['content'] = $content;
        }
        $data['updatetime'] = time();

        $model = M('Sysconfig');
        $res = $model->where($map)->save($data);
        if($res){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','data'=>$content));
        }else{
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
        }
    }

}
?>