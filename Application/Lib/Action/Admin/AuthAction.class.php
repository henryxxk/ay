<?php
// 权限管理控制器
class AuthAction extends BaseAction {
    public function index(){
		$this->display();
    }
    /**
     * 权限规则
     */
    public function ruleIndex(){
        $mRule = M('AuthRule');
        $dRule = $mRule->select();
        $this->assign('dRule',$dRule);
        $this->display();
    }
    public function ruleAdd(){
        $this->display();
    }
    public function ruleInsert(){
        $data['name'] = I('name');
        $data['title'] = I('title');
        $data['condition'] = I('condition');

        $mRule = M('AuthRule');
        if($mRule->add($data)){
            $this->redirect('Auth/ruleIndex');
        }else{
            $this->error('添加失败');
        }
    }
    public function ruleEdit(){
        $id = I('id');
        $map['id'] = array('eq',$id);

        $mRule = M('AuthRule');
        $dRule = $mRule->where($map)->find();
        $this->assign('dRule',$dRule);
        $this->display();
    }
    public function ruleUpdate(){
        $id = I('id');
        $map['id'] = array('eq',$id);
        $name = I('name');
        if(!empty($name)){
            $data['name'] = $name;
        }
        $title = I('title');
        if(!empty($title)){
            $data['title'] = $title;
        }
        $condition = I('condition');
        if(!empty($condition)){
            $data['condition'] = $condition;
        }

        $mRule = M('AuthRule');
        if($mRule->where($map)->save($data)){
            $this->redirect('Auth/ruleIndex');
        }else{
            $this->error('修改失败');
        }
    }
    public function ruleDelete(){
        $id = I('id',0,'intval');
        if($id < 1){
            $this->ajaxReturn(array('code'=>-6001,'msg'=>'请求参数错误'));
        }
        $map['id'] = array('eq',$id);
        $mRule = M('AuthRule');
        if($mRule->where($map)->delete()){
            // $this->redirect('Auth/ruleIndex');
            $this->ajaxReturn(array('code'=>2000,'msg'=>'删除成功'));
        }else{
            // $this->error('删除失败');
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'删除失败'));
        }
    }

    /**
     * 权限分组
     */
    public function groupIndex(){
    	$mGroup = M('AuthGroup');
    	$dGroup = $mGroup->select();
    	$this->assign('dGroup',$dGroup);
    	$this->display();
    }
    public function groupAdd(){
        $this->display();
    }
    public function groupInsert(){
        $data['title'] = I('title');
        $data['rules'] = I('rules');

        $mGroup = M('AuthGroup');
        if($mGroup->add($data)){
            $this->redirect('Auth/groupIndex');
        }else{
            $this->error('添加失败');
        }
    }
    public function groupEdit(){
        $id = I('id');
        $map['id'] = array('eq',$id);

        $mGroup = M('AuthGroup');
        $dGroup = $mGroup->where($map)->find();
        $this->assign('dGroup',$dGroup);
        $this->display();
    }
    public function groupUpdate(){
        $id = I('id');
        $map['id'] = array('eq',$id);
        $title = I('title');
        if(!empty($title)){
            $data['title'] = $title;
        }
        $rules = I('rules');
        if(!empty($rules)){
            $data['rules'] = $rules;
        }

        $mGroup = M('AuthGroup');
        if($mGroup->where($map)->save($data)){
            $this->redirect('Auth/groupIndex');
        }else{
            $this->error('修改失败');
        }
    }
    public function groupDelete(){
        $id = I('id',0,'intval');
        if($id < 1){
            $this->ajaxReturn(array('code'=>-6001,'msg'=>'请求参数错误'));
        }
        $map['id'] = array('eq',$id);
        $mGroup = M('AuthGroup');
        if($mGroup->where($map)->delete()){
            // $this->redirect('Auth/ruleIndex');
            $this->ajaxReturn(array('code'=>2000,'msg'=>'删除成功'));
        }else{
            // $this->error('删除失败');
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'删除失败'));
        }
    }

    /**
     * 用户权限分配
     */
    public function accessIndex(){
    	$mAccess = M('AuthGroupAccess');
    	$dAccess = $mAccess->select();
    	$this->assign('dAccess',$dAccess);
    	$this->display();
    }
    public function accessAdd(){
        $this->display();
    }
    public function accessInsert(){
        $data['group_id'] = I('group_id');
        $data['uid'] = I('uid');

        $mAccess = M('AuthGroupAccess');
        if($mAccess->add($data)){
            $this->redirect('Auth/accessIndex');
        }else{
            $this->error('添加失败');
        }
    }
    public function accessEdit(){
        $id = I('id');
        $map['id'] = array('eq',$id);

        $mAccess = M('AuthGroupAccess');
        $dAccess = $mAccess->where($map)->find();
        $this->assign('dAccess',$dAccess);
        $this->display();
    }
    public function accessUpdate(){
        $id = I('id');
        $map['id'] = array('eq',$id);
        $group_id = I('group_id');
        if(!empty($group_id)){
            $data['group_id'] = $group_id;
        }
        $uid = I('uid');
        if(!empty($uid)){
            $data['uid'] = $uid;
        }

        $mAccess = M('AuthGroupAccess');
        if($mAccess->where($map)->save($data)){
            $this->redirect('Auth/accessIndex');
        }else{
            $this->error('修改失败');
        }
    }
    public function accessDelete(){
        $id = I('id',0,'intval');
        if($id < 1){
            $this->ajaxReturn(array('code'=>-6001,'msg'=>'请求参数错误'));
        }
        $map['id'] = array('eq',$id);
        $mAccess = M('AuthGroupAccess');
        if($mAccess->where($map)->delete()){
            // $this->redirect('Auth/ruleIndex');
            $this->ajaxReturn(array('code'=>2000,'msg'=>'删除成功'));
        }else{
            // $this->error('删除失败');
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'删除失败'));
        }
    }

}