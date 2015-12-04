<?php
/*
$rule,要验证的规则名称；
$uid,用户的id；
$relation，规则组合方式，默认为‘or’，以上三个参数都是根据Auth的check（）函数来的，
$t,符合规则后，执行的代码
$f，不符合规则的，执行代码，默认为抛出字符串‘没有权限’
*/
function authcheck($rule,$uid,$relation='or',$t=true,$f=false){
	if(in_array($uid,C('Administrator')) ){
		return $t;
	}else{
		import('ORG.Util.Auth');
		$auth = new Auth(); 
		return $auth->check($rule,$uid,$relation)?$t:$f;
	}
}

/**
 * md5加密处理
 * @param string password 需要加密的密码
 */
function pwdHash($password, $type = 'md5') {
	return hash ( $type, $password );
}

/**
 * 返回时间相差天数函数
 * @param string $Date 需要与当前时间比较的时间字符串 2015-03-15
 * @return int 相差天数
 */
function returnDateDiff($Date = ''){
    	import('ORG.Util.Date');// 导入日期类
		if(empty($Date)){
			$Date = new Date('Y-m-d');
		}else{
			$Date = new Date($Date);
		}
		// 比较日期差  Y-年M-月w-星期d-天h-小时m-分钟s-秒
		return $Date->dateDiff(date('Y-m-d'),'d');
}
/**
 * 返回给定时间的月份最后一天是几号函数
 * @param string $Date 需要与当前时间比较的时间字符串 如：2015-03-15
 * @return string 月里的几号  如：31
 */
function returnLastDayOfMonth($Date2 = ''){
    	import('ORG.Util.Date');// 导入日期类 
		if(empty($Date2)){
			$Date2 = new Date('Y-m-d');
		}else{
			$Date2 = new Date($Date2);
		}
		return $Date2->lastDayOfMonth(); // 计算当月的最后一天
}
/**
 * 返回给定时间的当月最大天数
 * @param string $Date 需要与当前时间比较的时间字符串 2015-03-15
 * @return int 天数 
 */
function returnMaxDayOfMonth($Date2 = ''){
    	import('ORG.Util.Date');// 导入日期类 
		$Date = new Date('Y-m-d');
		if(empty($Date2)){
			$Date2 = new Date('Y-m-d');
		}else{
			$Date2 = new Date($Date2);
		}
		return $Date2->maxDayOfMonth(); // 计算当月的最大天数
}
/**
 * 字符串截取
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){  
  if(function_exists("mb_substr")){  
              if($suffix)  
              return mb_substr($str, $start, $length, $charset)."...";  
              else
                   return mb_substr($str, $start, $length, $charset);  
         }  
         elseif(function_exists('iconv_substr')) {  
             if($suffix)  
                  return iconv_substr($str,$start,$length,$charset)."...";  
             else
                  return iconv_substr($str,$start,$length,$charset);  
         }  
         $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef]
                  [x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";  
         $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";  
         $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";  
         $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";  
         preg_match_all($re[$charset], $str, $match);  
         $slice = join("",array_slice($match[0], $start, $length));  
         if($suffix) return $slice."......";  
         return $slice;
}
/**
 * 图表数据拼接
 * @param array $list 查询的集合
 * @param string $filed 拼接的字段
 * @param string $tag 拼接前缀
 */
function echartData($list,$field,$prefix=''){
	$data = ""; 
	foreach ($list as $key => $value) {
		if(!empty($prefix)){
			$data .= "'".$prefix.(1+$key).' '.$value[$field].'('.$value['sex'].")',";
		}else{
			$data .= "'".$value[$field]."'".',';
		}
	}
	$data = rtrim($data,',');
	return $data;
}

/**
 * 获取表数据
 * @param string $table 表名
 * @param array $map 查询条件
 * @tag int default 1 标记 1 find 2 select 3 getField
 */
function getTable($table='',$map=array(),$tag=1){
	if(empty($table)){
		return '';
	}
	$model = M($table);
	if($tag == 1){
		$list = $model->where($map)->find();
	}elseif($tag == 2){ 
		$list = $model->where($map)->select();
	}elseif($tag == 3){
		$list = $model->where($map)->select();
	}
	return $list;
}
/**
 * 获取分类名称树名称
 * @param int id  
 */
