<?php
/**
　* 功能:  查询示例
　* 版权:  西安融联网络科技有限公司,Copyright (c) 2012
　* 版本:  1.0.2 2013-04-17
　*/
 require_once 'payconf.php';
?>
<html>
	<head>
		<title>查询交易示例</title>
		<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
	</head>
	<body>
		<h1>
			接口示例-->查询交易示例
		</h1>
		
		<form action="query_send.php" method="post">
			<table border=1 cellSpacing=0 width="80%" style="FONT-SIZE: 12px">
				<tr>
					<td align="right" width="25%">
						查询地址:
					</td>
					<td width="45%">
						<input name="queryUrl" type="text" style="width: 500px;" value="https://www.ezf123.com/jspt_query/payment/order-query.action">
					</td>
					<td width="30%">&nbsp;
						
					</td>
				</tr>
				<tr>
					<td align="right" width="25%">
						商户编号:
					</td>
					<td width="45%">
						<input id="merId" style="width: 500px;" name="merId" type="text" value="10505">
					</td>
					<td width="30%">
						(必填) 与清算平台签约时，由清算平台统一分配。最长30位
					</td>
				</tr>
				<tr>
					<td align="right" width="25%">
						商户密钥:
					</td>
					<td width="45%">
						<input id="signKey" style="width: 500px;" name="signKey" type="text" value="PQPNTWQDDMD8QK2FT4A1NT8Z9GFYKPL8AURM3QX465MBEA7D5M167JTQ9W7E">
					</td>
					<td width="30%">
						(必填) 商户密钥
					</td>
				</tr>
				<tr>
					<td align="right" width="25%">
						商户订单号:
					</td>
					<td width="45%">
						<input name="orderNumber" type="text" style="width: 500px;" value="201508180934446549">
					</td>
					<td width="30%">
						(可选) 商户的交易定单号,由商户网站生成,最大长度32
					</td>
				</tr>
				<tr>
					<td align="right" width="25%">
						交易时间:
					</td>
					<td width="45%">
						<input name="orderTime" type="text" style="width: 500px;" value="">
					</td>
					<td width="30%">
						(可选)交易时间 orderTime 同待查询交易的交易开始日期时间
					</td>
				</tr>
				<tr>
					<td align="right" width="25%">
						原始交易流水号:
					</td>
					<td width="45%">
						<input name="qid" type="text" style="width: 500px;" value="">
					</td>
					<td width="30%">
						(可选)原始交易流水号 qid
					</td>
				</tr>
				<tr>
					<td colspan="3" align="center">
						<input id="querybtn" name="querybtn" type="submit" value=" 提交 ">
					</td>
				</tr>
			</table>
		</form>
	</body>
</html>
