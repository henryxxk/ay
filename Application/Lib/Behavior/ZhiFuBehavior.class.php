<?php  
/** 
 * RSA算法类 
 * 签名及密文编码：base64字符串/十六进制字符串/二进制字符串流 
 * 填充方式: PKCS1Padding（加解密）/NOPadding（解密） 
 * 
 * Notice:Only accepts a single block. Block size is equal to the RSA key size!  
 * 如密钥长度为1024 bit，则加密时数据需小于128字节，加上PKCS1Padding本身的11字节信息，所以明文需小于117字节 
 * 
 * @author: linvo 
 * @version: 1.0.0 
 * @date: 2015/7/10
 */  
class ZhiFuBehavior
{
  
     /* Version 0.9, 6th April 2003 - Simon Willison ( http://simon.incutio.com/ )
   Manual: http://scripts.incutio.com/Payconf/
*/
    // Request vars
    public $JSPT_PAY_URL = "https://www.ezf123.com/jspt/payment/payment.action";   //此处为模拟联调支付地址，正式投产时需使用清算平台统一分配的地址
    public $JSPT_QUERY_URL = "https://www.ezf123.com/jspt_query/payment/order-query.action";   //此处为模拟联调查询地址，正式投产时需使用清算平台统一分配的地址
    public $PAY_VERSION = "1.0.1"; // 支付版本
    public $QUE_VERSION = "1.0.0"; //查询版本
    public $PAY_TRANSTYPE = "01";  //01-消费交易
    public $PAY_TRANSTYPE03 = "03";    //03-查询付款账户
    
    // 退款相关
    public $REFUND_PAY_URL = "https://www.ezf123.com/jspt/payment/order-refund.action";    //此处为模拟联调退款地址，正式投产时需使用清算平台统一分配的地址
    public $REFUND_QUERY_URL = "https://www.ezf123.com/jspt_query/payment/refund-query.action";    //此处为模拟联调退款查询地址，正式投产时需使用清算平台统一分配的地址
    public $REF_PAY_VERSION = "1.0.0"; // 退款版本
    public $REF_QUE_VERSION = "1.0.0"; // 退款查询版本
    public $REF_TRANSTYPE = "02";      //02-退款交易
    
    // 公用数据
    public $CHARSET = "UTF-8";         //字符集UTF-8或者GBK
    public $SIGNMETHOD = "MD5";            //加密方式
    public $ORDERCURRENCY = "156";     // 交易币种
    public $MERID = "10505";       //此处为模拟联调商户号，正式投产时需使用清算平台分配的商户号
    public $SIGNKEY = "PQPNTWQDDMD8QK2FT4A1NT8Z9GFYKPL8AURM3QX465MBEA7D5M167JTQ9W7E"; //此处为模拟联调密钥，正式投产时需使用清算平台分配的密钥

    public $parameter;       //参数数组
    // public $signkey;
    public $url;
  
    // header ( 'Content-type:text/html;charset=gbk' );
/**
 * 后台交易 HttpClient通信
 * @param unknown_type $params
 * @param unknown_type $url
 * @return mixed
 */
public function sendHttpRequest($params, $url) {
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

//构造函数
  public  function send($parameter) {
        //payUrl                           
        //version                       (必填) 固定值:1.0.2 
        //charset                       (必填) GBK或UTF-8
        //signMethod                    (必填) MD5 
        //payType                       (必填) B2B或B2C 
        //transType                     (必填)01—消费交易 
        //merId                         (必填) 与清算平台签约时 ，由清算平台统一分配。最长30位 
        //backEndUrl                    (可选)不填写时，将无法 接收清算平台发送回来的支付结果通知数据。
        //frontEndUrl                   (可选) 
        //orderTime                     (必填) 格式： yyyyMMddHHmmss 
        //orderNumber                   (必填) 商户的交易定单号 ,由商户网站生成,最大长度32 
        //orderAmount                   (必填) 订单金额,金额单 位：分 
        //orderCurrency                 (必填) 人民币,编码:156 
        //defaultBankNumber             (必填) 建行：105 他行： 999 
        //customerIp                    (可选) 
        //merReserved1                  (可选) 商户自定义字段1 ，如果包含中文，需要按照提供的字符集进行编码转换 
        //merReserved2                  (可选) 商户自定义字段1 ，如果包含中文，需要按照提供的字符集进行编码转换 
        //merReserved3                  (可选) 商户自定义字段1 ，如果包含中文，需要按照提供的字符集进行编码转换 
        //merSiteIP                     (可选) 后台商户防欺诈设 置：IP合法性校验 
        //gateWay                       (可选) 此参数可以控制银行支付时网银上页面标签的显示
        //terType                       (必填) 终端类型取值：00—PC机网页 01—自助设备 02—手持终端 03—后台服务端 默认值为00
        //agentAmount                   (必填) 虚拟账号1=金额1|虚拟账号2=金额2|虚拟账号3=金额3
        $this->parameter = $parameter;
        
        //支付密钥(必填): 需在支付平台进行设置,可登录商户管理系统进行维护,用于上送商户支付及下传支付结果加密
        // $this->signkey = $parameter['signkey'];
        
        //支付请求URL(必填)
        $this->url = $parameter['payUrl'];
    }

  public  function getSign() {
        $sign_src = "version=".$this->parameter['version']."&charset=".$this->parameter['charset']
            ."&signMethod=".$this->parameter['signMethod']."&payType=".$this->parameter['payType']
            ."&transType=".$this->parameter['transType']."&merId=".$this->parameter['merId']
            ."&backEndUrl=".$this->parameter['backEndUrl']."&frontEndUrl=".$this->parameter['frontEndUrl']
            ."&orderTime=".$this->parameter['orderTime']."&orderNumber=".$this->parameter['orderNumber']
            ."&orderAmount=".$this->parameter['orderAmount']."&orderCurrency=".$this->parameter['orderCurrency']
            ."&defaultBankNumber=".$this->parameter['defaultBankNumber']."&customerIp=".$this->parameter['customerIp']
            ."&merReserved1=".$this->parameter['merReserved1']."&merReserved2=".$this->parameter['merReserved2']
            ."&merReserved3=".$this->parameter['merReserved3']."&merSiteIP=".$this->parameter['merSiteIP']
            ."&gateWay=".$this->parameter['gateWay']."&".md5($this->SIGNKEY);

        $sign = $this->sign($sign_src);
        // $row[0] = $sign;
        // $row[1] = $sign_src;
        // $row[2] = $this->SIGNKEY;
        return $sign;
    }

   public function sign($src) {
        return md5($src);
    }
 
}
?>