function getTreeTypeName($id=0){
	if($id<0){
		return;
	}
	$model = M('Type');
	$map['id'] = array('eq',$id);
	$list = $model->where($map)->find();
	if($list['parentId'] == 0){
		$str = $list['name'].'/'.$_SESSION['treeTypeName'];
		$_SESSION['treeTypeName'] = null;
		return $str;
	}else{
		$_SESSION['treeTypeName'] = $list['name'].'/'.$_SESSION['treeTypeName']; 
		return getTreeTypeName($list['parentId']);
	}
}
/**
 * 获取分类名称树id
 * @param int id  
 */
function getTreeTypeId($id=0){
	if($id<0){
		return;
	}
	$model = M('Type');
	$map['id'] = array('eq',$id);
	$list = $model->where($map)->find();
	if($list['parentId'] == 0){
		$str = $list['id'].'/'.$_SESSION['treeTypeId'];
		$_SESSION['treeTypeId'] = null;
		return $str;
	}else{
		$_SESSION['treeTypeId'] = $list['id'].'/'.$_SESSION['treeTypeId']; 
		return getTreeTypeId($list['parentId']);
	}
} 
/**
 * 获取分类名称树id 根据父目录向下查询
 * @param int id  
 */
function getTreeTypeIdDown($id=0){
	if($id<0){
		return;
	}
	$model = M('Type');
	$map['parentId'] = array('eq',$id);
	$map['status'] = array('eq',1);
	$list = $model->where($map)->select();
	$newIds1 = array();
	foreach ($list as $key => $value) {
		$newIds1[$key] = $value['id'];
	}
	//------------ 
	$map['parentId'] = array('in',$newIds1); 
	$list = $model->where($map)->select();
	$newIds2 = array();
	foreach ($list as $key => $value) {
		$newIds2[$key] = $value['id'];
	}
	$newIds = array_merge($newIds1, $newIds2); 

	return $newIds;
} 
/**
 * 获取分类名称名称
 * @param int id  
 */
function getTypeName($id=0){
	if($id<0){
		return;
	}
	$model = M('Type');
	$map['id'] = array('eq',$id);
	$list = $model->where($map)->find();
	if($list){
		return $list['name'];
	}else{
		return '';
	}
}
/**
 * 获取品牌名称名称
 * @param int id  
 */
function getBrandName($id=0){
	if($id<0){
		return;
	}
	$model = M('Goodsbrand');
	$map['id'] = array('eq',$id);
	$list = $model->where($map)->find();
	if($list){
		return $list['name'];
	}else{
		return '';
	}
}
/**
 * 获取颜色名称名称
 * @param int id  
 */
function getColorName($id=0){
	if($id<0){
		return;
	}
	$model = M('Goodscolor');
	$map['id'] = array('eq',$id);
	$list = $model->where($map)->find();
	if($list){
		return $list['name'];
	}else{
		return '';
	}
}

/**
 * 获取颜色名称名称
 * @param string id  
 */
function getColorNameStr($id=''){
	if($id == ''){
		return;
	}
	$idArr = explode(",",$id);
	$model = M('Goodscolor');
	$map['id'] = array('in',$idArr);
	$list = $model->where($map)->select();
	if($list){
		$strName = '';
		foreach ($list as $key => $value) {
			$strName .= $value['name'].',';
		}
		$strName = rtrim($strName,',');
		return $strName;
	}else{
		return '';
	}
}
/**
 * 获取尺寸名称名称
 * @param int id  
 */
function getSizeName($id=0){
	if($id<0){
		return;
	}
	$model = M('Goodssize');
	$map['id'] = array('eq',$id);
	$list = $model->where($map)->find();
	if($list){
		return $list['name'];
	}else{
		return '';
	}
}
/**
 * 获取级别名称名称
 * @param int id  
 */
function getViplevelName($id=0){
	if($id<0){
		return;
	}
	$model = M('Viplevel');
	$map['id'] = array('eq',$id);
	$list = $model->where($map)->find();
	if($list){
		return $list['name'];
	}else{
		return '';
	}
}
/**
 * 通过积分获取级别名称名称
 * @param int code 
 */
function getViplevelNameCode($code=0){
	if($code<0){
		return;
	}
	$model = M('Viplevel');
	$map['status'] = array('eq',1);
	$allCode = $model->where($map)->select();
	$name = '';
	if($allCode[0]['code'] < $code){
		$name = $allCode[0]['name'];
	}else if($allCode[1]['code'] < $code){
		$name = $allCode[1]['name'];
	}else if($allCode[2]['code'] < $code){
		$name = $allCode[2]['name'];
	}
	return $name;
}
/**
 * 获取用户名称 
 * @param int id 用户表ID
 */
