<?php
@header('Content-type:text/html;charset=UTF-8');
error_reporting(E_ERROR | E_WARNING | E_PARSE);
require_once 'HttpClient.php';
require_once 'payconf.php';
date_default_timezone_set('PRC');
class send {

	var $parameter;       //²ÎÊýÊý×é
	var $signkey;
	var $url;

	//¹¹Ôìº¯Êý
	function send($parameter) {
		//payUrl                           
		//version                       (±ØÌî) ¹Ì¶¨Öµ:1.0.0 ²éÑ¯Ê±Ê¹ÓÃ1.0.0
		//charset                       (±ØÌî) GBK»òUTF-8
		//signMethod                    (±ØÌî) MD5 
		//payType                       (±ØÌî) B2B»òB2C 
		//transType                     (±ØÌî)01¡ªÏû·Ñ½»Ò× 
		//merId                         (±ØÌî) ÓëÇåËãÆ½Ì¨Ç©Ô¼Ê± £¬ÓÉÇåËãÆ½Ì¨Í³Ò»·ÖÅä¡£×î³¤30Î» 
		//orderTime                     (±ØÌî) ¸ñÊ½£º yyyyMMddHHmmss 
		//orderNumber                   (±ØÌî) ÉÌ»§µÄ½»Ò×¶¨µ¥ºÅ ,ÓÉÉÌ»§ÍøÕ¾Éú³É,×î´ó³¤¶È32 
		//qid                           (¿ÉÑ¡)Ô­Ê¼½»Ò×Á÷Ë®ºÅ qid
		
		$this->parameter = $parameter;

		$this->signkey = $parameter['signkey'];
	}
	function getSign() {
		$sign_src = "version=".$this->parameter['version']."&charset=".$this->parameter['charset']
			."&signMethod=".$this->parameter['signMethod']."&payType=".$this->parameter['payType']
			."&transType=".$this->parameter['transType']."&merId=".$this->parameter['merId']
			."&orderNumber=".$this->parameter['orderNumber']."&orderTime=".$this->parameter['orderTime']
			."&qid=".$this->parameter['qid']."&".md5($this->parameter['signkey']);
		
		$sign = $this->sign($sign_src);
		return $sign;
	}
	
	function sign($src) {
		return md5($src);
	}
}

$params = array(
	'queryUrl' => $_POST["queryUrl"],
	'version' => $QUE_VERSION,
	'charset' => $CHARSET,
	'signMethod' => $SIGNMETHOD,
	'payType' => 'B2C',
	'transType' => $PAY_TRANSTYPE,
	'merId' => $_POST["merId"],
	'orderNumber' => $_POST["orderNumber"],
	'orderTime' => $_POST["orderTime"],
	'qid' => $_POST["qid"],
	'signkey' => $_POST["signKey"]
);

$send = new send($params);
$sign= $send->getSign();
$sk = $SIGNKEY;
$params['sign']=$sign ;

dump($params); exit;

$result = sendHttpRequest($params, $params['queryUrl']);
echo $result."<br><br><br>";
$xml = simplexml_load_string($result);
foreach($xml->queryorder as $row){
		$flag = verify($row, $sk, $params['charset']);
}

function verify($row, $sk, $charset){
		$str =   "payType=".$row -> payType."&"
				."transType=".$row -> transType."&"
				."merId=".$row -> merId."&"
				."orderNumber=".$row -> orderNumber."&"
				."qid=".$row -> qid."&"
				."orderAmount=".$row -> orderAmount."&"
				."payAmount=".$row -> payAmount."&"
				."state=".$row -> state."&"
				."refundAmount=".$row -> refundAmount."&"
				."orderCurrency=".$row -> orderCurrency."&"
				."orderTime=".$row -> orderTime."&"
				."merReserved1=".$row -> merReserved1."&"
				."merReserved2=".$row -> merReserved2."&"
				."merReserved3=".$row -> merReserved3."&";
		
		$send = new send(array());
		$str = $str.md5($sk);
		echo $str."<br>";
		
		if(md5(mb_convert_encoding($str, $charset, "utf-8")) == ($row -> sign) ){
			echo mb_convert_encoding("Ç©ÃûÍ¨¹ý£¬²éÑ¯³É¹¦", "utf-8", "gbk")."<br>";
		}else{
			echo mb_convert_encoding("Ç©ÃûÎ´Í¨¹ý£¬²éÑ¯Ê§°Ü", "utf-8", "gbk")."<br>";
		}
}
?>