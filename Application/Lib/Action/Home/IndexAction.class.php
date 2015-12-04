<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends BaseAction {
    public function __construct(){
        parent::__construct();
        if (isset($_SESSION['user'])){
            $this->assign("isLogin",1);
        }
        else{
            $this->assign("isLogin",0);
        }
        $this->assign("isLoginPage",0);
    }
    
    public function index(){
        $this->display();
    }

    public function nindex(){
        $this->display();
    }
    //申请代购
    public function application(){
        $this->display();
    }
    //购物车
    public function buycar(){
        $this->display();
    }
    // 积分兑换
    public function scoreExchange(){
        $model = D('Goods');
        $pageNo = I('pageNo');
        $pageSize = I('pageSize');
        if($pageNo==""){
            $pageNo = 1;
        }
        if($pageSize==""){
            $pageSize = 10;
        }
        $map['status'] = array('eq',1); //上架商品
        $con = 1;
        if($con==1){
            $map['isOrNoExchange'] = array('eq',1);
            
            $total = $model->where($map)->count();
            $totalPage = ceil($total/$pageSize);
            $goods = $model->where($map)->page($pageNo,$pageSize)->select();
            foreach($goods as $k=>$v){
                $imgArr = explode(',',$v['imgs']); 
                $goods[$k]['imgsArr']=$imgArr;
            }
            $this->assign('searchlist',$goods);
            $this->assign('totalPage',$totalPage);
        }
        $this->display();
    }
    // VIP专区
    public function vipZone(){
        $model = D('Goods');
        $pageNo = I('pageNo');
        $pageSize = I('pageSize');
        if($pageNo==""){
            $pageNo = 1;
        }
        if($pageSize==""){
            $pageSize = 10;
        }
        $map['m_goods.status'] = array('eq',1); //上架商品
        $con = 2;
        if($con==2){
            $map['m_goods.isOrNoExchange'] = array('eq',2); 
            $total = $model->where($map)->join('m_viplevel ON m_viplevel.id=m_goods.viplevelId')->count();
            $totalPage = ceil($total/$pageSize);
            $goods = $model->order("m_goods.sales desc")->where($map)->join('m_viplevel ON m_viplevel.id=m_goods.viplevelId')->field('m_goods.*')->page($pageNo,$pageSize)->select();
            foreach($goods as $k=>$v){
                $imgArr = explode(',',$v['imgs']); 
                $goods[$k]['imgsArr']=$imgArr;
            }
            
            $this->assign('searchlist',$goods);
            $this->assign('totalPage',$totalPage);
        }
        $this->display();
    }
    // 代金券专区
    public function coupon(){
        $this->display();
    }
    // 文章
    public function article(){
        $name = I('name');
        $title = I('title');
        if($title!=null){
            $model = D('Article');
            $title = I('title');
            $map['title'] = array('eq',$title);
            $articles = $model->where($map)->find();
            $str = unserialize($articles['content']);
            $articles['content'] = htmlspecialchars_decode($str);  //html标签解码
            $articles['content'] = str_replace('\\', "", $articles['content']);  //html标签解码
            $this->assign('list',$articles);
            $this->display();
            return;
        }
        if($name=="在线申请"){
            if (isset($_SESSION['user'])){ 
            }
            else{
                $this->redirect('Login/index');
            }    
        }
        $model = M('Type');
        $map1['m_type.name'] = array('eq',$name);
        $list = $model->where($map1)->join('m_article ON m_article.typeId=m_type.id')->find();
        
        $str = unserialize($list['content']);  //对数据库中序列化的字符串进行解码
        $list['content'] = htmlspecialchars_decode($str);  //html标签解码
        $list['content'] = str_replace('\\', "", $list['content']);  //html标签解码
        $this->assign('list',$list);
        $this->display();
    }
    //团体认证
    public function groupVerify(){
        $name = I('name');
        $title = I('title');
        if($title!=null){
            $model = D('Article');
            $title = I('title');
            $map['title'] = array('eq',$title);
            $articles = $model->where($map)->find();
            $str = unserialize($articles['content']);
            $articles['content'] = htmlspecialchars_decode($str);  //html标签解码
            $this->assign('list',$articles);
            $this->display();
            return;
        }
        if($name=="在线申请"){
            if (isset($_SESSION['user'])){ 
            }
            else{
                $this->redirect('Login/index');
            }    
        }
        $model = M('Type');
        $map1['m_type.name'] = array('eq',$name);
        $list = $model->where($map1)->join('m_article ON m_article.typeId=m_type.id')->find();
        
        $str = unserialize($list['content']);  //对数据库中序列化的字符串进行解码
        $list['content'] = htmlspecialchars_decode($str);  //html标签解码
        $this->assign('list',$list);
        $this->display();
    }
    //在线申请
    public function applic(){
        if (isset($_SESSION['user'])){
            $this->assign("isLogin",1);
        }
        else{
            $this->assign("isLogin",0);
            $this->redirect("Login/index");
        }
        $this->display();
    }
    
    // 搜索
    public function search(){
        $name = I('name');
        $type = I('tid');
        $sid = I('sid');
        $brandId = I('brandId');
        // dump($_SESSION['search_pageno']);
        if(empty($_SESSION['searchPageNo'])){
                $pageNo = 1;
                $_SESSION['searchPageNo'] = 1;
        }else{
                $pageNo = $_SESSION['searchPageNo'];
        }
        
        $pageSize = 8;
        if($name!=""){
            //1、处理空格
            $name = urlencode($name);
            $name = str_replace("%C2%A0","%20",$name);
            $name = urldecode($name);
            $model = D('Goods');
            $map['name'] = array('like',"%$name%");
            $map['status'] = array('eq',1);
            if(!empty($brandId)){
                $map['brandId'] = array('eq',$brandId); //$brandId
            }
            // $total = $model->where($map)->count();
            $goodsAll = $model->where($map)->select();
            if(!empty($goodsAll)){
                    foreach($goodsAll as $k=>$v){
                        $hotimgStr = $goodsAll[$k]['imgs'];
                        $hotimgArr = explode(',',$hotimgStr);
                        $goodsAll[$k]['imgsArr'] = $hotimgArr;
                    }
                    // dump($goodsAll);
                    //产品搜索优化
                    $nameArr = array();
                    $map = array();
                    $map['status'] = array('eq',1);
                    for($i=0;$i<strlen($name)/3;$i++){
                        if(mb_substr($name,$i,1,'utf-8')!=""){
                            $nameArr[$i] = mb_substr($name,$i,1,'utf-8');
                            $map['name'] = array('like',"%".$nameArr[$i]."%");
                            $map['brandId'] = array('eq',$brandId); //$brandId
                            $goodOne = $model->where($map)->select();
                            // dump(count($goodOne));
                            // dump($model->getLastSql());
                            for($q=0;$q<count($goodOne);$q++){
                                $is_has = 0;
                                // dump($goodOne); 
                                for($a=0;$a<count($goodsAll);$a++){
                                        if($goodsAll[$a]["id"]===$goodOne[$q]["id"]){
                                            $is_has += 1;
                                        }
                                }
                                if($is_has==0){
                                        $hotimgStr = $goodOne[$q]['imgs'];
                                        $hotimgArr = explode(',',$hotimgStr);
                                        $goodOne[$q]['imgsArr'] = $hotimgArr;
                                        $goodsAll[count($goodsAll)] = $goodOne[$q];
                                }
                            }
                        }
                    }
                    $totalPage = ceil(count($goodsAll)/$pageSize);  //总页数

                    $goods = array();
                    for($z=0;$z<$pageSize;$z++){
                        if(!empty($goodsAll[($pageNo-1)*$pageSize+$z])){
                            $goods[$z] = $goodsAll[($pageNo-1)*$pageSize+$z];
                        }
                    }
                  
                    // dump(count($goods));
                    $typeId = $goods[0]['typeId'];
                    
                    $model = D('Type');
                    $map3['m_type.id'] = array('eq',$typeId);
                    $map3['m_goods.status'] = array('eq',1);
                    $hotGoods = $model->where($map3)->join('m_goods ON m_goods.typeId=m_type.id')->field('m_goods.*,m_type.name typename,m_type.parentId parentId')->limit(2)->select();
                    foreach($hotGoods as $k=>$v){
                        $hotimgStr = $hotGoods[$k]['imgs'];
                        $hotimgArr = explode(',',$hotimgStr);
                        $hotGoods[$k]['imgsArr'] = $hotimgArr;
                    }
             }
            $this->assign('hotgoodInfos',$hotGoods);
            $this->assign('search',$name);
            $this->assign('searchlist',$goods);
            $this->assign('totalPage',$totalPage);
            $this->assign('searchPageno',$_SESSION['searchPageNo']);
            // dump($goods);
        }
        if($type!=""){
            $typeIdArr = getTreeTypeIdDown($type);  // array(2,11,12,13);
            $model = D('Goods');
            $map['m_type.id'] = array('in',$typeIdArr);
            $map['m_goods.status'] = array('eq',1);
            if(!empty($brandId)){
                $map['m_goods.brandId'] = array('eq',$brandId); //$brandId
            }
            $total = $model->where($map)->join('m_type ON m_type.id=m_goods.typeId')->field('m_goods.*')->count();
            $totalPage = ceil($total/$pageSize);
            $goods = $model->where($map)->join('m_type ON m_type.id=m_goods.typeId')->field('m_goods.*')->page($pageNo,$pageSize)->select();
            foreach($goods as $k=>$v){
                $hotimgStr = $goods[$k]['imgs'];
                $hotimgArr = explode(',',$hotimgStr);
                $goods[$k]['imgsArr'] = $hotimgArr;
            }
            $typeId = $goods[0]['typeId'];
            
            $model = D('Type');
            $map3['m_type.id'] = array('eq',$typeId);
            $hotGoods = $model->where($map3)->join('m_goods ON m_goods.typeId=m_type.id')->field('m_goods.*,m_type.name typename,m_type.parentId parentId')->limit(C('LIST_NUM'))->select();
            foreach($hotGoods as $k=>$v){
                $hotimgStr = $hotGoods[$k]['imgs'];
                $hotimgArr = explode(',',$hotimgStr);
                $hotGoods[$k]['imgsArr'] = $hotimgArr;
            }
            $this->assign('hotgoodInfos',$hotGoods);
            if (empty($_GET["name"])) {
            	$this->assign('search',getTypeName($_GET["tid"]));
            }
            else {
            	$this->assign('search',$type);
            }
            
            $this->assign('searchlist',$goods);
            $this->assign('totalPage',$totalPage);
        }
        if($sid!=""){
            // $typeIdArr = getTreeTypeIdDown($type);  // array(2,11,12,13);
            $model = D('Goods');
            $map['m_type.id'] = array('in',$sid);
            $map['m_goods.status'] = array('eq',1);
            if(!empty($brandId)){
                $map['m_goods.brandId'] = array('eq',$brandId);
            }
            
            $total = $model->where($map)->join('m_type ON m_type.id=m_goods.typeId')->field('m_goods.*')->count();
            $totalPage = ceil($total/$pageSize);
            $goods = $model->where($map)->join('m_type ON m_type.id=m_goods.typeId')->field('m_goods.*')->page($pageNo,$pageSize)->select();
            foreach($goods as $k=>$v){
                $hotimgStr = $goods[$k]['imgs'];
                $hotimgArr = explode(',',$hotimgStr);
                $goods[$k]['imgsArr'] = $hotimgArr;
            }
            $typeId = $goods[0]['typeId'];
            
            $model = D('Type');
            $map3['m_type.id'] = array('eq',$typeId);
            $hotGoods = $model->where($map3)->join('m_goods ON m_goods.typeId=m_type.id')->field('m_goods.*,m_type.name typename,m_type.parentId parentId')->limit(C('LIST_NUM'))->select();
            foreach($hotGoods as $k=>$v){
                $hotimgStr = $hotGoods[$k]['imgs'];
                $hotimgArr = explode(',',$hotimgStr);
                $hotGoods[$k]['imgsArr'] = $hotimgArr;
            }
            $this->assign('hotgoodInfos',$hotGoods);
            if (empty($_GET["name"])) {
                $this->assign('search',getTypeName($sid));
            }
            else {
                $this->assign('search',$type);
            }
            
            $this->assign('searchlist',$goods);
            $this->assign('totalPage',$totalPage);
        }
        $goodsBrand = M('Goodsbrand')->select();
        $this->assign("brands",$goodsBrand);

        $this->display();
    }
    /**
    * 首页
    */
    public function getAll(){ 
        $model = D('Type');
        $list = $model->select();   
        if(!$list){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'商品类型为空'));
        }
        $model = D('Goods');
        $map1['m_goods.status'] = array('eq',1); //上架商品
        $hot = $model->where($map1)->order('m_goods.sales desc')->join('m_goodsbrand ON m_goods.brandId=m_goodsbrand.id')->field('m_goods.*,m_goodsbrand.name brandname')->select();
        if(!$hot){
            $this->ajaxReturn(array('code'=>-3001,'msg'=>'热销排行为空'));
        }
        foreach($hot as $k=>$v){
            $hotimgStr = $hot[$k]['imgs'];
            $hotimgArr = explode(',',$hotimgStr);
            $hot[$k]['imgsArr'] = $hotimgArr;
        }
        
        $model = D('Type');
        $map['isIndex'] = array('eq',1);
        $map['status'] = array('eq',1);
        $goodsSub = $model->where($map)->select();
        //对子模块商品转换数组
        $mGoods = M('Goods');
        foreach($goodsSub as $k=>$v){
            $tmp = explode(',',$v['remark']);
            $goodsSub[$k]['goodsArrIds'] = $tmp;
            
            $mapGoods['m_goods.id'] = array('in',$tmp); 
            $goodsSub[$k]['goodsArr'] = $mGoods->where($mapGoods)->join('m_goodsbrand ON m_goodsbrand.id=m_goods.brandId')->field('m_goods.*,m_goodsbrand.name as brandname')->select();
        }
        $goods = $goodsSub;
        if(!$goods){
            $this->ajaxReturn(array('code'=>-3002,'msg'=>'首页子模块为空'));   //6条显示一次，6条显示一次
        }
        foreach($goods as $k=>$v){
            $ziF = $goods[$k]['goodsArr'];
            foreach($ziF as $q=>$w){
                $hotimgStr = $ziF[$q]['imgs'];
                $hotimgArr = explode(',',$hotimgStr);
                $goods[$k]['goodsArr'][$q]['imgsArr'] = $hotimgArr;
            }
            
        }
        
        $model = D('Keyword');
        $keyword = $model->select();
        if(!$keyword){
            $this->ajaxReturn(array('code'=>-3004,'msg'=>'热门搜索关键字为空'));
        }
        $model = D('Article');
        $map['typeId'] = array('eq',28);
        $map['status'] = array('eq',1);
        $article = $model->select();
        if(!$article){
            $this->ajaxReturn(array('code'=>-3005,'msg'=>'热门资讯为空'));
        }
        $model = D('Sysconfig');
        $mapBrand['their'] = array('eq','hatBrand');
        $brands = $model->where($mapBrand)->select();
        if(!$brands){
            $this->ajaxReturn(array('code'=>-3006,'msg'=>'热销品牌为空'));
        }
        $model = D('Sysconfig');
        $mapVip['their'] = array('eq','vipCode');
        $vipOpen = $model->where($mapVip)->select();
        $this->ajaxReturn(array('code'=>2000,'msg'=>'数据加载成功',
                                'data'=>array('list'=>$list,'hots'=>$hot,'goods'=>$goods,'keywords'=>$keyword,'article'=>$article,'brands'=>$brands,'vipOpen'=>$vipOpen)));
    }
    /**
    *积分兑换1，VIP专区2，代金券专区3
    */
    public function isOrNoExchange(){
        $model = D('Goods');
        $pageNo = I('pageNo');
        $pageSize = I('pageSize');
        if($pageNo==""){
            $pageNo = 1;
        }
        if($pageSize==""){
            $pageSize = 8;
        }
        $map['status'] = array('eq',1); //上架商品
        $con = I('con');
        if($con==1){
            $map['isOrNoExchange'] = array('eq',1);
            $total = $model->where($map)->count();
            $totalPage = ceil($total/$pageSize);
            $goods = $model->where($map)->page($pageNo,$pageSize)->select();
            if(!$goods){
                $this->ajaxReturn(array('code'=>-3000,'msg'=>'商品为空'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'数据加载成功','data'=>array('list'=>$goods),'totalPage'=>$totalPage));
        }
        if($con==2){
            $map['isOrNoExchange'] = array('eq',2);
            
            $total = $model->where($map)->count();
            $totalPage = ceil($total/$pageSize);
            $goods = $model->where($map)->page($pageNo,$pageSize)->select();
            if(!$goods){
                $this->ajaxReturn(array('code'=>-3001,'msg'=>'商品为空'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'数据加载成功','data'=>array('list'=>$goods),'totalPage'=>$totalPage));
        }
        if($con==3){
            $map['isOrNoExchange'] = array('eq',3);
            
            $total = $model->where($map)->count();
            $totalPage = ceil($total/$pageSize);
            $goods = $model->where($map)->page($pageNo,$pageSize)->select();
            if(!$goods){
                $this->ajaxReturn(array('code'=>-3002,'msg'=>'商品为空'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'数据加载成功','data'=>array('list'=>$goods),'totalPage'=>$totalPage));
        }
        
    }
    /**
    *积分兑换中，根据分值区间进行查询
    */
    public function convertScoreByScore(){
        $model = D('Goods');
        $stScore = I('start');
        $edScore = I('end');
        $pageNo = I('pageNo');
        $pageSize = I('pageSize');
        if($pageNo==""){
            $pageNo = 1;
        }
        if($pageSize==""){
            $pageSize = 10;
        }
        if($stScore!="0"&&$edScore!="0"){
            $map['score'] = array('between',array($stScore,$edScore));
        }else if($stScore!="0"&&$edScore=="0"){
            $map['score'] = array('egt',$stScore);
        }else{
            $map['score'] = array('egt',0);
        }
        
        $map['isOrNoExchange'] = array('eq',1);
        $map['status'] = array('eq',1);
//        var_dump($map);exit;
        $total = $model->where($map)->count();
        $totalPage = ceil($total/$pageSize);
        $goods = $model->where($map)->page($pageNo,$pageSize)->select();
        foreach($goods as $k=>$v){
            $imgArr = explode(',',$v['imgs']); 
            $goods[$k]['imgsArr']=$imgArr;
        }
        $debug = $model->getLastSql();
        
        if(!$goods){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'商品为空'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'数据加载成功','data'=>array('list'=>$goods),'totalPage'=>$totalPage));
    }
    /**
    *查找所有的VIP等级
    */
    public function getAllVipLevel(){
        $model = D('Viplevel');
        $vips = $model->select();
        if(!$vips){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'查找失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'查找成功','data'=>array('list'=>$vips)));
    }
    
    /**
    *根据VIP等级名称来查找对应的商品集合
    */
    public function getGoodsByVipLevel(){
        $model = D('Viplevel');
        $vipName = I('vipName');
        $pageNo = I('pageNo');
        $pageSize = I('pageSize');
        if($pageNo==""){
            $pageNo = 1;
        }
        if($pageSize==""){
            $pageSize = 10;
        }
        if($vipName!=""){
            $map['m_viplevel.name'] = array('eq',$vipName);
        }
        $map['m_goods.status'] = array('eq',1);  //上架商品
        $map['m_goods.isOrNoExchange'] = array('eq',2);
        $total = $model->order("sales desc")->where($map)->join('m_goods ON m_viplevel.id=m_goods.viplevelId')->count();
        $totalPage = ceil($total/$pageSize);
        $goods = $model->order("sales desc")->where($map)->join('m_goods ON m_viplevel.id=m_goods.viplevelId')->page($pageNo,$pageSize)->select();
        foreach($goods as $k=>$v){
            $imgArr = explode(',',$v['imgs']); 
            $goods[$k]['imgsArr']=$imgArr;
        }
//        var_dump($goods);
        if(!$goods){
            $this->ajaxReturn(array('code'=>-3000,'msg'=>'商品列表为空'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'查找成功','data'=>array('list'=>$goods),'totalPage'=>$totalPage));
    }
    
    /**
    *查询代金券信息
    */
    public function getVoucher(){
        $model = D('Type');
        $map['status'] = array('eq',4);
        $voucher = $model->where($map)->select();
        if(!$voucher){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'查询失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'查询成功','data'=>array('list'=>$voucher)));
    }
    /**
    *根据代金券名称查找对应的商品集合
    */
    public function getGoodsByVoucher(){
        $model = D('Type');
        $voucher = I('voucher');
        $map['m_type.name'] = array('eq',$voucher);
        $goods = $model->where($map)->join('m_voucherinfo ON m_type.id=m_voucherinfo.typeId')->select();
        if(!$goods){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'查找失败'));
        }
        foreach($goods as $k=>$v){
            $goodIds = explode(',',$v['suitId']);
            $model = D('Goods');
            $goods = array();
            $map1['id'] = array('in',$goodIds);
            $map1['isOrNoExchange'] = array('eq',3);
            $map1['status'] = array('eq',1);       //上架商品
            $goods[] = $model->where($map1)->select();
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'查找成功','data'=>array('list'=>$goods)));    
    }
    
    /**
    *搜索框，热门搜索(按热门搜索关键字、商品名字name或商品类型type)(相关商品：取集合中的前10个商品进行显示)
    */
    public function searchData(){
        $name = I('name');
        $type = I('type');
        if($name!=""){
            $model = D('Goods');
            $map['name'] = array('like',"%$name%");
            $map['status'] = array('eq',1);
            $goods = $model->where($map)->select();
            if(!$goods){
                $this->ajaxReturn(array('code'=>-3001,'msg'=>'商品为空'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'获取商品成功','data'=>array('list'=>$goods,'name'=>$name)));
        }
        if($type!=""){
            $model = M('Type');
            $map['m_type.name'] = array('eq',$type);
            $map['m_goods.status'] = array('eq',1); //上架商品
            $goods = $model->where($map)->join('m_goods ON m_goods.typeId=m_type.id')->select();
            
            if(!$goods){
                $this->ajaxReturn(array('code'=>-3001,'msg'=>'商品为空'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'获取商品成功','data'=>array('list',$goods,'typeName'=>$type)));
        }
        $this->ajaxReturn(array('code'=>-2001,'msg'=>'参数错误'));
    }
    
    /**
    *搜索中查询条件（按销量1，价格2，上架时间3）
    */
    public function goodsSort(){
        $con = I('con');
        $pageNo = I('pageNo');
        $_SESSION['sortPageNo'] = $pageNo;
        $pageSize = I('pageSize');
        $name = I('name');  //商品名字模糊字
        $name = urlencode($name);
        $name = str_replace("%C2%A0","%20",$name);
        $name = urldecode($name);

        if($pageNo==""){
            $pageNo = 1;
        }
        $con = (int)$con;
        if($pageSize==""){
            $pageSize = 8;
        }
        $model = D('Goods');
        if (empty($_POST["tid"])) {
        	$map['name'] = array('like',"%$name%");
        }
        else {
        	$typeIdArr = getTreeTypeIdDown($_POST["tid"]);  // array(2,11,12,13);
        	$map['typeId'] = array('in',$typeIdArr);
        	
        }
        $map['status'] = array('eq',1);
        if($con==1){
            if(!empty($map['typeId'])){
                    $total = $model->order('sales desc')->where($map)->count();
                    $totalPage = ceil($total/$pageSize);
                    $goods = $model->order('sales desc')->where($map)->page($pageNo,$pageSize)->select();
                    foreach($goods as $k=>$v){
                        $hotimgStr = $goods[$k]['imgs'];
                        $hotimgArr = explode(',',$hotimgStr);
                        $goods[$k]['imgsArr'] = $hotimgArr;
                    }
            }else{
                    $goodsAll = $model->order('sales desc')->where($map)->select();
                    foreach($goodsAll as $k=>$v){
                        $hotimgStr = $goodsAll[$k]['imgs'];
                        $hotimgArr = explode(',',$hotimgStr);
                        $goodsAll[$k]['imgsArr'] = $hotimgArr;
                    }
                    //产品搜索优化
                    $nameArr = array();
                    $map = array();
                    $map['status'] = array('eq',1);
                    for($i=0;$i<strlen($name)/3;$i++){
                            if(mb_substr($name,$i,1,'utf-8')!=""){
                            $nameArr[$i] = mb_substr($name,$i,1,'utf-8');
                            $map['name'] = array('like',"%".$nameArr[$i]."%");
                            $goodOne = $model->where($map)->order('sales desc')->select();
                            for($q=0;$q<count($goodOne);$q++){
                                $is_has = 0;
                                for($a=0;$a<count($goodsAll);$a++){
                                        if($goodsAll[$a]["id"]===$goodOne[$q]["id"]){
                                            $is_has += 1;
                                        }
                                }
                                if($is_has==0){
                                        $hotimgStr = $goodOne[$q]['imgs'];
                                        $hotimgArr = explode(',',$hotimgStr);
                                        $goodOne[$q]['imgsArr'] = $hotimgArr;
                                        $goodsAll[count($goodsAll)] = $goodOne[$q];
                                }
                            }
                        }
                    }
                    $totalPage = ceil(count($goodsAll)/$pageSize);  //总页数
                    $goods = array();
                    $syCount = count($goodsAll) - ($pageNo-1)*$pageSize;
                    if($syCount<$pageSize){
                            for($z=0;$z<$syCount;$z++){
                                    $goods[$z] = $goodsAll[($pageNo-1)*$pageSize+$z];
                            }
                    }else{
                            for($z=0;$z<$pageSize;$z++){
                                    $goods[$z] = $goodsAll[($pageNo-1)*$pageSize+$z];
                            }
                    }
            }

            if(!$goods){
                $this->ajaxReturn(array('code'=>-2001,'msg'=>'商品为空'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'获取商品成功','data'=>array('list'=>$goods),'totalPage'=>$totalPage,'sum'=>count($goodsAll),'pg'=>$_SESSION['search_pageno']));
        }
        if($con==2){ 
            if(!empty($map['typeId'])){
                    $total = $model->order('marketPrice desc')->where($map)->count();
                    $totalPage = ceil($total/$pageSize);
                    $goods = $model->order('marketPrice desc')->where($map)->page($pageNo,$pageSize)->select();
                    foreach($goods as $k=>$v){
                        $hotimgStr = $goods[$k]['imgs'];
                        $hotimgArr = explode(',',$hotimgStr);
                        $goods[$k]['imgsArr'] = $hotimgArr;
                    }
            }else{
                    $goodsAll = $model->order('marketPrice desc')->where($map)->select();
                    foreach($goodsAll as $k=>$v){
                        $hotimgStr = $goodsAll[$k]['imgs'];
                        $hotimgArr = explode(',',$hotimgStr);
                        $goodsAll[$k]['imgsArr'] = $hotimgArr;
                    }
                    //产品搜索优化
                    $nameArr = array();
                    $map = array();
                    $map['status'] = array('eq',1);
                    for($i=0;$i<strlen($name)/3;$i++){
                            if(mb_substr($name,$i,1,'utf-8')!=""){
                            $nameArr[$i] = mb_substr($name,$i,1,'utf-8');
                            $map['name'] = array('like',"%".$nameArr[$i]."%");
                            $goodOne = $model->where($map)->order('marketPrice desc')->select();
                            for($q=0;$q<count($goodOne);$q++){
                                $is_has = 0;
                                for($a=0;$a<count($goodsAll);$a++){
                                        if($goodsAll[$a]["id"]===$goodOne[$q]["id"]){
                                            $is_has += 1;
                                        }
                                }
                                if($is_has==0){
                                        $hotimgStr = $goodOne[$q]['imgs'];
                                        $hotimgArr = explode(',',$hotimgStr);
                                        $goodOne[$q]['imgsArr'] = $hotimgArr;
                                        $goodsAll[count($goodsAll)] = $goodOne[$q];
                                }
                            }
                        }
                    }
                    $totalPage = ceil(count($goodsAll)/$pageSize);  //总页数
                    $goods = array();
                    $syCount = count($goodsAll) - ($pageNo-1)*$pageSize;
                    if($syCount<$pageSize){
                            for($z=0;$z<$syCount;$z++){
                                    $goods[$z] = $goodsAll[($pageNo-1)*$pageSize+$z];
                            }
                    }else{
                            for($z=0;$z<$pageSize;$z++){
                                    $goods[$z] = $goodsAll[($pageNo-1)*$pageSize+$z];
                            }
                    }
            }
            if(!$goods){
                $this->ajaxReturn(array('code'=>-2001,'msg'=>'商品为空'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'获取商品成功','data'=>array('list'=>$goods),'totalPage'=>$totalPage));
        }
        if($con==3){  
            if(!empty($map['typeId'])){
                    $total = $model->order('createtime desc')->where($map)->count();
                    $totalPage = ceil($total/$pageSize);
                    $goods = $model->order('createtime desc')->where($map)->page($pageNo,$pageSize)->select();
                    foreach($goods as $k=>$v){
                        $hotimgStr = $goods[$k]['imgs'];
                        $hotimgArr = explode(',',$hotimgStr);
                        $goods[$k]['imgsArr'] = $hotimgArr;
                    }
            }else{
                    $goodsAll = $model->order('createtime desc')->where($map)->select();
                    foreach($goodsAll as $k=>$v){
                        $hotimgStr = $goodsAll[$k]['imgs'];
                        $hotimgArr = explode(',',$hotimgStr);
                        $goodsAll[$k]['imgsArr'] = $hotimgArr;
                    }
                    //产品搜索优化
                    $nameArr = array();
                    $map = array();
                    $map['status'] = array('eq',1);
                    for($i=0;$i<strlen($name)/3;$i++){
                            if(mb_substr($name,$i,1,'utf-8')!=""){
                            $nameArr[$i] = mb_substr($name,$i,1,'utf-8');
                            $map['name'] = array('like',"%".$nameArr[$i]."%");
                            $goodOne = $model->where($map)->order('createtime desc')->select();
                            for($q=0;$q<count($goodOne);$q++){
                                $is_has = 0;
                                for($a=0;$a<count($goodsAll);$a++){
                                        if($goodsAll[$a]["id"]===$goodOne[$q]["id"]){
                                            $is_has += 1;
                                        }
                                }
                                if($is_has==0){
                                        $hotimgStr = $goodOne[$q]['imgs'];
                                        $hotimgArr = explode(',',$hotimgStr);
                                        $goodOne[$q]['imgsArr'] = $hotimgArr;
                                        $goodsAll[count($goodsAll)] = $goodOne[$q];
                                }
                            }
                        }
                    }
                    $totalPage = ceil(count($goodsAll)/$pageSize);  //总页数
                    $goods = array();
                    $syCount = count($goodsAll) - ($pageNo-1)*$pageSize;
                    if($syCount<$pageSize){
                            for($z=0;$z<$syCount;$z++){
                                    $goods[$z] = $goodsAll[($pageNo-1)*$pageSize+$z];
                            }
                    }else{
                            for($z=0;$z<$pageSize;$z++){
                                    $goods[$z] = $goodsAll[($pageNo-1)*$pageSize+$z];
                            }
                    }
            }
            
            if(!$goods){
                $this->ajaxReturn(array('code'=>-2001,'msg'=>'商品为空'));
            }
            $this->ajaxReturn(array('code'=>2000,'msg'=>'获取商品成功','data'=>array('list'=>$goods),'totalPage'=>$totalPage));
        }
    }
    /**
    *根据热门资讯名称查询资讯信息
    */
    public function getArticleByName(){
        $model = D('Article');
        $title = I('title');
        $map['title'] = array('eq',$title);
        $articles = $model->where($map)->select();
        if(!$articles){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'获得资讯信息失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获得资讯信息成功','data'=>array('list'=>$articles)));
    }
    /**
    *首页显示的广告
    */
    public function getAllAdvert(){
        $model = D('Sysconfig');
        $map['their'] = array('eq',I('mapStr','banner'));
        $adverts = $model->where($map)->select();
        if(!$adverts){
            $this->ajaxReturn(array('code'=>-2001,'msg'=>'获得广告失败'));
        }
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获得广告成功','data'=>array('list'=>$adverts)));
    }
	/**
	* 积分分值区间
	*/
	public function codeScope(){
		$model = M('Sysconfig');
        $map['their'] = array('eq','code');
        $res = $model->where($map)->select();
		if($res){
			$this->ajaxReturn(array('code'=>2000,'msg'=>'获取成功','data'=>$res));
		}else{
			$this->ajaxReturn(array('code'=>-6002,'msg'=>'获取失败'));
		}
	}    
	/**
     * 申请代购
     */
    public function applybehalf(){
        $idenCode = I('iden');
        $buyProve = I('prove');
        $data['memberId'] = $_SESSION['memberId'];
        if(!empty($idenCode)){
            $data['idenCode'] = $idenCode;
        }
        if(!empty($buyProve)){
            $data['buyProve'] = $buyProve;
        }
        $model = M('Memberagency');
        $res = $model->add($data);
        if($res){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'您的申请已提交','id'=>$res));
        }else{ 
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'申请失败','id'=>$model->getLastSql()));
        }
    }
    
}
?>