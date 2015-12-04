<?php
/**
 * 基本控制器
 */
class BaseAction extends Action {
    

    //分页每页数据条数
    protected $pageSize = 10;
    /**
     * 初始化
     */
    function _initialize(){
        //判断管理员是否登录
        //$this->checkLogin(); 
        if(!isset($_SESSION['UID']) && $_SESSION['UID'] < 1 ){
            $this->redirect('Public/login');
        }
    }
    /**
     * 商品操作记录表
     * @parem int $id 商品id 
     * @parem int $type 1新增 2修改 3删除
     */
    public function goodsLog($id=0,$type=1,$newContent=''){
        $model = M('BeianGoods');
        $data['goods_id'] = $id;
        $data['type'] = $type;
        $data['createtime'] = time();
        if(!empty($newContent)){
            $data['new_content'] = serialize($newContent);
        }
        $res = $model->add($data);
        if(false === $res){
            Log::write('新增商品记录表操作失败'.$res);
        }
    }
    /**
     * 判断登录
     */
    protected function checkLogin(){
        if( authcheck(GROUP_NAME . '/' . MODULE_NAME.'/'.ACTION_NAME,$_SESSION['UID']) ){
            //$this->redirect('Index/index');
        }else{
            $this->redirect('Public/login');
        }
    }
    /**
     * 默认页
     */
    public function index(){
        $this->pageSize = C('PAGE_SIZE');
        $map = $this->_search();
        if(method_exists($this, '_filter')){
            $this->_filter($map);
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
            $list = $model->where($map)->order($model->getPk().' desc')->page($p.',10')->select();
            $this->assign('list',$list);
            import('ORG.Util.Page');// 导入分页类
            $count = $model->where($map)->order($model->getPk().' desc')->count();// 查询满足要求的总记录数
            $Page = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数
            $show = $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出
        }
        $this->display();
    }
    /**
     * 处理查询条件
     * @param string tbName 数据表名称
     * @return array 查询条件
     */
    protected function _search( $tbName = '' ){
        if(empty($tbName)){
            $tbName = $this->getActionName();
        }
        $model = D($tbName);
        $map = array();
        foreach ($model->getDbFields() as $key => $value) {
            if(isset($_REQUEST[$value]) && ('' != $_REQUEST[$value]) ){
                $map[$value] = $_REQUEST[$value];
            }
        }
        return $map;
    }
    /**
     * 查询分页数据
     * @param Model $model 数据对象
     * @param array $map 查询条件
     * @param string $orderBy 排序字段条件 
     * @param boolean $asc=true 默认正序
     * @request int page 页码
     * @request string order 需要排序的字段
     * @request string sort 排序方式
     * @request int size 每页显示的数据条数
     * /App/Job/index/page/2/sort/desc/order/job
     */
    // protected function _getList($model, $map, $orderBy = '', $asc = true){
    //     $currentPage  = I('page',1);
    //     //排序字段
    //     if( isset($_REQUEST['order']) ){
    //         $order = $_REQUEST['order'];
    //     }else{
    //         $order = !empty($orderBy) ? $orderBy : $model->getPk();
    //     }
    //     //排序方式
    //     if( isset($_REQUEST['sort']) ){
    //         $sort = $_REQUEST['sort'];
    //     }else{
    //         $sort = $asc ? 'asc' : 'desc';
    //     }
    //     //每页条数
    //     if( isset($_REQUEST['size']) ){
    //         $this->pageSize = $_REQUEST['size'];
    //     }
    //     //分页
    //     import("ORG.Util.Page");
    //     $count      = $model->where($map)->count();         // 查询满足要求的总记录数
    //     if($count > 0){
    //             $Page       = new Page($count,$this->pageSize);      // 实例化分页类 传入总记录数和每页显示的记录数
    //             $show       = $Page->show();                                    // 分页显示输出

