<?php
// 本类由系统自动生成，仅供测试用途
class TestAction extends Action {
	public function post($url, $post_data = '', $timeout = 5){//curl
 
        $ch = curl_init();
 
        curl_setopt ($ch, CURLOPT_URL, $url);
 
        curl_setopt ($ch, CURLOPT_POST, 1);
 
        if($post_data != ''){
 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
 
        }
 				
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
 
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
 
        curl_setopt($ch, CURLOPT_HEADER, false);
        echo "aa";
        exit;
 
        $file_contents = curl_exec($ch);
 				dump($file_contens);
 				exit;
        curl_close($ch);
        return $file_contents;
 
    }
    public function index(){
    	$postData = '<?xml version="1.0" encoding="UTF-8"?><queryInvestor CertificateType="0" IdentityAccountID="422201198904050822" UUID="5860c93420a648fd880d744ffa8b077b"/>';
    	echo $this->post("https://etradetest.bosera.com/servlet/trade.zd_as",$postData);

    }   
}