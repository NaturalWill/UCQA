<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_bwztdivision.php 7690 2008-06-18 06:18:39Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//�����Ϣ
$bwztdivisionid = empty($_GET['bwztdivisionid'])?0:intval($_GET['bwztdivisionid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$bwztdivision = array();
if($bwztdivisionid) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('bwztdivision')." WHERE bwztdivisionid='$bwztdivisionid' AND uid='$_SGLOBAL[supe_uid]'");
	$bwztdivision = $_SGLOBAL['db']->fetch_array($query);
}
if(empty($bwztdivision)) showmessage('did_not_specify_the_type_of_operation');

if ($op == 'edit') {
	
	if(submitcheck('editsubmit')) {
		
		$_POST['bwztdivisionname'] = getstr($_POST['bwztdivisionname'], 40, 1, 1, 1);
		if(strlen($_POST['bwztdivisionname']) < 1) {
			showmessage('enter_the_correct_bwztdivision_name');
		}
		updatetable('bwztdivision', array('bwztdivisionname'=>$_POST['bwztdivisionname']), array('bwztdivisionid'=>$bwztdivisionid));
		showmessage('do_success', $_POST['refer'], 0);
	}

} elseif ($op == 'delete') {
	//ɾ������
	if(submitcheck('deletesubmit')) {
		//������־����
		updatetable('bwzt', array('bwztdivisionid'=>0), array('bwztdivisionid'=>$bwztdivisionid));
		$_SGLOBAL['db']->query("DELETE FROM ".tname('bwztdivision')." WHERE bwztdivisionid='$bwztdivisionid'");
		
		showmessage('do_success', $_POST['refer'], 0);
	}
}

//ģ��
include_once template("cp_bwztdivision");
	
?>