function getUserField($id = '',$table='Member',$field='name'){
    $mUser = M($table);
	if(!empty($id)){
		$where['id'] = array('eq',$id);
		$dUser = $mUser->where($where)->getField($field);
	}else{
		$dUser = '';
	}
    return $dUser;
}
/**
 * 获取字段 
 * @param int id 表ID
 */
function getField($id = '',$field='name',$table='Member'){
    $mUser = M($table);
	if(!empty($id)){
		$where['id'] = array('eq',$id);
		$dUser = $mUser->where($where)->getField($field);
	}else{
		$dUser = '';
	}
    if($id==0){
        $where['id'] = array('eq',1);
        $dUser = $mUser->where($where)->getField($field);
    }
    return $dUser;
}
/**
 * 获取字段 
 * @param int id 表ID
 */
function getField2($id = '',$field='name',$table='Member',$a=false,$b=''){
    $mUser = M($table);
	if(!empty($id)){
		if($a){
			$where[$b] = array('eq',$id);
		}else{ 
			$where['id'] = array('eq',$id);
		}
		$dUser = $mUser->where($where)->getField($field);
	}else{
		$dUser = '';
	}
    return $dUser;
}
/**
 * 获取多图片的单张图片
 * @param string imgs 被逗号分割的多张图片字符串
 * @param int index 获取第几张图片 默认第一张 index=0
 * $a = getImgUrl("/Public/Uploads/1.jpg,/Public/Uploads/2.jpg,/Public/Uploads/3.jpg",1);
 * var_dump($a);
 */
function getImgUrl($imgs='',$index=1){
	$index -= 1; 
	if(empty($imgs)){
		return '';
	}
	$imgArr = explode(',', $imgs);
	$countArr = count($imgArr);
	if($index < 0){
		$index = 0;
	}
	if($index > $countArr){
		$index = $countArr;
	}
	return $imgArr[$index];
}

/**
 * 申请团体
 * @param int $userId 用户id
 * @param string $field 字段
 * @param string $val 值
 * @return bool applyGroup('attestationRemark',3);
 */
function applyGroup($userId=0,$field='',$val=''){ 
	if($userId < 1 || empty($field) || empty($val)){
		return false;
	}
    $mMember = M('Member');
    $mapMember['id'] = array('eq',$userId);
    $data[$field] = $val;
    $data['updatetime'] = time();
    //$dMember = $mMember->where($mapMember)->setField($field,$val);
    $dMember = $mMember->where($mapMember)->save($data);
    if($dMember){
    	return true;
    }
    return false;
}




























/**
 * 获取用户列表 
 * @param int id 用户表ID
 * @param int status 用户表状态
 */
function getUser($id = '',$status='',$table='Student'){
    $mUser = M($table);
    $where['id'] = array('gt',0);
	if(!empty($status)){
		$where['status'] = array('eq',$status);
	}
	if(!empty($id)){
		$where['id'] = array('eq',$id);
	}
	$dUser = $mUser->where($where)->select();
    return $dUser;
}

/**
 * 获取用户性别中文名 
 * @param int sex 1男 2女
 */
function getSexName($sex = 0){
	$arr = array('','男','女');
    return $arr[$sex];
}

/**
 * 获取省份中文名称 
 * @param int code province表 省份编号
 * @return string 省份名称  如果code==all 获取所有
 */
function getProvinceName($code = ''){
    $mProvince = M('Province');
	if(!empty($code) && $code != 'all'){
		$where['code'] = array('eq',$code);
		$province = $mProvince->where($where)->getField('name');
	}else{
		if($code != '' && $code == 'all'){
			$province = $mProvince->select();
		}else{
			$province = '';
		}
	}
    return $province;
}
/**
 * 获取市中文名称 
 * @param int code city表 编号
 * @return string 市名称 如果code==0 获取所有
 */
function getCityName($code = ''){
    $mCity = M('City');
	if(!empty($code) && $code != 'all'){
		$where['code'] = array('eq',$code);
		$city = $mCity->where($where)->getField('name');
	}else{
		if($code != '' && $code == 'all'){
			$city = $mCity->order('code')->select();
		}else{
			$city = '';
		}
	}
    return $city;
}
/**
 * 获取区中文名称 
 * @param int code area表 编号
 * @return string 区名称 如果code==0 获取所有
 */
function getAreaName($code = ''){
    $mArea = M('Area');
	if(!empty($code) && $code != 'all'){
		$where['code'] = array('eq',$code);
		$area = $mArea->where($where)->getField('name');
	}else{
		if($code != '' && $code == 'all'){
			$area = $mArea->order('code')->select();
		}else{
			$area = '';
		}
	}
    return $area;
}
/**
 * AJAX 通过省份编号获取市中文名称 
 * @param int code province编号
 * @return AJAX string 市名称
 */
