<?php
/**
 * 订单控制器
 */
class OrderAction extends BaseAction{   
   /**
     * 读取页面
     */
    public function show(){
        $model = D('Order');
        $where['id'] = array('eq',$_REQUEST['id']);
        $list = $model->where($where)->find();

        $mOrderinfo = D('Orderinfo');
        $map['orderId'] = array('eq',$_REQUEST['id']);
        $dOrderinfo = $mOrderinfo->where($map)->select();
        $total['totalGoods'] = 0;
        //$total['totalGoodsNum'] = 0;
        //$total['totalSubtotal'] = 0;
        $total['totalPrice'] = 0;
        foreach ($dOrderinfo as $key => $value) {
	        $total['totalGoods'] += 1;
	        //$total['totalGoodsNum'] += sprintf("%.2f", $value['goodsNum']);
	        //$total['totalSubtotal'] += sprintf("%.2f", $value['goodsPrice']);
        	$total['totalPrice'] += sprintf("%.2f", $value['goodsNum'] * $value['goodsPrice']);

        	$dOrderinfo[$key]['subtotal'] = sprintf("%.2f", $value['goodsNum'] * $value['goodsPrice']);
        }
        //合计 = 商品价格 + 运费 - 优惠券
         $total['totalPrice'] =  $total['totalPrice'] +  $list['freight'] -  $list['voucherId']; //coupon; 
        if($list){
        	 // dump($list);
            $this->assign('list',$list); 
            $this->assign('dOrderinfo',$dOrderinfo);
            $this->assign('total',$total); 
        	$this->display();
        }else{
            $this->error('获取数据失败'); 
        }
    }

   /**
     * 改价
     */
    public function editPayment(){
    	$id = I('id'); 
        $model = D('Order');
        $where['id'] = array('eq',$id);
        $data['freight'] = I('newYunfei');
        $order = $model->where($where)->find();
        $data['payment'] = $order['payment'] - $data['freight'];
        $data['updatetime'] = time();
        $res = $model->where($where)->save($data);
        if($res){
        	$list = $model->where($where)->find();
        	$this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','data'=>$list));
        }else{
        	$this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
        } 
    }

    /**
     * 发货列表
     */
    public function index2(){
        $model = D('Order');
        if(!empty($model)){
            $map['status'] = array('eq',1);
            if(empty($_GET['p'])){
                    $p = 1;
            }else{
                    $p = $_GET['p'];
            }
            $list = $model->where($map)->order($model->getPk().' desc')->page($p.',10')->select();
            //var_dump($list);
            $this->assign('list',$list);
            import('ORG.Util.Page');// 导入分页类
            $count = $model->where($map)->order($model->getPk().' desc')->count();// 查询满足要求的总记录数
            $Page = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
            $show = $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出
        }
        $this->display();
    }
   /**
     * 读取页面
     */
    public function show2(){
        $model = D('Order');
        $where['id'] = array('eq',$_REQUEST['id']);
        $list = $model->where($where)->find();

        //物流公司
        $mExpress = M('Express');
        $dExpress = $mExpress->select();
        $this->assign('express',$dExpress);
        //
        $mOrderinfo = D('Orderinfo');
        $map['orderId'] = array('eq',$_REQUEST['id']);
        $dOrderinfo = $mOrderinfo->where($map)->select();
        $total['totalGoods'] = 0;
        //$total['totalGoodsNum'] = 0;
        //$total['totalSubtotal'] = 0;
        $total['totalPrice'] = 0;
        foreach ($dOrderinfo as $key => $value) {
	        $total['totalGoods'] += 1;
	        //$total['totalGoodsNum'] += sprintf("%.2f", $value['goodsNum']);
	        //$total['totalSubtotal'] += sprintf("%.2f", $value['goodsPrice']);
        	$total['totalPrice'] += sprintf("%.2f", $value['goodsNum'] * $value['goodsPrice']);

        	$dOrderinfo[$key]['subtotal'] = sprintf("%.2f", $value['goodsNum'] * $value['goodsPrice']);
        }
        //合计 = 商品价格 + 运费 - 优惠券
         $total['totalPrice'] =  $total['totalPrice'] +  $list['freight'] -  $list['voucherId']; //coupon;
        if($list){
            $this->assign('list',$list); 
            $this->assign('dOrderinfo',$dOrderinfo);
            $this->assign('total',$total); 
        	$this->display();
        }else{
            $this->error('获取数据失败'); 
        }
    }

