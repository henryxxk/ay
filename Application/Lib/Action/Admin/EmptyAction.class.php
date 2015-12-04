<?php
class EmptyAction extends Action{
	/**
	 * index()
	 */
	public function index(){
		echo '非法请求'.MODULE_NAME;
	}	
}
?>