function getProvinceAllCity($code = ''){
    $mCity = M('City');
	if(!empty($code)){
		$where['provincecode'] = array('eq',$code); 
		$city = $mCity->where($where)->order('code')->select(); 
	}else{
		$city = '';
	}
	return $city;
}
/**
 * AJAX 通过市编号获取区中文名称 
 * @param int code city表 编号
 * @return AJAX string 区名称
 */
function getCityAllArea($code = ''){
    $mArea = M('Area');
	if(!empty($code)){
		$where['citycode'] = array('eq',$code); 
		$area = $mArea->where($where)->order('code')->select(); 
	}else{
		$area = '';
	}
	return $area;
}
/**
 * AJAX 通过区编号获取区游泳馆中文名称 
 * @param int code area表 编号
 * @return AJAX string site表名称
 */
function getAreaAllSite($code = ''){
    $mArea = M('Site');
	if(!empty($code)){
		$where['area_id'] = array('eq',$code); 
		$area = $mArea->where($where)->select(); 
	}else{
		$area = '';
	}
	return $area;
}

/**
 * 获取游泳级别名称 
 * @param int id level表 
 * @return string 名称 如果code==all 获取所有
 */
function getLevelName($id = ''){
    $mLevel = M('Level');
	if(!empty($id) && $id != 'all'){
		$where['id'] = array('eq',$id);
		$level = $mLevel->where($where)->getField('levelname');
	}else{
		if($id != '' && $id == 'all'){
			$level = $mLevel->select();
		}else{
			$level = '';
		}
	}
    return $level;
}
/**
 * 获取场馆名称 
 * @param int id site表 
 * @return string 名称 如果code==all 获取所有
 */
function getSiteName($id = ''){
    $model = M('Site');
	if(!empty($id) && $id != 'all'){
		$where['id'] = array('eq',$id);
		$list = $model->where($where)->getField('title');
	}else{
		if($id != '' && $id == 'all'){
			$list = $model->select();
		}else{
			$list = '';
		}
	}
    return $list;
}
/**
 * 获取教练名称 
 * @param int id coach表 
 * @return string 名称 如果id==all 获取所有
 */
function getCoachName($id = ''){
    $model = M('Coach');
	if(!empty($id) && $id != 'all'){
		$where['id'] = array('eq',$id);
		$list = $model->where($where)->getField('name');
	}else{
		if($id != '' && $id == 'all'){
			$list = $model->select();
		}else{
			$list = '';
		}
	}
    return $list;
}
/**
 * 获取学员名称 
 * @param int id Student表 
 * @return string 名称 如果id==all 获取所有
 */
function getStudentName($id = ''){
    $model = M('Student');
	if(!empty($id) && $id != 'all'){
		$where['id'] = array('eq',$id);
		$list = $model->where($where)->getField('name');
	}else{
		if($id != '' && $id == 'all'){
			$list = $model->select();
		}else{
			$list = '';
		}
	}
    return $list;
}
/**
 * 获取课程名称 
 * @param int id Article表 
 * @return string 名称 如果id==all 获取所有
 */
function getArticleName($id = ''){
    $model = M('Article');
    $where['type'] = array('eq',12);
	if(!empty($id) && $id != 'all'){
		$where['id'] = array('eq',$id);
		$list = $model->where($where)->getField('loginname');
	}else{
		if($id != '' && $id == 'all'){
			$list = $model->where($where)->select();
		}else{
			$list = '';
		}
	}
    return $list;
}
/**
 * 获取课程价格 
 * @param int id Article表 
 * @return string 名称 如果id==all 获取所有
 */
function getArticleCost($id = ''){
    $model = M('Article');
    $where['type'] = array('eq',12);
	if(!empty($id) && $id != 'all'){
		$where['id'] = array('eq',$id);
		$list = $model->where($where)->getField('cost');
	}else{
		$list = ''; 
	}
    return $list;
}
/**
 * 获取文章名称 
 * @param int id Article表 
 * @return string 名称 如果id==all 获取所有
 */
function getArticleTitle($id = ''){
    $model = M('Article');
    $where['type'] = array('eq',12);
	if(!empty($id) && $id != 'all'){
		$where['id'] = array('eq',$id);
		$list = $model->where($where)->getField('title');
	}else{
		$list = ''; 
	}
    return $list;
}

?>