   /**
     * 发货
     */
    public function sendGoods(){
    	$id = I('id'); 
        $model = D('Order');
        $sendNum = I('sendNum');
        $map['sendNum'] = array('eq',$sendNum);
        $res = $model->where($map)->find();
        if($res && $res !== null){
                $this->ajaxReturn(array('code'=>-6001,'msg'=>'该运单编号已存在！'));
        }
        $where['id'] = array('eq',$id);
        $data['expressId'] = I('expressId');
        $data['sendNum'] = $sendNum;
        $data['status'] = 2;
        $data['updatetime'] = time();
        $res = $model->where($where)->save($data);
        if($res){
        	$list = $model->where($where)->find();
            // 
            $newData = array(
                'logisticsNo'=>$data['sendNum'], //运单
                'orderNo'=>$list['orderNum'],
                'consignee'=>getField($list['addressId'],'name','Address'), //收货人
                'consigneeAddress'=>getField($list['addressId'],'specificAddr','Address').getField($list['addressId'],'addr','Address'),
                'consigneeTelephone'=>getField($list['addressId'],'tel','Address'),
                'idType'=>'1',
                'customerId'=>$this->getMemInfo($id),
                'weight'=>$this->getWeight($id),
                'quantity'=>$this->getQuantity($id),
                'ieType'=>'I',
                'stockFlag'=>'2',
                'batchNumbers'=>'', //批次号
                'modifyMark'=>'1',
                'appTime'=>time(),
            );
            $code = $this->yunda($newData);
            if($code == 0){
                    $this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','data'=>$list,'bug'=>$newData ));
            }
        }else{
        	$this->ajaxReturn(array('code'=>-6002,'msg'=>'操作失败'));
        } 
    }
    //增加发货地址
    public function addExpressName(){
            $model = M('Express');
            $name = I('name');
            $sendNum = I('sendNum');
            $map['name'] = array('eq',$name);
            $res = $model->where($map)->find();
            if($res && $res !== null){
                    $this->ajaxReturn(array('code'=>-6001,'msg'=>'该物流公司已存在！'));
            }
            $map1['sendNum'] = array('eq',$sendNum);
            $res = M('Order')->where($map1)->find();
            if($res && $res !== null){
                   $this->ajaxReturn(array('code'=>-6002,'msg'=>'该运单编号已存在！','res'=>$res));
            }
            $data['name'] = $name;
            $res = $model->add($data);
            if($res && $res !== null){
                    $this->ajaxReturn(array('code'=>2000,'msg'=>'操作成功','id'=>$res));
            }
            $this->ajaxReturn(array('code'=>-6003,'msg'=>'操作失败'));
    }
    //身份证
    public function getMemInfo($oid=0){
        $model = M('Ordermember');
        $map['orderId'] = array('eq',$oid);
        $res = $model->where($map)->find();
        $arr = rtrim($res['memInfo'],"]");
        $arr = ltrim($arr,"[");
        $arr = json_decode($arr,true);
        foreach ($arr as $key => $value) {
            return $value;
            break;
        } 
    }
    //
    public function getWeight($id=0){
        $model = M('Orderinfo');
        $map['orderId'] = array('eq',$id);
        $res = $model->where($map)->select();
        $weight = 0;
        foreach ($res as $key => $value) {
            $weight = $weight + getField($value['goodsId'],'weight','Goods') * $value['goodsNum'];
        }
        return $weight;
    }
    //
    public function getQuantity($id=0){
        $model = M('Orderinfo');
        $map['orderId'] = array('eq',$id); 
        $res = $model->where($map)->select();
        $sum = 0;
        foreach ($res as $key => $value) {
            $sum += $value['goodsNum'];
        }
        return $sum;
    }

}
?>