    //             $list = $model->where($map)->order('`'.$order.'`  '.$sort.'')->page($currentPage.','.$this->pageSize)->select();
    //             //$list['debug'] = $model->getLastSql(); 
    //              //分页跳转的时候保证查询条件
    //              foreach($map as $key=>$val) {
    //                 $Page->parameter   .=   "$key=".urlencode($val).'&';
    //              } 
    //              //var_dump($list);
    //              $this->assign('list',$list);
    //              //$this->appReturn(2000,'数据查询成功',$list);
    //     }
        //$this->appReturn(2001,'数据库没有查询到数据');
        //return;
    // }
    /**
     * 添加页面
     */
    public function add(){
        $this->display();
    }
    /**
     * 插入表数据
     */
    public function insert(){
        $tbName = $this->getActionName();
        $model = D($tbName);
        if(false === $model->create()){
            $this->error('表单提交数据错误');
            // $this->appReturn(-6000,'表单提交数据错误');
        }
        $model->createtime = time();
        $list = $model->add();
        if(false !== $list){
            $this->redirect("$tbName/index");
            // $this->appReturn(2000,'数据插入成功');
        }else{
            $this->error('数据插入失败');
            // $this->appReturn(-6002,'数据插入失败');
        }
    }
    /**
     * 读取页面
     */
    public function show(){
        $tbName = $this->getActionName();
        $model = D($tbName);
        $where[$model->getPk()] = array('eq',$_REQUEST[$model->getPk()]);
        $list = $model->where($where)->find();
        // print_r($model->getLastSql());
        // var_dump($list);
        if($list){
            $this->assign('list',$list);
            // $this->appReturn(2000,'获取编辑数据成功');
        }else{
            $this->error('获取数据失败');
            // $this->appReturn(-6002,'获取编辑数据失败');
        }
        $this->display();
    }
    /**
     * 编辑页面
     */
    public function edit(){
        $tbName = $this->getActionName();
        $model = D($tbName);
        $where[$model->getPk()] = array('eq',$_REQUEST[$model->getPk()]);
        $list = $model->where($where)->find();
        // if(0 == count($list)){
        //     $this->appReturn(2001,'数据库没有查询到数据');
        // }

        if($list){
            $list['desc'] = unserialize($list['desc']);
            $this->assign('list',$list);
            // $this->appReturn(2000,'获取编辑数据成功');
        }else{
            $this->error('获取编辑数据失败');
            // $this->appReturn(-6002,'获取编辑数据失败');
        }
        $this->display();
    }
    /**
     * 更新表数据
     */
    public function update(){
        $tbName = $this->getActionName();
        $model = D($tbName);
        if(false === $model->create()){
            $this->error('表单提交数据错误');
            // $this->appReturn(-6000,'表单提交数据错误');
        }
        $model->updatetime = time();
        // var_dump($model);exit;
        $list = $model->save();
        if(false !== $list){
            $this->redirect("$tbName/index");
            // $this->appReturn(2000,'数据编辑成功');
        }else{
            $this->error('数据编辑失败');
            // $this->appReturn(-6002,'数据编辑失败');
        }
    }
    /**
     * 直接删除表数据
     */
    public function delete(){
        $tbName = $this->getActionName();
        $model = D($tbName);
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
    /**
     * 虚拟删除表数据
     */
    public function virtualdelete(){
        $tbName = $this->getActionName();
        $model = D($tbName);
        if(!empty($model)){
            $pk = $model->getPk();
            $id = $_REQUEST[$pk];
            if( isset($id) ){
                $data['id'] =  array('in',explode(',', $id));
                $data['status'] = -1;
                $data['updatetime'] = time();
                $list = $model->save($data);
                if(false !== $list){
                    // $this->redirect("$tbName/index");
                    $this->appReturn(2000,'数据删除成功');
                }else{
                    // $this->error('数据删除失败');
                    $this->appReturn(-6002,'数据删除失败');
                }
            }else{
                // $this->error('非法操作');
                $this->appReturn(-6001,'非法操作');
            }
        }// end if(!empty($model))
    }
    /**
     * 虚拟启用表数据
     */
    public function virtualstart(){
        $tbName = $this->getActionName();
        $model = D($tbName);
        if(!empty($model)){
            $pk = $model->getPk();
            $id = $_REQUEST[$pk];
            if( isset($id) ){
                $data['id'] =  array('in',explode(',', $id));
                $data['status'] = 1;
                $data['updatetime'] = time();
                $list = $model->save($data);
                if(false !== $list){
                    // $this->redirect("$tbName/index");
                    $this->appReturn(2000,'数据删除成功');
                }else{
                    // $this->error('数据删除失败');
                    $this->appReturn(-6002,'数据删除失败');
                }
            }else{
                // $this->error('非法操作');
                $this->appReturn(-6001,'非法操作');
            }
        }// end if(!empty($model))
    }
    /**
     * 状态改变
     */
    public function statuschange(){
        $id = I('id',0,'intval');
        $status = I('status',0,'intval');
        $tbName = $this->getActionName();
        $model = D($tbName);
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
     * 字段改变值
     */
    public function fieldchange(){
        $id = I('id',0,'intval');
        $field = I('field');
        $value = I('value');
        $tbName = $this->getActionName();
        $model = D($tbName);
        if(!empty($model)){ 
                $data['id'] =  array('eq',$id); 
                if(!empty($field)){
                    $data[$field] = $value;
                }
                $data['updatetime'] = time();
                $list = $model->save($data);
                if(false !== $list){
                    $this->appReturn(2000,'数据删除成功');
                }else{
                    $this->appReturn(-6002,'数据删除失败');
                } 
        }// end if(!empty($model))
    }
   

    protected function _empty(){
        echo '非法请求';
    }

    /**
     * ajaxReturn()
     * @param int code 错误代码
     * @param string msg 错误消息字符串
     * @param array data 返回的数据数组
     */
    protected function appReturn($code=0,$msg='',$data=array()){
        $arr = array(
            'data'=>$data,
            'msg'=>$msg,
            'code'=>$code
        );
        $this->ajaxReturn($arr);
    }

   /**
     * 图片上传 Base64 方式
   */
    public function uploadImage(){
        $data = I("data");
        $path = I("path");
        if(empty($path)){
            $path=C('UPLOAD_PATH');
        }
        if (!is_readable($path)){ 
             is_file($path) or mkdir($path,0777);  
        }
        $file=$path.time().'.png';
        $base64=base64_decode($data);
        file_put_contents($file, $base64);
        $this->ajaxReturn(array("code"=>2000,"msg"=>"图标上传成功!","data"=>'/'.$file));
    }
    public function delUploadImage(){
        $file = I("imgs");  
        if(file_exists($file)){ 
            if (unlink($file)){
                $this->ajaxReturn(array("code"=>2000,"msg"=>"图片删除成功!",'file'=>$file));
            }else{
                $this->ajaxReturn(array("code"=>-6001,"msg"=>"图片删除失败!",'file'=>$file));
            }
        }else{
            $this->ajaxReturn(array("code"=>-6001,"msg"=>"图片不存在!",'file'=>$file));
        } 
    }

    /**
     * 上传文件
     */
    public function uploadFile(){
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();
        // 设置附件上传大小
        $upload->maxSize  = 3145728 ;
        // 设置附件上传目录
        $upload->savePath = C('UPLOAD_PATH'); 
        if (!file_exists($upload->savePath)){ 
            mkdir ($upload->savePath,0777);
        }
         if(!$upload->upload()) {                           
            // 上传错误提示错误信息
            $this->appReturn(-3000,'上传错误'.$upload->getErrorMsg());
         }else{
            // 上传成功 获取上传文件信息
            $data = $upload->getUploadFileInfo();
            $this->appReturn(2000,'上传成功',$data);
        }
    }
 
    /**
     * ajax获取省份的城市
     */
    public function getProvinceToCity(){
        $pid = I('province');
        $city = getProvinceAllCity($pid);
        $this->appReturn(2000,'成功',$city);
    }
    /**
     * ajax获取城市的区县
     */
    public function getCityToArea(){
        $cid = I('city');
        $area = getCityAllArea($cid);
        $this->appReturn(2000,'成功',$area);
    }
    /**
     * ajax获取类型名称
     * 商品分类联动
     */
    public function getSubTypeName(){
        $pid = I('pid',0,'intval');
        if($pid < 1){
            $this->appReturn(-6001,'请求参数错误');
        }
        $model = M('Type');
        $map['parentId'] = array('eq',$pid);
        $res = $model->where($map)->select();
        $this->appReturn(2000,'成功',$res);
    }
    /**
     * ajax获取品牌名称
     * 商品联动
     */
    public function getBrandName(){
        $typeid = I('typeid',0,'intval');
        if($typeid < 1){
            $this->appReturn(-6001,'请求参数错误');
        }
        $model = M('Goodsbrand');
        $map['typeId'] = array('eq',$typeid);
        $res = $model->where($map)->select();
        // if(empty($res)){
        //     $res = $model->select();
        // }
        $this->appReturn(2000,'成功',$res);
    }
    /**
     * ajax获取颜色名称
     * 商品联动
     */
    public function getColorName(){
        $typeid = I('typeid',0,'intval');
        if($typeid < 1){
            $this->appReturn(-6001,'请求参数错误');
        }
        $model = M('Goodscolor');
        $map['typeId'] = array('eq',$typeid);
        $map['status'] = array('eq',1);
        $res = $model->where($map)->select();
        // if(empty($res)){
        //     $res = $model->select();
        // }
        $this->appReturn(2000,'成功',$res);
    }
    /**
     * ajax获取尺寸名称
     * 商品联动
     */
    public function getSizeName(){
        $typeid = I('typeid',0,'intval');
        if($typeid < 1){
            $this->appReturn(-6001,'请求参数错误');
        }
        $model = M('Goodssize');
        $map['typeId'] = array('eq',$typeid);
        $map['status'] = array('eq',1);
        $res = $model->where($map)->select();
        // if(empty($res)){
        //     $res = $model->select();
        // }
        $this->appReturn(2000,'成功',$res);
    }
    /**
     * ajax获取商品名称
     * 商品联动
     */
    public function getGoodsName(){
        $typeid = I('typeid',0,'intval');
        if($typeid < 1){
            $this->appReturn(-6001,'请求参数错误');
        }
        $model = M('Goods');
        $map['typeId'] = array('eq',$typeid);
        $map['status'] = array('eq',1);
        $res = $model->where($map)->select();
        // if(empty($res)){
        //     $res = $model->select();
        // }
        $this->appReturn(2000,'成功',$res);
    }
    /**
    * 商品头部
    */
    public function goodsXmlHead(){ 
        //备案XML文件
            $mGoodsHead = M('ReportGoodshead');
            $dGoodsHead = $mGoodsHead->where('id=1')->find();
            $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>'.
                        '<Goods xmlns="http://www.chinaport.gov.cn/ecss">'.
                            '<GoodsHead>'.
                                '<customsCode>'.$dGoodsHead['customsCode'].'</customsCode>'.
                                '<cbeCode>'.$dGoodsHead['cbeCode'].'</cbeCode>'.
                                '<cbeName>'.$dGoodsHead['cbeName'].'</cbeName>'.
                                '<applyCode>'.$dGoodsHead['applyCode'].'</applyCode>'.
                                '<applyName>'.$dGoodsHead['applyName'].'</applyName>'.
                                '<agentCode>'.$dGoodsHead['agentCode'].'</agentCode>'.
                                '<agentName>'.$dGoodsHead['agentName'].'</agentName>'.
                                '<accessType>'.$dGoodsHead['accessType'].'</accessType>'.
                                '<ieType>'.$dGoodsHead['ieType'].'</ieType>'.
                                '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                                '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                            '</GoodsHead>';
            return $xmlStr;

    }
    /**
    * 商品尾部
    */
    public function goodsXmlFooter(){  
       $xmlStr = '</Goods>';
       return $xmlStr;

    }
    /**
     * 生成商品备案XML文件
     * @param object $goods
     * @param int modifyMark 操作 1增 2改 3删
     */
    public function createGoodsXml($goods,$modifyMark){ 
            $mGoodsHead = M('ReportGoodshead');
            $dGoodsHead = $mGoodsHead->where('id=1')->find();
            $path = './Public/Xml/'; 
            if(!file_exists($path)){  
                mkdir($path,0777);  
            }    
            $file = $path.'/Goods'.date('Ymd').'.xml';
            
            $str = '';
            /*
            if(is_file($file)){
                $str .= file_get_contents($file);
            }*/

           $str .= '<GoodsList>'.
                    '<goodsNo>'.$goods['goodsNum'].'</goodsNo>'.
                    '<shelfGoodsName>'.$goods['name'].'</shelfGoodsName>'.
                    '<describe>'.$goods['name'].'</describe>'.
                    '<codeTs></codeTs>'.
                    '<goodsName>'.$goods['name'].'</goodsName>'.
                    '<goodsModel>'.getField2($goods['unit'],'UNIT_NAME','Unit',true,'UNIT_CODE').'|'.$goods['weight'].'</goodsModel>'.
                    '<unit>'.$goods['unit'].'</unit>'.
                    '<unit1></unit1>'.
                    '<unit2></unit2>'.
                    '<country>'.$goods['country'].'</country>'.
                    '<price>'.$goods['recordPrice'].'</price>'.
                    '<currency>'.$goods['curr'].'</currency>'.
                    '<image></image>'.
                    '<barCode></barCode>'.
                    '<taxCode>1101</taxCode>'.
                    '<ecpCode>'.$dGoodsHead['ecpCode'].'</ecpCode>'.
                    '<ecpName>'.$dGoodsHead['ecpName'].'</ecpName>'.
                    '<isTax></isTax>'.
                    '<superviseId></superviseId>'.
                    '<itemNo></itemNo>'.
                    '<limitationGoodsCode></limitationGoodsCode>'.
                    '<batchNumbers></batchNumbers>'.
                    '<brand>'.$dGoodsHead['brand'].'</brand>'.
                    '<giftFlag>'.$dGoodsHead['giftFlag'].'</giftFlag>'.
                    '<modifyMark>'.$modifyMark.'</modifyMark>'.
                    '<note></note>'.
                    '<appTime>'.date('YmdHms').'</appTime>'.
                    '<appStatus>2</appStatus>'.
                    '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                    '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                '</GoodsList>';
            
            file_put_contents($file,$str,FILE_APPEND); 
    }
    /**
    * 生成xml
    */
    public function createXml2(){
        $str = '';
        $str .= $this->goodsXmlHead();
        
        $file = './Public/Xml/Goods'.date('Ymd').'.xml';
        if(is_file($file)){
            $str .= file_get_contents($file);
        }
        $str .= $this->goodsXmlFooter();
        file_put_contents($file,$str);
        $this->redirect('Index/index');
    }
           /**
           * 生成订单备案XML文件
           * @param object $order
           * @param int modifyMark 操作 1增 2改 3删
           * 生成所有下单的订单 XML文件 http://localhost/Admin/Goods/createOrderXml
           */
           //public function createOrderXml($order,$modifyMark){
           public function createOrderXml(){
                      $modifyMark = 1;
                      //获取订单
                      $mOrder = M('Order o');
                      $mapOrder['o.status'] = array('eq',1);
                      $mapOrder['o.is_beian'] = array('eq',0);
                      $mapOrder['m.memInfo'] = array('neq','');
                      // $mapOrder['o.id'] = array('eq',755);
                      $order = $mOrder->join('m_ordermember m ON o.id = m.orderId')->where($mapOrder)->field('o.*,m.memInfo')->order('m.createtime desc')->select();
                      $order = $this->array_unset($order,"id");
                      foreach($order as $k=>$v){
                                $tt = $v['memInfo'];
                                $end = explode(':',$tt);
                                $qq = substr($end[1],1,-3);
                                $as = explode('}',$qq);
                                $order[$k]['iden'] = $as[0];
                      }
                      // dump($order); exit;
                      //获取订单收货人地址
                      $mAddress = M('Address');
                      //获取订单商品
                      $mOrderinfo = M('Orderinfo');
                      foreach ($order as $key => $value) {
                            //地址
                            $mapAddress['id'] = array('eq',$value['addressId']);
                            $addr = $mAddress->where($mapAddress)->find();
                            $order[$key]['name'] = $addr['name'];
                            $order[$key]['specificAddr'] = $addr['specificAddr'];
                            $order[$key]['addr'] = $addr['addr'];
                            $order[$key]['tel'] = $addr['tel'];
                            //商品 
                            $mapInfo['m_orderinfo.orderId'] = array('eq',$value['id']);
                            $mapInfo['m_goods.unit'] = array('neq',"");
                            $mapInfo['m_goods.curr'] = array('neq',"");
                            $goods = $mOrderinfo->where($mapInfo)->join('m_goods ON m_goods.id=m_orderinfo.goodsId')->field('m_goods.name,m_goods.goodsNum as goodsNo,m_goods.unit,m_goods.country,m_goods.recordPrice,m_goods.curr,m_goods.giftFlag,m_goods.brandId,m_goods.weight,m_goods.stock,m_orderinfo.*')->select();
 
                            $order[$key]['goodsArr'] = $goods;
                      } 

                      //备案XML文件
                      $mGoodsHead = M('ReportGoodshead');
                      $dGoodsHead = $mGoodsHead->where('id=1')->find();

                      foreach ($order as $key => $value) {
                            $xmlHead = '<?xml version="1.0" encoding="UTF-8"?>'.
                                '<Order xmlns="http://www.chinaport.gov.cn/ecss">'.
                                    '<OrderHead>'.
                                        '<cbeCode>'.$dGoodsHead['cbeCode'].'</cbeCode>'.
                                        '<cbeName>'.$dGoodsHead['cbeName'].'</cbeName>'.
                                        '<ecpCode>'.$dGoodsHead['ecpCode'].'</ecpCode>'.
                                        '<ecpName>'.$dGoodsHead['ecpName'].'</ecpName>'.
                                        '<orderNo>'.$value['orderNum'].'</orderNo> '.         
                                        '<charge>'.$value['money'].'</charge>'.
                                        '<goodsValue>'.$value['payment'].'</goodsValue>'.
                                        '<freight></freight>'.
                                        '<other></other>'.
                                        '<currency>142</currency>'.
                                        '<tax></tax>'.
                                        '<customer></customer>'.
                                        '<shipper></shipper>'.
                                        '<shipperAddress></shipperAddress>'.
                                        '<shipperTelephone></shipperTelephone>'.
                                        '<shipperCountry></shipperCountry>'.
                                        '<consignee>'.$value['name'].'</consignee>'.
                                        '<consigneeAddress>'.$value['specificAddr'].$value['addr'].'</consigneeAddress>'.
                                        '<consigneeTelephone>'.$value['tel'].'</consigneeTelephone>'.
                                        '<consigneeCountry></consigneeCountry>'.
                                        '<idType>1</idType>'.
                                        '<customerId>'.$value['iden'].'</customerId>'.
                                        '<accessType>2</accessType>'.
                                        '<ieType>I</ieType>'.
                                        '<batchNumbers></batchNumbers>'.
                                        '<totalLogisticsNo></totalLogisticsNo>'.
                                        '<tradeCountry></tradeCountry>'.
                                        '<agentCode>'.$dGoodsHead['cbeName'].'</agentCode>'.
                                        '<agentName>'.$dGoodsHead['cbeName'].'</agentName>'.
                                        '<wrapType>2</wrapType>'.
                                        '<modifyMark>'.$modifyMark.'</modifyMark>'.
                                        '<note></note>'.
                                        '<appTime>'.date('YmdHms').'</appTime>'.
                                        '<appStatus>2</appStatus>'.
                                        '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                                        '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                                    '</OrderHead>'; 

                                 $xmlBox = '';
                                 foreach ($value['goodsArr'] as $k => $v) {
                                 $xmlBox .= '<OrderList>'.
                                        '<itemNo></itemNo>'.
                                        '<goodsNo>'.$v['goodsNo'].'</goodsNo>'.
                                        '<shelfGoodsName>'.$v['name'].'</shelfGoodsName>'.
                                        '<describe>11</describe>'.
                                        '<codeTs>1</codeTs>'.
                                        '<goodsName>'.$v['name'].'</goodsName>'.
                                        '<goodsModel></goodsModel>'.
                                        '<taxCode></taxCode>'.
                                        '<price>'.$v['goodsPrice'].'</price>'.
                                        '<currency>142</currency>'.
                                        '<quantity>'.$v['goodsNum'].'</quantity>'.
                                        '<priceTotal>'.$value['payment'].'</priceTotal>'.
                                        '<unit>'.$v['unit'].'</unit>'. 
                                        '<discount></discount>'.
                                        '<giftFlag></giftFlag>'.
                                        '<country></country>'.
                                        '<purposeCode></purposeCode>'.
                                        '<wasteMaterials>1</wasteMaterials>'.
                                        '<wrapType></wrapType>'.
                                        '<packNum></packNum>'.
                                        '<barCode></barCode>'.
                                        '<brand>'.getBrandName($v['brandId']).'</brand>'.
                                        '<note></note>'.
                                    '</OrderList>'; 

                                 }//foreach         
                                 $xmlFooter='<OrderPaymentLogistics>'.
                                        '<paymentCode></paymentCode>'.
                                        '<paymentName></paymentName>'.
                                        '<paymentType></paymentType>'.
                                        '<paymentNo></paymentNo>'.
                                        '<logisticsCode></logisticsCode>'.
                                        '<logisticsName></logisticsName>'.
                                        '<logisticsNo></logisticsNo>'.
                                        '<trackNo></trackNo>'.
                                    '</OrderPaymentLogistics>'.
                                 '</Order>';

                                //$path = './Public/Xml/'.date('Ymd');
                                if(!empty($xmlBox)){
                                    $xmlStr = $xmlHead.$xmlBox.$xmlFooter;
                                    $xmlStr = str_replace("&nbsp;"," ",$xmlStr);

                                    $path = './Public/Xml/';
                                       //$path = '/usr/local/Data/send/ReceiveErp/'.date('Ymd');
                                    if(!file_exists($path)){  
                                            mkdir($path,0777);  
                                    }
                                    $file = $path.'/Order'.$key.date('YmdHms').'.xml'; 
                                    // $file = $path.'/Order'.date('Ymd').'.xml';
                                    file_put_contents($file,$xmlStr); 

                                    system("cp /www/web/anyooh_com/public_html/Public/Xml/*  /usr/local/Data/send/ReceiveErp/");

                                    $scpath = '/usr/local/Data/send/ReceiveErp/'.'Order'.$key.date('YmdHms');
                                    file_put_contents($scpath,$xmlStr);

                                    //更新备案状态
                                    // $orData['is_beian'] = 1;
                                    $orData['is_beian'] = 1;
                                    $res = M('Order')->where('id='.$value['id'])->save($orData);
                                }
                                $xmlStr = "";
                            }//foreach
                            $this->redirect('Index/index');
                    }


    public function ceshiOrder(){
        $modifyMark = 1;
        //获取订单
        $mOrder = M('Order o');
        $mapOrder['o.status'] = array('eq',1);
        $mapOrder['o.is_beian'] = array('eq',0);
        $mapOrder['m.memInfo'] = array('neq','');
        $mapOrder['o.id'] = array('eq',817);
        // $mapOrder['o.orderNum'] = array('eq','201508101136041214');
        $order = $mOrder->join('m_ordermember m ON o.id = m.orderId')->where($mapOrder)->field('o.*,m.memInfo')->order('m.createtime desc')->select();
        $order = $this->array_unset($order,"id");
        foreach($order as $k=>$v){
                $tt = $v['memInfo'];
                $end = explode(':',$tt);
                $qq = substr($end[1],1,-3);
                $as = explode('}',$qq);
                $order[$k]['iden'] = $as[0];
        }
        // dump($order);exit;
        //获取订单收货人地址
        $mAddress = M('Address');
        //获取订单商品
        $mOrderinfo = M('Orderinfo');
        foreach ($order as $key => $value) {
            //地址
            $mapAddress['id'] = array('eq',$value['addressId']);
            $addr = $mAddress->where($mapAddress)->find();
            $order[$key]['name'] = $addr['name'];
            $order[$key]['specificAddr'] = $addr['specificAddr'];
            $order[$key]['addr'] = $addr['addr'];
            $order[$key]['tel'] = $addr['tel'];
            //商品 
            $mapInfo['m_orderinfo.orderId'] = array('eq',$value['id']);
            // $mapInfo['m_orderinfo.status'] = array('eq',1);
            $mapInfo['m_goods.unit'] = array('neq',"");
            $mapInfo['m_goods.curr'] = array('neq',"");
            $goods = $mOrderinfo->where($mapInfo)->join('m_goods ON m_goods.id=m_orderinfo.goodsId')->field('m_goods.name,m_goods.goodsNum as goodsNo,m_goods.unit,m_goods.country,m_goods.recordPrice,m_goods.curr,m_goods.giftFlag,m_goods.brandId,m_goods.weight,m_goods.stock,m_goods.status goodStatus,m_orderinfo.*')->select();
            // var_dump($goods);
            $order[$key]['goodsArr'] = $goods;
        } 
        // dump($order); exit;

        foreach ($order as $key => $value) {
                            $xmlHead = '<?xml version="1.0" encoding="UTF-8"?>'.
                                '<Order xmlns="http://www.chinaport.gov.cn/ecss">'.
                                    '<OrderHead>'.
                                        '<cbeCode>'.$dGoodsHead['cbeCode'].'</cbeCode>'.
                                        '<cbeName>'.$dGoodsHead['cbeName'].'</cbeName>'.
                                        '<ecpCode>'.$dGoodsHead['ecpCode'].'</ecpCode>'.
                                        '<ecpName>'.$dGoodsHead['ecpName'].'</ecpName>'.
                                        '<orderNo>'.$value['orderNum'].'</orderNo> '.         
                                        '<charge>'.$value['money'].'</charge>'.
                                        '<goodsValue>'.$value['payment'].'</goodsValue>'.
                                        '<freight></freight>'.
                                        '<other></other>'.
                                        '<currency>142</currency>'.
                                        '<tax></tax>'.
                                        '<customer></customer>'.
                                        '<shipper></shipper>'.
                                        '<shipperAddress></shipperAddress>'.
                                        '<shipperTelephone></shipperTelephone>'.
                                        '<shipperCountry></shipperCountry>'.
                                        '<consignee>'.$value['name'].'</consignee>'.
                                        '<consigneeAddress>'.$value['specificAddr'].$value['addr'].'</consigneeAddress>'.
                                        '<consigneeTelephone>'.$value['tel'].'</consigneeTelephone>'.
                                        '<consigneeCountry></consigneeCountry>'.
                                        '<idType>1</idType>'.
                                        '<customerId>'.$value['iden'].'</customerId>'.
                                        '<accessType>2</accessType>'.
                                        '<ieType>I</ieType>'.
                                        '<batchNumbers></batchNumbers>'.
                                        '<totalLogisticsNo></totalLogisticsNo>'.
                                        '<tradeCountry></tradeCountry>'.
                                        '<agentCode>'.$dGoodsHead['cbeName'].'</agentCode>'.
                                        '<agentName>'.$dGoodsHead['cbeName'].'</agentName>'.
                                        '<wrapType>2</wrapType>'.
                                        '<modifyMark>'.$modifyMark.'</modifyMark>'.
                                        '<note></note>'.
                                        '<appTime>'.date('YmdHms').'</appTime>'.
                                        '<appStatus>2</appStatus>'.
                                        '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                                        '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                                    '</OrderHead>'; 

                                 $xmlBox = '';
                                 foreach ($value['goodsArr'] as $k => $v) {
                                 $xmlBox .= '<OrderList>'.
                                        '<itemNo></itemNo>'.
                                        '<goodsNo>'.$v['goodsNo'].'</goodsNo>'.
                                        '<shelfGoodsName>'.$v['name'].'</shelfGoodsName>'.
                                        '<describe>11</describe>'.
                                        '<codeTs>1</codeTs>'.
                                        '<goodsName>'.$v['name'].'</goodsName>'.
                                        '<goodsModel></goodsModel>'.
                                        '<taxCode></taxCode>'.
                                        '<price>'.$v['goodsPrice'].'</price>'.
                                        '<currency>142</currency>'.
                                        '<quantity>'.$v['goodsNum'].'</quantity>'.
                                        '<priceTotal>'.$value['payment'].'</priceTotal>'.
                                        '<unit>'.$v['unit'].'</unit>'. 
                                        // '<unit>112</unit>'.
                                        '<discount></discount>'.
                                        '<giftFlag></giftFlag>'.
                                        '<country></country>'.
                                        '<purposeCode></purposeCode>'.
                                        '<wasteMaterials>1</wasteMaterials>'.
                                        '<wrapType></wrapType>'.
                                        '<packNum></packNum>'.
                                        '<barCode></barCode>'.
                                        '<brand>'.getBrandName($v['brandId']).'</brand>'.
                                        '<note></note>'.
                                    '</OrderList>'; 

                                 }//foreach         
                                 $xmlFooter='<OrderPaymentLogistics>'.
                                        '<paymentCode></paymentCode>'.
                                        '<paymentName></paymentName>'.
                                        '<paymentType></paymentType>'.
                                        '<paymentNo></paymentNo>'.
                                        '<logisticsCode></logisticsCode>'.
                                        '<logisticsName></logisticsName>'.
                                        '<logisticsNo></logisticsNo>'.
                                        '<trackNo></trackNo>'.
                                    '</OrderPaymentLogistics>'.
                                 '</Order>';

                                //$path = './Public/Xml/'.date('Ymd');
                                if(!empty($xmlBox)){
                                    $xmlStr = $xmlHead.$xmlBox.$xmlFooter;
                                    $xmlStr = str_replace("&nbsp;"," ",$xmlStr);

                                    $path = './Public/Xml/';
                                       //$path = '/usr/local/Data/send/ReceiveErp/'.date('Ymd');
                                    if(!file_exists($path)){  
                                            mkdir($path,0777);  
                                    }
                                    $file = './Public/Xml_bak/myisceshi_test'.$key.date('YmdHms').'.xml'; 
                                    file_put_contents($file,$xmlStr); 

                                    //更新备案状态
                                    // $orData['is_beian'] = 1;
                                    // $orData['is_beian'] = 1;
                                    // $res = M('Order')->where('id='.$value['id'])->save($orData);
                                }
                                $xmlStr = "";
                            }//foreach

         dump($order);
    }

//////////////////////////
    /**
    * 生成xml
    */
    public function createGXml(){
        $str = '';
        $file = './Public/Xml/Goods'.date('Ymd').'.xml';
        // if(is_file($file)){
        //     $str .= file_get_contents($file);
        // }
        //
        $modifyMark = 1;
        $mGoodsHead = M('ReportGoodshead');
        $dGoodsHead = $mGoodsHead->where('id=1')->find();
        $str = '<?xml version="1.0" encoding="UTF-8"?>'.
                        '<Goods xmlns="http://www.chinaport.gov.cn/ecss">'.
                            '<GoodsHead>'.
                                '<customsCode>'.$dGoodsHead['customsCode'].'</customsCode>'.
                                '<cbeCode>'.$dGoodsHead['cbeCode'].'</cbeCode>'.
                                '<cbeName>'.$dGoodsHead['cbeName'].'</cbeName>'.
                                '<applyCode>'.$dGoodsHead['applyCode'].'</applyCode>'.
                                '<applyName>'.$dGoodsHead['applyName'].'</applyName>'.
                                '<agentCode>'.$dGoodsHead['agentCode'].'</agentCode>'.
                                '<agentName>'.$dGoodsHead['agentName'].'</agentName>'.
                                '<accessType>'.$dGoodsHead['accessType'].'</accessType>'.
                                '<ieType>'.$dGoodsHead['ieType'].'</ieType>'.
                                '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                                '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                            '</GoodsHead>';

        $model = M('BeianGoods');
        $map['m_beian_goods.status'] = array('eq',1);
        $map['m_goods.goodsNum'] = array('neq',"");
        $map['m_goods.status'] = array('eq',1);
        // $map['m_goods.id'] = array('eq',790);
        $list = $model->join('m_goods ON m_goods.id = m_beian_goods.goods_id')->join('m_taxrate ON m_taxrate.id = m_goods.taxrateId')->where($map)->distinct(true)->field('m_goods.*,m_beian_goods.type as bType,m_taxrate.snum')->select();

        $list = $this->array_unset($list,"goodsNum"); 
        $list = $this->array_unset($list,"id");   
        foreach($list as $key=>$goods){ 
             $str .= '<GoodsList>'.
                    '<goodsNo>'.$goods['goodsNum'].'</goodsNo>'.
                    '<shelfGoodsName>'.$goods['name'].'</shelfGoodsName>'.    
                    '<describe>'.$goods['name'].'</describe>'.
                    '<codeTs></codeTs>'.
                    '<goodsName>'.$goods['name'].'</goodsName>'.
                    '<goodsModel>'.getField2($goods['unit'],'UNIT_NAME','Unit',true,'UNIT_CODE').'|'.$goods['weight'].'</goodsModel>'.
                    '<unit>'.$goods['unit'].'</unit>'.
                    '<unit1></unit1>'.
                    '<unit2></unit2>'.
                    '<country>'.$goods['country'].'</country>'.
                    '<price>'.$goods['recordPrice'].'</price>'.
                    '<currency>'.$goods['curr'].'</currency>'.
                    '<image></image>'.
                    '<barCode></barCode>'.
                    '<taxCode>'.$goods['snum'].'</taxCode>'.
                    '<ecpCode>'.$dGoodsHead['ecpCode'].'</ecpCode>'.
                    '<ecpName>'.$dGoodsHead['ecpName'].'</ecpName>'.
                    '<isTax></isTax>'.
                    '<superviseId></superviseId>'.
                    '<itemNo></itemNo>'.
                    '<limitationGoodsCode></limitationGoodsCode>'.
                    '<batchNumbers></batchNumbers>'.
                    '<brand>'.$dGoodsHead['brand'].'</brand>'.
                    '<giftFlag>'.$dGoodsHead['giftFlag'].'</giftFlag>'.
                    '<modifyMark>'.$modifyMark.'</modifyMark>'.
                    '<note></note>'.
                    '<appTime>'.date('YmdHms').'</appTime>'.
                    '<appStatus>2</appStatus>'.
                    '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                    '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                '</GoodsList>';
                $data['status'] = 4;
                $res = $model->where('goods_id='.$goods['id'])->save($data);
        }
        $str .= '</Goods>';
        $str = str_replace(" ","&nbsp;",$str);
        $str = str_replace("&nbsp;"," ",$str);

        file_put_contents($file,$str);

        $path = '/usr/local/Data/send/ReceiveErp/'.date('Ymd');
        file_put_contents($path,$str);

        system("cp /www/web/anyooh_com/public_html/Public/Xml/*  /usr/local/Data/send/ReceiveErp/");
        $this->redirect('Index/index');
    } 

    //二维数组去除特定键的重复项
    public function array_unset($arr,$key){   //$arr->传入数组   $key->判断的key值
        //建立一个目标数组
        $res = array();      
        foreach ($arr as $value) {         
           //查看有没有重复项
           if(isset($res[$value[$key]])){
                 //有：销毁
                 unset($value[$key]);
           }
           else{
                $res[$value[$key]] = $value;
           }
        }
        return $res;
    }

     //此方法仅为测试用途
     public function ceshi(){
        $str = '';
        
         $mGoodsHead = M('ReportGoodshead');
        $dGoodsHead = $mGoodsHead->where('id=1')->find();
        $str = '<?xml version="1.0" encoding="UTF-8"?>'.
                        '<Goods xmlns="http://www.chinaport.gov.cn/ecss">'.
                            '<GoodsHead>'.
                                '<customsCode>'.$dGoodsHead['customsCode'].'</customsCode>'.
                                '<cbeCode>'.$dGoodsHead['cbeCode'].'</cbeCode>'.
                                '<cbeName>'.$dGoodsHead['cbeName'].'</cbeName>'.
                                '<applyCode>'.$dGoodsHead['applyCode'].'</applyCode>'.
                                '<applyName>'.$dGoodsHead['applyName'].'</applyName>'.
                                '<agentCode>'.$dGoodsHead['agentCode'].'</agentCode>'.
                                '<agentName>'.$dGoodsHead['agentName'].'</agentName>'.
                                '<accessType>'.$dGoodsHead['accessType'].'</accessType>'.
                                '<ieType>'.$dGoodsHead['ieType'].'</ieType>'.
                                '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                                '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                            '</GoodsHead>';

        $model = M('BeianGoods');
        $map['m_beian_goods.status'] = array('eq',1);
        $map['m_goods.goodsNum'] = array('neq',"");
        $map['m_goods.status'] = array('eq',1);
        $map['m_goods.id'] = array('eq',423);
        $list = $model->join('m_goods ON m_goods.id = m_beian_goods.goods_id')->where($map)->field('m_goods.*,m_beian_goods.type as bType')->select();

        $list = $this->array_unset($list,"goodsNum");
        // $qwArr = array();
        foreach($list as $key=>$goods){
             $i ++;
             $str .= '<GoodsList>'.
                    '<goodsNo>'.$goods['goodsNum'].'</goodsNo>'.
                    '<shelfGoodsName>'.$goods['name'].'</shelfGoodsName>'.
                    '<describe>'.$goods['name'].'</describe>'.
                    '<codeTs></codeTs>'.
                    '<goodsName>'.$goods['name'].'</goodsName>'.
                    '<goodsModel>'.getField2($goods['unit'],'UNIT_NAME','Unit',true,'UNIT_CODE').'|'.$goods['weight'].'</goodsModel>'.
                    '<unit>'.$goods['unit'].'</unit>'.
                    '<unit1></unit1>'.
                    '<unit2></unit2>'.
                    '<country>'.$goods['country'].'</country>'.
                    '<price>'.$goods['recordPrice'].'</price>'.
                    '<currency>'.$goods['curr'].'</currency>'.
                    '<image></image>'.
                    '<barCode></barCode>'.
                    '<taxCode>1101</taxCode>'.
                    '<ecpCode>'.$dGoodsHead['ecpCode'].'</ecpCode>'.
                    '<ecpName>'.$dGoodsHead['ecpName'].'</ecpName>'.
                    '<isTax></isTax>'.
                    '<superviseId></superviseId>'.
                    '<itemNo></itemNo>'.
                    '<limitationGoodsCode></limitationGoodsCode>'.
                    '<batchNumbers></batchNumbers>'.
                    '<brand>'.$dGoodsHead['brand'].'</brand>'.
                    '<giftFlag>'.$dGoodsHead['giftFlag'].'</giftFlag>'.
                    '<modifyMark>'.$modifyMark.'</modifyMark>'.
                    '<note></note>'.
                    '<appTime>'.date('YmdHms').'</appTime>'.
                    '<appStatus>2</appStatus>'.
                    '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                    '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                '</GoodsList>';
                 
        }
        $str .= '</Goods>';
        $str = str_replace("&nbsp;"," ",$str);
        dump(count($list));
        $file = './Public/Xml_bak/goods_ceshi2'.date('Ymd').'.xml';    
        // $str = str_replace("&nbsp;"," ",$str);            
        file_put_contents($file,$str);  
    }


  /**
    * 生成数据库中现存的商品的xml
    */
    public function createOldGoodsXml(){
        $str = '';
        $str .= $this->goodsXmlHead();
        
        $file = './Public/Xml/Goods_Old_'.date('Ymd').'.xml';
        // if(is_file($file)){
        //     $str .= file_get_contents($file);
        // }
        //
        $mGoodsHead = M('ReportGoodshead');
        $dGoodsHead = $mGoodsHead->where('id=1')->find();
        $model = M('Goods');
        $map['recordStatus'] = array('eq',1);
        $list = $model->where($map)->select();
//  
        foreach($list as $key=>$goods){
             $str .= '<GoodsList>'.
                    '<goodsNo>'.$goods['goodsNum'].'</goodsNo>'.
                    '<shelfGoodsName>'.$goods['name'].'</shelfGoodsName>'.
                    '<describe>'.$goods['name'].'</describe>'.
                    '<codeTs></codeTs>'.
                    '<goodsName>'.$goods['name'].'</goodsName>'.
                    '<goodsModel>'.getField2($goods['unit'],'UNIT_NAME','Unit',true,'UNIT_CODE').'|'.$goods['weight'].'</goodsModel>'.
                    '<unit>'.$goods['unit'].'</unit>'.
                    '<unit1></unit1>'.
                    '<unit2></unit2>'.
                    '<country>'.$goods['country'].'</country>'.
                    '<price>'.$goods['recordPrice'].'</price>'.
                    '<currency>'.$goods['curr'].'</currency>'.
                    '<image></image>'.
                    '<barCode></barCode>'.
                    '<taxCode>1101</taxCode>'.
                    '<ecpCode>'.$dGoodsHead['ecpCode'].'</ecpCode>'.
                    '<ecpName>'.$dGoodsHead['ecpName'].'</ecpName>'.
                    '<isTax></isTax>'.
                    '<superviseId></superviseId>'.
                    '<itemNo></itemNo>'.
                    '<limitationGoodsCode></limitationGoodsCode>'.
                    '<batchNumbers></batchNumbers>'.
                    '<brand>'.$dGoodsHead['brand'].'</brand>'.
                    '<giftFlag>'.$dGoodsHead['giftFlag'].'</giftFlag>'.
                    '<modifyMark>'.$modifyMark.'</modifyMark>'.
                    '<note></note>'.
                    '<appTime>'.date('YmdHms').'</appTime>'.
                    '<appStatus>2</appStatus>'.
                    '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                    '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                '</GoodsList>';
        }
        //
        $str .= $this->goodsXmlFooter(); 
        file_put_contents($file,$str);
        $this->redirect('Index/index');
    } 
     
    /**
     * 韵达接口
     */
    public function yunda($data=array()){ 
        $mGoodsHead = M('ReportGoodshead');
        $dGoodsHead = $mGoodsHead->where('id=1')->find();
        $baseData = array(
            'appkey'=>$dGoodsHead['appkey'],
            'ecpName'=>$dGoodsHead['ecpName'],
        );
        $str = '';
        if(count($data)>0){
            $data = array_merge($baseData,$data); 
            foreach ($data as $key => $value) {
                // if('' != $value){
                    $str .= $key.'='.$value.'&';
                // }
            }
            $str = rtrim($str,'&');
        }
        // echo $str;
        $obj = $this->getCurl($str);
        return $obj;
        // $this->ajaxReturn(array('msg'=>$obj));
        // echo $obj;
    }
 /**
 * curl_get请求
 */
 public function getCurl($getData=''){  
    $url = 'http://121.42.31.134:8888/MyNosql/api!logisbyjson.action'; 
    if(!empty($getData)){
        $url = $url.'?'.$getData;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0); 
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
    $output = curl_exec($ch);
    if($output===false){  
        Log::write('getCurl_ERROR:'.curl_error($ch));
        Log::write('getCurl_ERROR:'.var_export(curl_getinfo($ch),true)); 
    } 
    curl_close($ch);
    return $output;
 }

 public function test(){
    $data = array(
        'logisticsNo'=>'', //运单
        'orderNo'=>'',
        'consignee'=>'', //收货人
        'consigneeAddress'=>'',
        'consigneeTelephone'=>'',
        'idType'=>'1',
        'customerId'=>'',
        'weight'=>'',
        'quantity'=>'',
        'ieType'=>'I',
        'stockFlag'=>'2',
        'batchNumbers'=>'', //批次号
        'modifyMark'=>'1',
        'appTime'=>time(),
    );
    dump($this->yunda($data));
 }

    /**
    * 发送xml请求post
    */
    public function sendXml($xml_data){
            // $xml_data = "<xml>...</xml>";
            // $url = "http://121.42.31.134:8888/MyNosql/api!logisbyjson.action";
            $url = "http://121.42.30.13/Base/jsOrderInfo";
            $header[] = "Content-type: text/xml";//定义content-type为xml
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
            $response = curl_exec($ch);
            if(curl_errno($ch))
            {
                print curl_error($ch);
            }
            curl_close($ch);
            $this->ajaxReturn(array('msg'=>'success','xml'=>$response));
    }
    /**
    *
    * iesroad交易订单推送接口
    **/
    public function iesJyOrder(){
            $old = $_SESSION['orderId'];
            $map['o.id'] = array('eq',$old);
            $order = M('Order o')->join('m_address a ON o.addressId=a.id')->where($map)->field('o.*,a.name,a.specificAddr,a.addr,a.tel')->find();
            $map = array();
            $map['o.orderId'] = array('eq',$old);
            $orderInfo = M('Orderinfo o')->join('m_goods g ON o.goodsId=g.id')->where($map)->field('g.*,o.goodsNum buyNum,o.sumTotal,o.goodsPrice')->select();

            $mac = md5("merNo=10001&merPass=001aee4981494c6bf0bae25095bc6969&orderNo=".$order['orderNum']);
            $sumMoney = $order['payment'] + $order['freight'] + $order['taxrate'];  //邮费0
            $head = '<?xml version="1.0" encoding="UTF-8"?>'.
                            '<orderdata>'.
                                '<merNo>10001</merNo>'.
                                '<mac>'.$mac.'</mac>'.
                                '<orderNo>'.$order["orderNum"].'</orderNo>'.
                                '<orderTime>'.$_SESSION["jyTime"].'</orderTime>'.
                                '<orderTaxfee>'.$order["taxrate"].'</orderTaxfee>'.
                                '<orderZipfee>0</orderZipfee>'.
                                '<orderGoodMoney>'.$sumMoney.'</orderGoodMoney>'.
                                '<orderTotalMoney>'.$sumMoney.'</orderTotalMoney>'.
                                '<consignee>'.$order["name"].'</consignee>'.
                                '<consigneeId>111</consigneeId>'.
                                '<address>'.$order["specificAddr"].$order["addr"].'</address>'.
                                '<mobile>'.$order["tel"].'</mobile>'.
                                '<orderDetail>';
            $footer = '</orderDetail>'.
                    '</orderdata>';
            //商品总金额（商品单价x数量 + 单个商品税费x数量）
            foreach($orderInfo as $k=>$v){
                    $body .= '<detail>'.
                                        '<goodNo>'.$v["goodsNum"].'</goodNo>'.
                                        '<goodName>'.$v["name"].'</goodName>'.
                                        '<goodPrice>'.$v["goodsPrice"].'</goodPrice>'.
                                        '<goodTaxfee>0</goodTaxfee>'.
                                        '<goodCount>'.$v["buyNum"].'</goodCount>'.
                                        '<goodMoney>'.$v["sumTotal"].'</goodMoney>'.
                                    '</detail>';
            }
            $str = $head.$body.$footer;
            // sendXml($str);
            // file_put_contents($file,$str);
            // $this->redirect('Index/index');
            // $this->sendXml($str);
            return $str;
            // $this->ajaxReturn(array('detail'=>$str));
            // $this->ajaxReturn(array('code'=>0,'msg'=>'success','old'=>$old,'head'=>$head,'data'=>$orderInfo,'detail'=>$str));

    }
    //此方法仅为更新库里的unit为统一的3位数
    public function updateTest(){
            $map['id'] = array('between',array(28,99));
            $goods = M('Goods')->where($map)->select();
            foreach($goods as $k=>$v){
                        var_dump($v['unit']);
                        $unit = $v['unit'];
                        if(ceil($unit)<10){
                            $newUnit = '00'.ceil($unit);
                        }else if(ceil($unit)>=10 && ceil($unit)<100){
                            $newUnit = '0'.ceil($unit);
                        }
                        var_dump($newUnit);
                        $data['unit'] = $newUnit;
                        var_dump($data['unit']);
                        $res = M('Goods')->where('id = '.$v['id'])->save($data);
            }
    }

}
?>
