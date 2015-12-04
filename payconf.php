<?php

/* Version 0.9, 6th April 2003 - Simon Willison ( http://simon.incutio.com/ )
   Manual: http://scripts.incutio.com/Payconf/
*/
    // Request vars
	$JSPT_PAY_URL = "http://test.ezf123.com/jspt/payment/payment.action"; 	//此处为模拟联调支付地址，正式投产时需使用清算平台统一分配的地址
	$JSPT_QUERY_URL = "http://test.ezf123.com/jspt_query/payment/order-query.action";	//此处为模拟联调查询地址，正式投产时需使用清算平台统一分配的地址
	$PAY_VERSION = "1.0.1";	// 支付版本
	$QUE_VERSION = "1.0.0";	//查询版本
	$PAY_TRANSTYPE = "01"; 	//01-消费交易
	$PAY_TRANSTYPE03 = "03";	//03-查询付款账户
	
	// 退款相关
	$REFUND_PAY_URL = "http://test.ezf123.com/jspt/payment/order-refund.action";	//此处为模拟联调退款地址，正式投产时需使用清算平台统一分配的地址
	$REFUND_QUERY_URL = "http://test.ezf123.com/jspt_query/payment/refund-query.action";	//此处为模拟联调退款查询地址，正式投产时需使用清算平台统一分配的地址
	$REF_PAY_VERSION = "1.0.0";	// 退款版本
	$REF_QUE_VERSION = "1.0.0";	// 退款查询版本
	$REF_TRANSTYPE = "02";	 	//02-退款交易
	
	// 公用数据
	$CHARSET = "UTF-8";			//字符集UTF-8或者GBK
	$SIGNMETHOD = "MD5";	 		//加密方式
	$ORDERCURRENCY = "156";		// 交易币种
	$MERID = "10001";		//此处为模拟联调商户号，正式投产时需使用清算平台分配的商户号
	$SIGNKEY = "8888888888888";	//此处为模拟联调密钥，正式投产时需使用清算平台分配的密钥
?>