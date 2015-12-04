<?php
/**
 * 基本控制器
 */
class IesoAction extends Action {
  
    /**
    * 发送xml请求post
    */
    public function sendXml($xml_data){
            // $xml_data = "<xml>...</xml>";
            // $url = "http://121.42.31.134:8888/MyNosql/api!logisbyjson.action";
            $url = "http://anyooh.com/Admin/Base/jsOrderInfo";
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
            $this->ajaxReturn(array('msg'=>'success'));
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
            $this->sendXml($str);
            // $this->ajaxReturn(array('detail'=>$str));
            // $this->ajaxReturn(array('code'=>0,'msg'=>'success','old'=>$old,'head'=>$head,'data'=>$orderInfo,'detail'=>$str));

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

    public function jsOrderInfo(){
            $xml = file_get_contents('php://input');
            $this->ajaxReturn(array('xml'=>$xml));
    }



}
?>
