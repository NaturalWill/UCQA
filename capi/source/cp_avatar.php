<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_avatar.php 13149 2009-08-13 03:11:26Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//����ͷ���ַ
$avatar_size=$_GET['avatar_size'];
$get_avatar=$_GET['get_avatar'];
if(!empty($get_avatar)&&!empty($avatar_size)){
	capi_showmessage_by_data('do_success', 0, array('avatar_url'=>avatar($space['uid'],$avatar_size,TRUE)));
}

if(capi_submitcheck('avatarsubmit')) {
	
	if(empty($_FILES['Filedata']))
		capi_showmessage_by_data('upload_error');
	
	$filepath=realpath($_FILES['Filedata']['tmp_name']);
	if($filepath){
		
		include_once S_ROOT.'./uc_client/client.php';
		$uc_avatar_url = capi_uc_avatar($_SGLOBAL['supe_uid'], (empty($_SCONFIG['avatarreal'])?'virtual':'real'), 1);
		$data=array(
			'Filedata'=>'@'.$filepath.";type=".$_FILES['Filedata']['type'].";filename=".$_FILES['Filedata']['name']
		);
		$curl=my_curl($uc_avatar_url, $data);
		//capi_runlog('curl',$curl);
		$result=@json_decode($curl);
		
		if($result->code==0){
			/*
			$curl_cookie=$separate='';
			foreach($_COOKIE as $key => $val) {
				$curl_cookie.=$separate.$key.'='.$val;
				$separate=';';
			}
			*/
			$url=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME'];
			$url.='?ac=avatar&m_auth='.rawurlencode($_GET['m_auth']);
			
			$curl=my_curl($url);
			//capi_runlog('curl',$curl);
			$result=@json_decode($curl);
			
			if($result->code==0){
				capi_showmessage_by_data('do_success', 0, array('avatar_url'=>avatar($space['uid'],'middle',TRUE)));
			}
		}
	}
	capi_showmessage_by_data('non_normal_operation');
}

//ͷ��
include_once S_ROOT.'./uc_client/client.php';
$uc_avatar = capi_uc_avatar($_SGLOBAL['supe_uid'], (empty($_SCONFIG['avatarreal'])?'virtual':'real'));

//�ж��û��Ƿ�������ͷ��
$setarr = array();
$avatar_exists = ckavatar($space['uid']);
if($avatar_exists) {
	if(!$space['avatar']) {
		//��������
		$reward = getreward('setavatar', 0);
		if($reward['credit']) {
			$setarr['credit'] = "credit=credit+$reward[credit]";
		}
		if($reward['experience']) {
			$setarr['experience'] = "experience=experience+$reward[experience]";
		}
		
		$setarr['avatar'] = 'avatar=1';
		$setarr['updatetime'] = "updatetime=$_SGLOBAL[timestamp]";
	}
} else {
	if($space['avatar']) {
		$setarr['avatar'] = 'avatar=0';
	}
}

if($setarr) {
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarr)." WHERE uid='$space[uid]'");
	//�����¼
	if($_SCONFIG['my_status']) {
		inserttable('userlog', array('uid'=>$_SGLOBAL['supe_uid'], 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
	}
}

//include template("cp_avatar");
capi_showmessage_by_data('do_success', 0, array('uc_avatar'=> $uc_avatar));
?>