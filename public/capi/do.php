<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do.php 12354 2009-06-11 08:14:06Z liguode $
*/

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'./common.php');

//��ȡ����
$ac = empty($_GET['ac'])?'':$_GET['ac'];

//�Զ����¼
// if($ac == $_SCONFIG['login_action']) {
	// $ac = 'login';
// } elseif($ac == 'login') {
	// $ac = '';
// }
// if($ac == $_SCONFIG['register_action']) {
	// $ac = 'register';
// } elseif($ac == 'register') {
	// $ac = '';
// }

//����ķ���
$acs = array('login', 'register', 'lostpasswd', 'swfupload', 'inputpwd',
	'ajax', 'seccode', 'sendmail', 'stat', 'emailcheck');
if(empty($ac) || !in_array($ac, $acs)) {
	capi_showmessage_by_data('enter_the_space');
}

//����
$theurl = 'do.php?ac='.$ac;

include_once(S_ROOT.'./capi/source/do_'.$ac.'.php');

?>