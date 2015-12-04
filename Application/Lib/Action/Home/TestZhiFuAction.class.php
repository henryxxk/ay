<?php
// 本类由系统自动生成，仅供测试用途
class TestZhiFuAction extends Action {
	public function index(){
	    	$this->assign('name','aaa');
	    	$this->display();
	}

	public function test(){
		$this->ajaxReturn(array('code'=>0,'msg'=>'success'));
	}

	public function getjyInfo(){
		 $ZhiFu = new ZhiFuBehavior();
                      $parameter = array(
		    'queryUrl' => $ZhiFu->{"JSPT_QUERY_URL"},
		    'version' => '1.0.0',
		    'charset' => $ZhiFu->{"CHARSET"},
		    "signMethod" =>$ZhiFu->{"SIGNMETHOD"},                     //签名方法  
		    'payType' => 'B2C',
		    'transType' => '01',
		    'merId' => $ZhiFu->{"MERID"},
		    'orderNumber' => '201508192037227208',
		    'orderTime' => '',
		    'qid' => '',
		    'signkey' => $ZhiFu->{"SIGNKEY"}
		);
		$sign = $this->getSign($parameter);
		$parameter['sign'] = $sign;

		 $uri = $parameter['queryUrl'];
		$result = $this-> sendHttpRequest($uri, $parameter);
		$xml = simplexml_load_string($result);
		dump($xml);
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

	/**
 * 后台交易 HttpClient通信
 * @param unknown_type $params
 * @param unknown_type $url
 * @return mixed
 */
public function sendHttpRequest($url, $params) {
    $opts = $this->getRequestParamString ( $params );
    
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//不验证证书
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false);//不验证HOST
    curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
            'Content-type:application/x-www-form-urlencoded;charset=UTF-8' 
    ) );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $opts );
    
    /**
     * 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
     */
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
    
    // 运行cURL，请求网页
    $html = curl_exec ( $ch );
    // echo $html;
    // $res= curl_errno($ch);
    // dump(curl_errno($ch));
    // close cURL resource, and free up system resources
    curl_close ( $ch );
    return $html;
}

/**
 * 组装报文
 *
 * @param unknown_type $params          
 * @return string
 */
public function getRequestParamString($params) {
    $params_str = '';
    foreach ( $params as $key => $value ) {
        $params_str .= ($key . '=' . (!isset ( $value ) ? '' : urlencode( $value )) . '&');
    }
    return substr ( $params_str, 0, strlen ( $params_str ) - 1 );
}


}
?>