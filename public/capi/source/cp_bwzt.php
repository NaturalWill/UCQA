<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_bwzt.php 13026 2009-08-06 02:17:33Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//�����Ϣ
$bwztid = empty($_GET['bwztid'])?0:intval($_GET['bwztid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$bwzt = array();
if($bwztid) {
	$query = $_SGLOBAL['db']->query("SELECT bf.*, b.* FROM ".tname('bwzt')." b 
		LEFT JOIN ".tname('bwztfield')." bf ON bf.bwztid=b.bwztid 
		WHERE b.bwztid='$bwztid'");
	$bwzt = $_SGLOBAL['db']->fetch_array($query);
}

//Ȩ�޼��
if(empty($bwzt)) {
	if(!checkperm('allowbwzt')) {
		ckspacelog();
		capi_showmessage_by_data('no_authority_to_add_log');
	}
	
	//ʵ����֤
	ckrealname('bwzt');
	
	//��Ƶ��֤
	ckvideophoto('bwzt');
	
	//���û���ϰ
	cknewuser();
	
	//�ж��Ƿ񷢲�̫��
	$waittime = interval_check('post');
	if($waittime > 0) {
		capi_showmessage_by_data('operating_too_fast',1,array("waittime"=>$waittime));
	}
	
	//�����ⲿ����
	$bwzt['subject'] = empty($_GET['subject'])?'':getstr($_GET['subject'], 80, 1, 0);
	$bwzt['message'] = empty($_GET['message'])?'':getstr($_GET['message'], 5000, 1, 0);
	
} else {
	
	if($_SGLOBAL['supe_uid'] != $bwzt['uid'] && !checkperm('managebwzt')) {
		capi_showmessage_by_data('no_authority_operation_of_the_log');
	}
}
//��ӱ༭����
if(capi_submitcheck('bwztsubmit')) {

	if(empty($bwzt['bwztid'])) {
		$bwzt = array();
	} else {
		if(!checkperm('allowbwzt')) {
			ckspacelog();
			capi_showmessage_by_data('no_authority_to_add_log');
		}
	}
	
	//��֤��
	if(checkperm('seccode') && !ckseccode($_REQUEST['seccode'])) {
		capi_showmessage_by_data('incorrect_code');
	}
	
	include_once(S_ROOT.'./source/function_bwzt.php');
	if($op=='alterstatus'){
		if($newbwztstatus = bwzt_alterstatus($_GET['status'], $bwzt)) {
			capi_showmessage_by_data('do_success', 0, $newbwztstatus);
		} else {
			capi_showmessage_by_data('alter_status_failed');
		}
	}
	if($newbwzt = bwzt_post($_POST, $bwzt)) {
		if(empty($bwzt) && $newbwzt['topicid']) {
			$url = 'space.php?do=topic&topicid='.$newbwzt['topicid'].'&view=bwzt';
		} else {
			$url = 'space.php?uid='.$newbwzt['uid'].'&do=bwzt&id='.$newbwzt['bwztid'];
		}
		capi_showmessage_by_data('do_success', 0, array('url'=> $url));
	} else {
		capi_showmessage_by_data('that_should_at_least_write_things');
	}
}

if($_GET['op'] == 'delete') {
	//ɾ��
	if(capi_submitcheck('deletesubmit')) {
		include_once(S_ROOT.'./source/function_delete.php');
		if(deletebwzts(array($bwztid))) {
			capi_showmessage_by_data('do_success', 0, array("url"=>"space.php?uid=$bwzt[uid]&do=bwzt&view=me"));
		} else {
			capi_showmessage_by_data('failed_to_delete_operation');
		}
	}
	
} elseif($_GET['op'] == 'goto') {
	
	$id = intval($_GET['id']);
	$uid = $id?getcount('bwzt', array('bwztid'=>$id), 'uid'):0;

	capi_showmessage_by_data('do_success', 0, array("url"=> "space.php?uid=$uid&do=bwzt&id=$id"));
	
} elseif($_GET['op'] == 'edithot') {
	//Ȩ��
	if(!checkperm('managebwzt')) {
		capi_showmessage_by_data('no_privilege');
	}
	
	if(capi_submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		updatetable('bwzt', array('hot'=>$_POST['hot']), array('bwztid'=>$bwzt['bwztid']));
		if($_POST['hot']>0) {
			include_once(S_ROOT.'./source/function_feed.php');
			feed_publish($bwzt['bwztid'], 'bwztid');
		} else {
			updatetable('feed', array('hot'=>$_POST['hot']), array('id'=>$bwzt['bwztid'], 'idtype'=>'bwztid'));
		}
		
		capi_showmessage_by_data('do_success', 0,  array("url"=>"space.php?uid=$bwzt[uid]&do=bwzt&id=$bwzt[bwztid]"));
	}
	
} else {
	//��ӱ༭
	//��ȡ���˷���
	$bwztclassarr = $bwzt['uid']?getbwztclassarr($bwzt['uid']):getbwztclassarr($_SGLOBAL['supe_uid']);
	//��ȡ���ҷ���
	$bwztdivisionarr = $bwzt['uid']?getbwztdivisionarr($bwzt['uid']):getbwztdivisionarr($_SGLOBAL['supe_uid']);
	//��ȡ���
	$albums = getalbums($_SGLOBAL['supe_uid']);
	
	$tags = empty($bwzt['tag'])?array():unserialize($bwzt['tag']);
	$bwzt['tag'] = implode(' ', $tags);
	
	$bwzt['target_names'] = '';
	
	$friendarr = array($bwzt['friend'] => ' selected');
	
	$passwordstyle = $selectgroupstyle = 'display:none';
	if($bwzt['friend'] == 4) {
		$passwordstyle = '';
	} elseif($bwzt['friend'] == 2) {
		$selectgroupstyle = '';
		if($bwzt['target_ids']) {
			$names = array();
			$query = $_SGLOBAL['db']->query("SELECT username FROM ".tname('space')." WHERE uid IN ($bwzt[target_ids])");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$names[] = $value['username'];
			}
			$bwzt['target_names'] = implode(' ', $names);
		}
	}
	
	
	$bwzt['message'] = str_replace('&amp;', '&amp;amp;', $bwzt['message']);
	$bwzt['message'] = shtmlspecialchars($bwzt['message']);
	
	$allowhtml = checkperm('allowhtml');
	
	//������
	$groups = getfriendgroup();
	
	//�����ȵ�
	$topic = array();
	$topicid = $_GET['topicid'] = intval($_GET['topicid']);
	if($topicid) {
		$topic = topic_get($topicid);
	}
	if($topic) {
		$actives = array('bwzt' => ' class="active"');
	}
	
	//�˵�����
	$menuactives = array('space'=>' class="active"');
}

//include_once template("cp_bwzt");

$bwzt['formhash']=formhash();
$bwzt['bwztclassarr']=$bwztclassarr;
$bwzt['bwztdivisionarr']=$bwztdivisionarr;
capi_showmessage_by_data('do_success', 0, array("bwzt"=>$bwzt));
?>