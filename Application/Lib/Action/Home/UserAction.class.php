<?php
class UserAction extends BaseAction{
    public function __construct(){
        parent::__construct();
        if (isset($_SESSION['user'])){
            $this->assign("isLogin",1);
        }
        else{
            $this->assign("isLogin",0);
            $this->redirect("Login/index");
        }
        $this->assign("isBuycar",0);
        $this->assign("isLoginPage",0);
    }
    /**
    *买家个人中心
    */
    public function index(){
        $this->display();
    }
    public function pubGoods(){ 
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
        $mTaxrate = M('Taxrate');
        $mapTa['parentId'] = array('eq',0);
        $taxratelist = $mTaxrate->where($mapTa)->select();
        $this->assign('taxratelist',$taxratelist);

    	$this->display();
    }
    //add goods
    public function addPubGoods(){
        $pData = $_POST;
        $data = array();
        if(!empty($pData['type1'])){
            $data['typeId'] = I('type1');
        }
        if(!empty($pData['type2'])){
            $data['typeId'] = I('type2');
        } 
        $data['name'] = $pData['name'];
        $data['marketPrice'] = $pData['marketPrice'];
        $data['vipPrice'] = $pData['vipPrice'];
        $data['groupPrice'] = $pData['groupPrice'];
        $data['stock'] = $pData['stock'];
        $data['desc'] = $pData['desc'];
        $data['commodity'] = $pData['commodity'];
        $data['commodityUser'] = $pData['commodityUser'];
        $data['imgs'] = $pData['imgs'];
		$data['checkstatus'] = 1;
        $data['createtime'] = time();
        $model = M('Goods'); 
        $taxrateId = $pData['taxrateId'];
        $data['taxrateId'] = $taxrateId;

        $test = getField($taxrateId,'revenue','Taxrate');
        $data['taxrate'] = (int)substr($test, 0,-1)/100;

        $res = $model->add($data); 
        if($res){
            $data['id'] = $res; 
            $this->goodsLog($res,1);
            $this->ajaxReturn(array('code'=>2000,'msg'=>"操作成功",'data'=>$data));
        }else{ 
            $this->ajaxReturn(array('code'=>-6002,'msg'=>"操作失败"));
        }

    }
    public function suggest(){
    	$this->display();
    }
    public function sell(){

        $y=date("Y",time());
        $m=date("m",time());
        $d=date("d",time());
        $start_time = mktime(0, 0, 0, $m, $d ,$y);
        $end_time = mktime(23, 59, 59, $m, $d ,$y);   
        //今日
        $model = M('Countgoods');
        $map['m_countgoods.mId'] = array('eq',$_SESSION['memberId']);
        $map['m_countgoods.createtime'] = array(array('egt',$start_time),array('elt',$end_time)) ;
        $list = $model->join('m_goods ON m_goods.id=m_countgoods.gId')->field('m_goods.*,m_countgoods.mId,m_countgoods.gId,m_countgoods.sales as ctSales,m_countgoods.bargain,m_countgoods.createtime as ctCreatetime')->where($map)->select();
        $sumBargainDay = $model->join('m_goods ON m_goods.id=m_countgoods.gId')->field('m_goods.*,m_countgoods.mId,m_countgoods.gId,m_countgoods.sales as ctSales,m_countgoods.bargain,m_countgoods.createtime as ctCreatetime')->where($map)->sum('m_countgoods.bargain');
        $this->assign('list',$list);
        $this->assign('sumBargainDay',$sumBargainDay);

        //分页 
        $page = I('page',1,'intval');
        if($page<1){
            $page = 1;
        } 
        $pageSize = C('PAGE_SIZE');
        //往期 
        $map['m_countgoods.createtime'] = array('lt',$start_time) ;
        $startTime = I('startTime');
        if(!empty($startTime)){
            $map['m_countgoods.createtime'] = array('egt',$startTime);
        }
        $endTime = I('endTime');
        if(!empty($endTime)){
            $map['m_countgoods.createtime'] = array('elt',$endTime);
        }
        if(!empty($startTime) && !empty($endTime)){
            $map['m_countgoods.createtime'] = array(array('egt',$startTime),array('elt',$endTime)) ;
        }
        $all = $model->join('m_goods ON m_goods.id=m_countgoods.gId')->field('m_goods.*,m_countgoods.mId,m_countgoods.gId,m_countgoods.sales as ctSales,m_countgoods.bargain,m_countgoods.createtime as ctCreatetime')->where($map)->count(); 
        $totalPage = ceil($all/$pageSize);
        // var_dump($totalPage);
        if($page>=$totalPage){
            $page = $totalPage;
        }
        $list2 = $model->join('m_goods ON m_goods.id=m_countgoods.gId')->field('m_goods.*,m_countgoods.mId,m_countgoods.gId,m_countgoods.sales as ctSales,m_countgoods.bargain,m_countgoods.createtime as ctCreatetime')->where($map)->page($page,$pageSize)->select();
        $sumBargain = $model->join('m_goods ON m_goods.id=m_countgoods.gId')->field('m_goods.*,m_countgoods.mId,m_countgoods.gId,m_countgoods.sales as ctSales,m_countgoods.bargain,m_countgoods.createtime as ctCreatetime')->where($map)->sum('m_countgoods.bargain');
        $this->assign('list2',$list2);
        $this->assign('sumBargain',$sumBargain);

        $this->assign('startTime',$startTime);
        $this->assign('endTime',$endTime);
        $this->assign('totalPage',$totalPage); 
        $this->assign('curP',$page);
    	$this->display();
    }
    public function goods(){
        $model = D('Goods');
        $com = $_SESSION['memberId'];  //当前登录的用户id
        if($com==""){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误,请重新登录'));
        }
        $map['commodityUser'] = array('in',$com);
        $goods = $model->where($map)->select();
        // foreach ($goods as $key => $value) {
        //     $tmp = explode(',',$value['imgs']);
        //     $goods[$key]['imgs2'] = $tmp[0];
        // }
        // var_dump($goods);
        if(!$goods){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'获取商品列表失败'));
        } 
        // var_dump($goods);
        $this->assign("list",$goods);
        //$this->ajaxReturn(array('code'=>2000,'msg'=>'获取商品列表成功','data'=>array('goods'=>$goods)));
    	$this->display();
    }
    //deleteGoods
    public function deleteGoods(){
        $id = I('id');
        $model = M('Goods');
        $map['id'] = array('eq',$id);
        $res = $model->where($map)->delete();
        if($res){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'删除成功'));
        }else{ 
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'删除失败'));
        }
    }
    public function editGoods(){
        //海关税率表 
        $taxratelist = getTable('Taxrate',array(),2); 
        $this->assign('taxratelist',$taxratelist);
        //商品类型
        $mapType['status'] = array('eq',1);
        $mapType['parentId'] = array('eq',0);
        $typelist = getTable('Type',$mapType,2);  
        //目录树
        foreach ($typelist as $key => $value) {
            $typelist[$key]['treePathName'] = getTreeTypeName($value['id']);
        }
        $this->assign('goodsType',$typelist); 

        $id = I('id');
        $model = M('Goods');
        $map['id'] = array('eq',$id);
        $list = $model->where($map)->find();
        $tmp = getField($list['typeId'],'parentId','Type');
        if(!empty($tmp)){
            $list['typeId1'] = $tmp;
            $list['typeId2'] = $list['typeId'];
        }else{
            $list['typeId1'] = $list['typeId'];
            $list['typeId2'] = '';
        }
        $list['imgArr'] = explode(',', $list['imgs']);
        // dump($list);
        $this->assign('list',$list);
        $this->display();
    }
    //updateGoods
    public function updateGoods(){ 
        $pData = $_POST; 
        if(!empty($pData['type1'])){
            $pData['type'] = $pData['type1'];
        }
        if(!empty($pData['type2'])){
            $pData['type'] = $pData['type2'];
        } 
        if(!empty($pData['desc'])){
            $pData['desc'] = $pData['desc'];
        } 
        if(!empty($pData['groupPrice'])){
            $pData['groupPrice'] = $pData['groupPrice'];
        } 
        if(!empty($pData['marketPrice'])){
            $pData['marketPrice'] = $pData['marketPrice'];
        } 
        if(!empty($pData['name'])){
            $pData['name'] = $pData['name'];
        } 
        if(!empty($pData['stock'])){
            $pData['stock'] = $pData['stock'];
        } 
        if(!empty($pData['vipPrice'])){
            $pData['vipPrice'] = $pData['vipPrice'];
        } 
        $pData['updatetime'] = time();
        $model = M('Goods');

        // $taxrateId = $pData['taxrateId'];
        // $pData['taxrateId'] = $taxrateId;

        $test = getField($pData['taxrateId'],'revenue','Taxrate');
        $pData['taxrate'] = (int)substr($test, 0,-1)/100;

        $map['id'] = $pData['id'];
        $res = $model->where($map)->save($pData);
        $debug = $model->getLastSql();
        if($res){
            $pData['id'] = $res;
            $this->ajaxReturn(array('code'=>2000,'msg'=>"操作成功",'data'=>$pData));
        }else{ 
            $this->ajaxReturn(array('code'=>-6002,'msg'=>"操作失败",'debug'=>$debug));
        }
    }
    public function address(){
        $mProvince = M('Province');
        $dProvince = $mProvince->select();
        $this->assign('dProvince',$dProvince);

        $mAddress = M('Address');
        $mapAdd['mId'] = array('eq',$_SESSION['memberId']);
        $dAddress = $mAddress->where($mapAdd)->select();
        $this->assign('aAddress',$dAddress);
    	$this->display();
    }
    //删除地址
    public function delete(){ 
        $model = D("Address");
        if(!empty($model)){
            $pk = $model->getPk();
            $id = $_REQUEST[$pk];
            if( isset($id) ){
                $where[$pk] =  array('in',explode(',', $id));
                $list = $model->where($where)->delete();
                $debug = $model->getLastSql();
                if(false !== $list){
                    $this->appReturn(2000,'数据删除成功');
                }else{
                    $this->appReturn(-6002,'数据删除失败',$debug);
                }
            }else{
                $this->appReturn(-6001,'非法操作');
            }
        }// end if(!empty($model))
    }
    //add address
    public function insertAddress(){ 
        $data['mId'] = $_SESSION['memberId'];
        $data['name'] = I('username');
        $data['provinceId'] = I('province');
        $data['cityId'] = I('city');
        $data['areaId'] = I('area');
        $data['specificAddr'] = getProvinceName(I('province')).getCityName(I('city')).getAreaName(I('area'));
        $data['addr'] = I('addressInfo');
        $data['tel'] = I('tel');
        $data['postcode'] = I('youzheng');
        $data['createtime'] = time();
        $model = M('Address');
        $res = $model->add($data);
        if($res){
            $data['id'] = $res;
            $this->ajaxReturn(array('code'=>2000,'msg'=>"操作成功",'data'=>$data));
        }else{ 
            $this->ajaxReturn(array('code'=>-6002,'msg'=>"操作失败"));
        }
    }
    public function score(){
        $mVipchangelog = D('Vipchangelog');
        $mapVipchangelog['memberId'] = array('eq',$_SESSION['memberId']);
        $dVipchangelog = $mVipchangelog->where($mapVipchangelog)->order('createtime desc')->select();
        $this->assign('dVipchangelog',$dVipchangelog);
    	$this->display();
    }
    public function changePass(){
    	$this->display();
    }
    public function profile(){ 
        $model = M('Member');
        $map['id'] = array('eq',$_SESSION['memberId']);
        $list = $model->where($map)->find();
        $this->assign('list',$list);
        $this->display();
    }
    //更改用户信息
    public function updateUser(){
        $pData = $_POST;
        if(!empty($pData['imgs'])){
            $data['imgUrl'] = $pData['imgs'];
        }
        if(!empty($pData['name'])){
            $data['name'] = $pData['name'];
        }
        if(!empty($pData['nickname'])){
            $data['nickname'] = $pData['nickname'];
        }
        if(!empty($pData['tel'])){
            $data['tel'] = $pData['tel'];
        }
        if(!empty($pData['sex'])){
            $data['sex'] = $pData['sex'];
        }
        if(!empty($pData['email'])){
            $data['email'] = $pData['email'];
        }
        $model = M('Member');
        $map['id'] = array('eq',$pData['id']);
        $res = $model->where($map)->save($data);
        if($res !== 0 && !$res){
            $this->ajaxReturn(array('code'=>-6002,'msg'=>"操作失败"));
        }else{
            $this->ajaxReturn(array('code'=>2000,'msg'=>"操作成功",'data'=>$pData));
        }
    }
    public function groupVerify(){
        $this->display();
    }
    public function groupVerifyResult(){
        // $model = M('Membergroup');
        // $map['memberId'] = array('eq',$_SESSION['memberId']);
        // $res = $model->where($map)->find();
        // $this->assign('status',$res['status']);
        $model = M('Member');
        $map['id'] = array('eq',$_SESSION['memberId']);
        $res = $model->where($map)->find();
        $this->assign('status',$res['isAttestation']); 
        $this->display();
    }
    public function addGroupVerify(){
        
        
        $this->display();
    }
    public function insertGroupVerify(){
        $pData = $_POST;
        $tmp = array();
        foreach ($pData['memName'] as $key => $value) {
            if(!empty($value['memName']) && !empty($value['memCode'])){
                $tmp[$key]['memName'] = $pData['memName'][$key];
                $tmp[$key]['memCode'] = $pData['memCode'][$key];
            }
        }

        $data['memberId'] = $_SESSION['memberId'];
        $data['membergroup'] = json_encode($tmp);
        $data['organization'] = $pData['organization'];
        $data['contacts'] = $pData['contacts'];
        $data['phone'] = $pData['phone'];
        $data['remark'] = $pData['remark'];
        $data['status'] = 1;
        $data['createtime'] = time();

        $model = M('Membergroup');
        $res = $model->add($data);
        //申请团体
        $aGroup = applyGroup($_SESSION['memberId'],'isAttestation',3);
        //if($res){
        if($res && $aGroup){
            $this->redirect('User/groupVerifyResult',array('subSq'=>1));
        }else{
            $this->error('操作失败');
        }
    }
    //vip
    public function addVipVerify(){ 
        $model = M('Sysconfig');
        $map['id'] = array('eq',1);    //数据库中手动指定字段ID 1
        $list = $model->where($map)->find();
        $this->assign('feiyong',$list['content']);
        $this->display();
    }
}
?>