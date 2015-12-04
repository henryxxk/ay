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
       if (isset($_SESSION['user'])){
            $this->assign("isLogin",1);
        }
        else{
            $this->assign("isLogin",0);
        }
        
        $this->assign("isLogin","1");
        
        $this->assign("isLoginPage",0);
        $mSysConfig = M("sysconfig");
        $dSysConfig = $mSysConfig->where("id=3")->select();
        
        $model = D('Type');
        $map['status'] = array('eq',1);
        $map['parentId'] = array('eq',0);
        $type = $model->where($map)->order('scort asc')->select();
        
        foreach($type as $q=>$w){
            $type[$q]['typeArr'] =array();
            $map1['status'] = array('eq',1);
            $map1['parentId'] = array('in',$type[$q]['id']);
            $typeAll = $model->where($map1)->order('scort asc')->select();
            $type[$q]['typeArr'] = $typeAll;
        }

        $this->assign("hotword",explode(",", $dSysConfig[0]["content"]));
        // dump($type);
        $this->assign("type",$type);
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
        $data['from'] = 2;
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
            $list = $model->order($model->getPk().' desc')->select();
            // var_dump($list);
            $this->assign('list',$list);
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
    protected function _getList($model, $map, $orderBy = '', $asc = true){
        $currentPage  = I('page',1);
        //排序字段
        if( isset($_REQUEST['order']) ){
            $order = $_REQUEST['order'];
        }else{
            $order = !empty($orderBy) ? $orderBy : $model->getPk();
        }
        //排序方式
        if( isset($_REQUEST['sort']) ){
            $sort = $_REQUEST['sort'];
        }else{
            $sort = $asc ? 'asc' : 'desc';
        }
        //每页条数
        if( isset($_REQUEST['size']) ){
            $this->pageSize = $_REQUEST['size'];
        }
        //分页
        import("ORG.Util.Page");
        $count      = $model->where($map)->count();         // 查询满足要求的总记录数
        if($count > 0){
                $Page       = new Page($count,$this->pageSize);      // 实例化分页类 传入总记录数和每页显示的记录数
                $show       = $Page->show();                                    // 分页显示输出

                $list = $model->where($map)->order('`'.$order.'`  '.$sort.'')->page($currentPage.','.$this->pageSize)->select();
                //$list['debug'] = $model->getLastSql(); 
                 //分页跳转的时候保证查询条件
                 foreach($map as $key=>$val) {
                    $Page->parameter   .=   "$key=".urlencode($val).'&';
                 } 
                 //var_dump($list);
                 $this->assign('list',$list);
                 //$this->appReturn(2000,'数据查询成功',$list);
        }
        $this->appReturn(2001,'数据库没有查询到数据');
        return;
    }
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
            $this->redirect('/'.$tbName.'/index');
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
        $list = $model->save();
        if(false !== $list){
            $this->redirect('/'.$tbName.'/index');
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
                    $this->redirect('/'.$tbName.'/index');
                    // $this->appReturn(2000,'数据删除成功');
                }else{
                    $this->error('数据删除失败');
                    // $this->appReturn(-6002,'数据删除失败');
                }
            }else{
                $this->error('非法操作');
                // $this->appReturn(-6001,'非法操作');
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
                    $this->redirect('/'.$tbName.'/index');
                    // $this->appReturn(2000,'数据删除成功');
                }else{
                    $this->error('数据删除失败');
                    // $this->appReturn(-6002,'数据删除失败');
                }
            }else{
                $this->error('非法操作');
                // $this->appReturn(-6001,'非法操作');
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
            $path=C('UPLOAD_IMG_PATH');
        }
        if (!file_exists($path)){ 
            mkdir ($path,0777);
        }
        $file=$path.time().'.png';
        $base64=base64_decode($_POST['data']);
        file_put_contents($file, $base64);
        $this->ajaxReturn(array("code"=>2000,"msg"=>"图片上传成功!","data"=>substr($file, 1, strlen($file)-1)));
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
        $upload->savePath = C('UPLOAD_FILE_PATH');
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
     * 获取省份联动
     */
    public function getProvinceCityName(){
        $id = I('id');
        $arr = getProvinceAllCity($id);
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取成功','data'=>$arr));
    }
    /**
     * 获取市联动
     */
    public function getCityAreaName(){
        $id = I('id');
        $arr = getCityAllArea($id);
        // $this->appReturn(2000,'获取成功',$arr);
        $this->ajaxReturn(array('code'=>2000,'msg'=>'获取成功','data'=>$arr));
    }
     /**
     * 生成订单备案XML文件
     * @param object $order
     * @param int modifyMark 操作 1增 2改 3删
     */
    public function createOrderXml($order,$modifyMark){
        //备案XML文件
            $mGoodsHead = M('ReportGoodshead');
            $dGoodsHead = $mGoodsHead->where('id=1')->find();
            // var_dump($dGoodsHead);
            $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>'.
                '<Order xmlns="http://www.chinaport.gov.cn/ecss">'.
                    '<OrderHead>'.
                        '<cbeCode>'.$dGoodsHead['cbeCode'].'</cbeCode>'.
                        '<cbeName>'.$dGoodsHead['cbeName'].'</cbeName>'.
                        '<ecpCode>'.$dGoodsHead['cbeName'].'</ecpCode>'.
                        '<ecpName>'.$dGoodsHead['cbeName'].'</ecpName>'.
                        '<orderNo>'.$order['orderNum'].'</orderNo> '.         
                        '<charge>'.$order['money'].'</charge>'.
                        '<goodsValue>'.$order['payment'].'</goodsValue>'.
                        '<freight></freight>'.
                        '<other></other>'.
                        '<currency>142</currency>'.
                        '<tax></tax>'.
                        '<customer></customer>'.
                        '<shipper></shipper>'.
                        '<shipperAddress></shipperAddress>'.
                        '<shipperTelephone></shipperTelephone>'.
                        '<shipperCountry></shipperCountry>'.
                        '<consignee>'.$order['name'].'</consignee>'.
                        '<consigneeAddress>'.$order['specificAddr'].$order['addr'].'</consigneeAddress>'.
                        '<consigneeTelephone>'.$order['tel'].'</consigneeTelephone>'.
                        '<consigneeCountry></consigneeCountry>'.
                        '<idType>1</idType>'.
                        '<customerId></customerId>'.
                        '<accessType>2</accessType>'.
                        '<ieType>I</ieType>'.
                        '<batchNumbers></batchNumbers>'.
                        '<totalLogisticsNo></totalLogisticsNo>'.
                        '<tradeCountry></tradeCountry>'.
                        '<agentCode></agentCode>'.
                        '<agentName></agentName>'.
                        '<wrapType></wrapType>'.
                        '<modifyMark>'.$modifyMark.'</modifyMark>'.
                        '<note></note>'.
                        '<appTime>'.date('YmdHms').'</appTime>'.
                        '<appStatus>2</appStatus>'.
                        '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                        '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                    '</OrderHead>'.
                    '<OrderList>'.
                        '<itemNo></itemNo>'.
                        '<goodsNo>'.$order['orderNum'].'</goodsNo>'.
                        '<shelfGoodsName>'.$order['goodsName'].'</shelfGoodsName>'.
                        '<describe></describe>'.
                        '<codeTs></codeTs>'.
                        '<goodsName></goodsName>'.
                        '<goodsModel>盒装|20g</goodsModel>'.
                        '<taxCode></taxCode>'.
                        '<price></price>'.
                        '<currency>142</currency>'.
                        '<quantity>'.$order['goodsNum'].'</quantity>'.
                        '<priceTotal>'.$order['money'].'</priceTotal>'.
                        '<unit>'.$order['unit'].'</unit>'.
                        '<discount></discount>'.
                        '<giftFlag></giftFlag>'.
                        '<country></country>'.
                        '<purposeCode></purposeCode>'.
                        '<wasteMaterials>1</wasteMaterials>'.
                        '<wrapType></wrapType>'.
                        '<packNum></packNum>'.
                        '<barCode></barCode>'.
                        '<brand>'.$order['goodsBrand'].'</brand>'.
                        '<note></note>'.
                    '</OrderList>'.
                    '<OrderPaymentLogistics>'.
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
 
            $path = './Public/Xml/'.date('Ymd');
            if(!file_exists($path)){  
                mkdir($path);  
            }    
            $file = $path.'/Order'.$goods['orderNum'].'.xml'; 
            file_put_contents($file,$xmlStr);
    }

    /**
     * ajax 获取子分类
     */
    public function getType2(){
        $model = M('Type');
        $map['parentId'] = array('eq',I('id'));
        $list = $model->where($map)->select();
        if($list){
            $this->ajaxReturn(array('code'=>2000,'msg'=>'获取成功','data'=>$list));
        }else{ 
            $this->ajaxReturn(array('code'=>-6002,'msg'=>'获取失败'));
        }
    }


    /**
 * $str 原始中文字符串
 * $encoding 原始字符串的编码，默认GBK
 * $prefix 编码后的前缀，默认"&#"
 * $postfix 编码后的后缀，默认";"
 */
 //将内容进行UNICODE编码
