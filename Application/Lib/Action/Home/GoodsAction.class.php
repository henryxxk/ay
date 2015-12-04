<?php
class GoodsAction extends Action{
    /**
    *商品
    */
    public function index(){
        
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