public function unicode_encode($name)
{
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2)
    {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0)
        {    // 两个字节的文字
            $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
        }
        else
        {
            $str .= $c2;
        }
    }
    return $str;
}
 
/**
 * $str Unicode编码后的字符串
 * $decoding 原始字符串的编码，默认GBK
 * $prefix 编码字符串的前缀，默认"&#"
 * $postfix 编码字符串的后缀，默认";"
 */
// 将UNICODE编码后的内容进行解码
public function unicode_decode($name)
{
    // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches))
    {
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j++)
        {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0)
            {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code).chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name .= $c;
            }
            else
            {
                $name .= $str;
            }
        }
    }
    return $name;
}


    /*
    *iesroad商品信息推送接口
    *
    */
    public function iesGoodInfo(){
            $map['g.status'] = array('eq',1);
            $map['g.goodsNum'] = array('neq',"");
            $goods = M('Goods g')->join('m_goodsbrand b ON b.id=g.brandId')->join('m_type t ON t.id=g.typeId')->where($map)->field('g.*,b.name brandName,t.name typeName,t.parentId')->select();

            foreach($goods as $k=>$v){
                    if($v["parentId"] !== 0){
                            $type = M('Type')->where('id='.$v['parentId'])->find();
                            $typeName = $type["name"];
                    }else{
                            $typeName = $v["typeName"];
                    }
                    $imgArr = explode(',',$v["imgs"]);
                    $good .= '<good>'.
                                    '<merGoodCode>'.$v["goodsNum"].'</merGoodCode>'.
                                    '<goodName>'.$v["name"].'</goodName>'.
                                    '<marketPrice>'.$v["marketPrice"].'</marketPrice>'.
                                    '<merPrice>'.$v["realPrice"].'</merPrice>'.
                                    '<shopPrice>'.$v["vipPrice"].'</shopPrice>'.
                                    '<goodNum>'.$v["stock"].'</goodNum>'.
                                    '<goodFee>'.$v["taxrate"].'</goodFee>'.
                                    '<goodCountry>'.$v["place"].'</goodCountry>'.
                                    '<goodBrand>'.$v["brandName"].'</goodBrand>'.
                                    '<goodCat>'.$typeName.'</goodCat>'.
                                    '<goodImg>http://anyooh.com/'.$imgArr[0].'</goodImg>';
                                    $thumb = "";
                                    foreach($imgArr as $q=>$w){

                                            if($q!=0&&$q!=(count($imgArr)-1)){
                                                    $thumb .= $w.'|';
                                            }else if($q!=0&&$q==(count($imgArr)-1)){
                                                    $thumb .= $w;
                                            }
                                    }
                      $good .= '<goodThumb>'.$thumb.'</goodThumb>'.
                                    '<goodDesc>'.$v["desc"].'</goodDesc>'.
                                '</good>';

                        $strmac[] = 'merGoodCode='.$v["goodsNum"].'&goodName='.mb_convert_encoding($v["name"],"UTF-8","auto").'&marketPrice='.$v["marketPrice"].
                                    '&merPrice='.$v["realPrice"].'&shopPrice='.$v["vipPrice"].'&goodNum='.$v["stock"].'&goodFee='.$v["taxrate"];
            }
            foreach($strmac as $k=>$v){
                    if($k!=(count($strmac)-1)){
                        $xmac .= $v."&";
                    }else{
                        $xmac .= $v;
                    }
            }
            $shmac = "10001".$xmac."001aee4981494c6bf0bae25095bc6969";
            $mac = md5($shmac);
            // dump($mac);  
            $head = '<?xml version="1.0" encoding="UTF-8"?>'.
                            '<request>'.
                                '<merNo>10001</merNo>'.
                                '<mac>'.$mac.'</mac>'.
                                '<goodData>';
            $footer = '</goodData>'.
                    '</request>';
            $str = $head.$good.$footer;

            // $xml = file_get_contents('php://input');

            // file_put_contents($file,$str);
            // $this->redirect('Index/index');
            $this->ajaxReturn(array('str'=>$str));

    }


      /**
     * ajax获取品牌名称
     * 商品联动
     */
    public function getBrandName($goodNum){
        /*if($goodNum < 1){
            $this->appReturn(-6001,'请求参数错误');
        }*/
        $model = M('Goods g');
        $map['g.goodsNum'] = array('eq',$goodNum);
        $res = $model->join('m_goodsbrand b ON g.brandId=b.id')->field('b.name brandName')->where($map)->find();
        // if(empty($res)){
        //     $res = $model->select();
        // }
        // dump($res['brandName']);
        return $res['brandName'];
    }

    /**
     * 要接收别人xml数据请求的接口
     */
    public function jsOrderInfo(){
            $xml = file_get_contents('php://input');
            // echo iconv("UTF-8","GBK//IGNORE",$xml);

            $path = './Public/Xml/Order_Ieso_sd'.date('YmdHms').'.xml';
            file_put_contents($path,$xml);

             // $data = simplexml_load_string($xml);
             $obj=simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
             // dump($obj);

            if(is_object($obj)){
                $obj=get_object_vars($obj);
            }

            //保存数据到数据库
             $model = D('Address');
             $dataAddr['mId'] = 0;
             $dataAddr['name'] = $obj['consignee'];
             $dataAddr['specificAddr'] = $obj['address'];
             $dataAddr['tel'] = $obj['mobile'];
             $dataAddr['createtime'] = time();
             $addressId = $model->add($dataAddr);

             $model = D('Order');
             $data['orderNum'] = $obj['orderNo'];
             $data['memberId'] = 0; //不是平台注册用户
             $data['money'] = $obj['orderGoodMoney'];
             $data['payment'] = $obj['orderTotalMoney'];
             $data['taxrate'] = $obj['orderTaxfee'];
             $data['freight'] = $obj['orderZipfee'];
             $data['status'] = 1;  //已支付
             $data['addressId'] = $addressId;
             $data['createtime'] = time(0);
             $data['payTime'] = strtotime($obj['orderTime']);
             $data['remark'] = '洋货码头订单';
             $orderId = $model->add($data);
             // $this->ajaxReturn(array('code'=>0,'msg'=>$res));
             
             $i = 0;
             $orderArr = array();  //订单信息数组
             foreach($obj["orderDetail"]->children() as $k=>$v){
                    foreach($v as $q=>$w){
                          if($q == 'goodNo'){
                                $model = D('Goods');
                                $map['goodsNum'] = array('like','%'.$w.'%');
                                $goodInfo = $model->where($map)->find();
                                $orderArr[$i]['goodId'] = $goodInfo['id'];
                                $orderArr[$i]['goodsNum'] = $goodInfo['goodsNum'];
                                $orderArr[$i]['name'] = $goodInfo['name'];
                                $orderArr[$i]['unit'] = $goodInfo['unit'];
                          }
                          if($q == 'goodPrice'){
                                $orderArr[$i]['price'] = (float)$w;
                          }
                          if($q == 'goodTaxfee'){
                                $orderArr[$i]['tax'] = (float)$w;
                          }
                          if($q == 'goodCount'){
                                $orderArr[$i]['count'] = (int)$w;
                          }
                          if($q == 'goodMoney'){
                                $orderArr[$i]['money'] =  (float)$w;
                          }
                    }
                    $i ++;
             }
             for($k = 0; $k < count($orderArr); $k++){
                      $model = D('Orderinfo');
                      $dataInfo['goodsId'] = $orderArr[$k]['goodId'];
                      $dataInfo['goodsNum']= $orderArr[$k]['count'];
                      $dataInfo['goodsPrice']= $orderArr[$k]['price'];
                      $dataInfo['sumTotal']= $orderArr[$k]['money'];
                      $dataInfo['goodsTax']= $orderArr[$k]['tax'];
                      $dataInfo['orderId'] = $orderId;
                      $dataInfo['memberId'] = 0;
                      $dataInfo['createtime'] = time();
                      $res = $model->add($dataInfo);
             }

             // $obj = iconv("UTF-8","GBK//IGNORE",$obj);
             // dump($obj);
             //备案XML文件
             $mGoodsHead = M('ReportGoodshead');
             $dGoodsHead = $mGoodsHead->where('id=1')->find();
             $modifyMark = 1;
             // dump($obj['merNo']);
             $xmlHead = '<?xml version="1.0" encoding="UTF-8"?>'.
                '<Order xmlns="http://www.chinaport.gov.cn/ecss">'.
                    '<OrderHead>'.
                        '<cbeCode>'.$dGoodsHead['cbeCode'].'</cbeCode>'.
                        '<cbeName>'.$dGoodsHead['cbeName'].'</cbeName>'.
                        '<ecpCode>'.$dGoodsHead['ecpCode'].'</ecpCode>'.
                        '<ecpName>'.$dGoodsHead['ecpName'].'</ecpName>'.
                        '<orderNo>'.$obj['orderNo'].'</orderNo> '.         
                        '<charge>'.$obj['orderTotalMoney'].'</charge>'.
                        '<goodsValue>'.$obj['orderGoodMoney'].'</goodsValue>'.
                        '<freight></freight>'.
                        '<other></other>'.
                        '<currency>142</currency>'.
                        '<tax></tax>'.
                        '<customer></customer>'.
                        '<shipper></shipper>'.
                        '<shipperAddress></shipperAddress>'.
                        '<shipperTelephone></shipperTelephone>'.
                        '<shipperCountry></shipperCountry>'.
                        '<consignee>'.$obj['consignee'].'</consignee>'.
                        '<consigneeAddress>'.$obj['address'].'</consigneeAddress>'.
                        '<consigneeTelephone>'.$obj['mobile'].'</consigneeTelephone>'.
                        '<consigneeCountry></consigneeCountry>'.
                        '<idType>1</idType>'.
                        '<customerId>'.$obj['consigneeId'].'</customerId>'.
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
                        '<appTime>'.$obj['orderTime'].'</appTime>'.
                        '<appStatus>2</appStatus>'.
                        '<appUid>'.$dGoodsHead['appUid'].'</appUid>'.
                        '<appUname>'.$dGoodsHead['appUname'].'</appUname>'.
                    '</OrderHead>'; 
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
            for($k = 0; $k < count($orderArr); $k++){
                    $xmlBox .= '<OrderList>'.
                                         '<itemNo></itemNo>';
                                         $xmlBox .= '<goodsNo>'.$orderArr[$k]['goodsNum'].'</goodsNo>';
                                         $xmlBox .= '<shelfGoodsName>'.$orderArr[$k]['name'].'</shelfGoodsName>';
                                         $xmlBox .= '<describe>11</describe>'.
                                                             '<codeTs>1</codeTs>';
                                         $xmlBox .= '<goodsName>'.$orderArr[$k]['name'].'</goodsName>';
                    $xmlBox .= '<goodsModel></goodsModel>'.
                                        '<taxCode></taxCode>';
                                        $xmlBox .= '<price>'.$orderArr[$k]['price'].'</price>'.
                                                        '<currency>142</currency>';
                                        $xmlBox .=  '<quantity>'.$orderArr[$k]['count'].'</quantity>';
                                        $xmlBox .=  '<priceTotal>'.$orderArr[$k]['money'].'</priceTotal>';
                    $xmlBox .= '<unit>'.$orderArr[$k]['unit'].'</unit>'.
                                        '<discount></discount>'.
                                        '<giftFlag></giftFlag>'.
                                        '<country></country>'.
                                        '<purposeCode></purposeCode>'.
                                        '<wasteMaterials>1</wasteMaterials>'.
                                        '<wrapType></wrapType>'.
                                        '<packNum></packNum>'.
                                        '<barCode></barCode>';
                                        $brandName = $this->getBrandName($orderArr[$k]['goodsNum']);
                                        if(empty($brandName)){
                                                $w="无";
                                        }else{
                                                $w=$brandName;
                                        }
                                        $xmlBox .=  '<brand>'.$w.'</brand>';
                    $xmlBox .=  '<note></note>'.
                                    '</OrderList>'; 
                    if(!empty($xmlBox)){
                            $xmlStr = $xmlHead.$xmlBox.$xmlFooter;
                            // dump($xmlStr);
                            // exit;
                            $path = './Public/Xml/';
                            // $path = '/usr/local/Data/send/ReceiveErp/'.date('Ymd');
                            if(!file_exists($path)){  
                                    mkdir($path,0777);  
                            }
                            $file = $path.'/Order'.date('YmdHms').'.xml'; 
                            // $file = $path.'/Order'.date('Ymd').'.xml';
                            file_put_contents($file,$xmlStr); 

                            $sendPath = '/usr/local/Data/send/ReceiveErp/'.date('Ymd');
                            file_put_contents($sendPath,$xmlStr);

                            // system("cp /www/web/anyooh_com/public_html/Public/Xml/*  /usr/local/Data/send/ReceiveErp/");  //是否上传到海关
                  }
                  $xmlBox = '';
            }
            //接口返回的数据
            $resultxml = '<?xml version="1.0" encoding="UTF-8"?>
                                    <reponse>
                                        <retCode>00</retCode>
                                        <retMsg>成功</retMsg>
                                        <wayNo>E001002003004</wayNo>
                                    </reponse>';
            $resultxml =iconv("UTF-8","GBK//IGNORE",$resultxml);  //EUC-CN
            $resultxml = iconv('GBK', 'UTF-8', $resultxml);
            echo $resultxml;
    }

    /**
     * *
     * 模拟别人发送数据的接口
     *
     */
    public function testiesoOrder(){
        // dump($this->getBrandName("BR-02-008-230"));
            $testXml = '<?xml version="1.0" encoding="UTF-8"?><orderdata><merNo>10007</merNo><mac>1c346f853c2553e8ed576f1149c457b9</mac><orderNo>2015102332141001</orderNo><orderTime>20151023152943</orderTime><orderTaxfee>0.00</orderTaxfee><orderZipfee>0.00</orderZipfee><orderGoodMoney>21.00</orderGoodMoney><orderTotalMoney>21.00</orderTotalMoney><consignee>啊雷</consignee><consigneeId>610524199309250036</consigneeId><address>陕西省渭南合阳雷家洼村</address><mobile>18161736153</mobile><orderDetail><detail><goodNo>AA-04-081-407</goodNo><goodName>元祖六条大麦茶 袋泡茶 烘焙 碱性 健康无糖助消化</goodName><goodPrice>21.00</goodPrice><goodTaxfee>2.10</goodTaxfee><goodCount>1</goodCount><goodMoney>23.10</goodMoney></detail></orderDetail></orderdata>';
            /*$testXml = '<?xml version="1.0" encoding="UTF-8"?>
            <orderdata><merNo>10007</merNo>
            <mac>f24b8695adc102d961b4a391e7e3b23e</mac>
            <orderNo>2015081022330088</orderNo>
            <orderTime>20151023152943</orderTime>
            <orderTaxfee>10.00</orderTaxfee><orderZipfee>10.00</orderZipfee>
            <orderGoodMoney>1.00</orderGoodMoney><orderTotalMoney>2.00</orderTotalMoney>
            <consignee>肖军</consignee><consigneeId>430723198410272412</consigneeId>
            <address>陕西省西安市高新三路</address><mobile>12312312312</mobile><orderDetail><detail>
            <goodNo>AA-04-081-407</goodNo><goodName>果味麦片 10个月+ 250G</goodName>
            <goodPrice>1.00</goodPrice><goodTaxfee>11.80</goodTaxfee><goodCount>1</goodCount>
            <goodMoney>12.80</goodMoney></detail><detail>
            <goodNo>BR-02-008-230</goodNo><goodName>果味麦片 10个月+ 250G</goodName>
            <goodPrice>1.00</goodPrice><goodTaxfee>11.80</goodTaxfee><goodCount>1</goodCount>
            <goodMoney>12.80</goodMoney></detail></orderDetail></orderdata>';*/

            $setting = array(
                         'http'=>array( 
                                'method'=>"POST", 
                                'timeout'=>60,
                                'header'=>"Content-type:text/xml;charset=UTF-8" ,
                                // 'Coentent-Type'=>"application/x-www-data-urlencoded",
                                'content'=> $testXml
                                ) 
                            ); 
            $context = stream_context_create($setting);
            $xml = file_get_contents('http://anyooh.com/Base/jsOrderInfo',null,$context);
            $data = (array)simplexml_load_string($xml);
            $xml = iconv("UTF-8","GBK//IGNORE",$xml);
            dump($xml);
    }
    /**
    * 查询失败的订单（更新其付款状态）
    */
    public function getjyInfo(){
            $ZhiFu = new ZhiFuBehavior();
            $map['payTime'] = array('neq','0');
            $map['status'] = array('eq',0);
            $orders = M('Order')->where($map)->select();
            foreach($orders as $k=>$v){
                    $parameter = array();
                    $parameter = array(
                        'queryUrl' => $ZhiFu->{"JSPT_QUERY_URL"},
                        'version' => '1.0.0',
                        'charset' => $ZhiFu->{"CHARSET"},
                        "signMethod" =>$ZhiFu->{"SIGNMETHOD"},                     //签名方法  
                        'payType' => 'B2C',
                        'transType' => '01',
                        'merId' => $ZhiFu->{"MERID"},
                        'orderNumber' => $v['orderNum'],
                        'orderTime' => '',
                        'qid' => '',
                        'signkey' => $ZhiFu->{"SIGNKEY"}
                    );
                    $sign = $this->getSign($parameter);
                    $parameter['sign'] = $sign;
                    $result = $ZhiFu->sendHttpRequest($parameter , $parameter['queryUrl']);
                    $xml = (array)simplexml_load_string($result);
                    if($xml['respCode'] == "00"){
                            dump('success');
                            $data = array();
                            $data['updatetime'] = time();
                            $data['status'] = 1;
                            $res = M('Order')->where('id='.$v['id'])->save($data);
                    }else if($xml['respCode'] == "03"){
                            dump('no record');
                    }
            }
    }

    //获得加密签名信息
    public function getSign($parameter) {
        $sign_src = "version=".$parameter['version']."&charset=".$parameter['charset']
            ."&signMethod=".$parameter['signMethod']."&payType=".$parameter['payType']
            ."&transType=".$parameter['transType']."&merId=".$parameter['merId']
            ."&orderNumber=".$parameter['orderNumber']."&orderTime=".$parameter['orderTime']
            ."&qid=".$parameter['qid']."&".md5($parameter['signkey']);
        $sign = md5($sign_src);
        return $sign;
    }
  
}