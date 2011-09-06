<?php

/*

USAGE: configure client machine to wget or curl this script every <interval> to keep dynamic DNS records at slicehost current

http://ip.hostname.com/ - by default or with bad arguments, the script will return the current remote IP.
http://ip.hostname.com/?h=<hostname>&r=<slicehost record ID>&i=<OPTIONAL: current host IP&p=<slicehost API password> - will return either no change necessary or will update the IP.

 
what we want this script to do
 - script is executed from server
 - determine public IP of server (or defined IP)
 - poll nameserver service to determine if public IP and for specified hostname match
 - if they match, report success and no update
 - if they do not match, perform update, report success or failure of update
*/

function valid_ip($ip) {
	return preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])" .
		"(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ip);
}

if ($_GET['h']) $hostname=$_GET['h']; // hostname
if ($_GET['u']) $username=$_GET['u']; // username
if ($_GET['p']) $password=$_GET['p']; // api key
if ($_GET['z']) $zoneid=$_GET['z']; // zone id
if ($_GET['r']) $recordid=$_GET['r']; // record id
if ($_GET['i'] && valid_ip($_GET['i'])) { // ip address
	$record['data']=$_GET['i'];
} else {
	$record['data']=$_SERVER['REMOTE_ADDR'];
}

($_GET['host'])?$current_host=$_GET['host']:$current_host='slicehost';

if ($current_host=='slicehost') {
	if ($hostname) {
		$current_record_value=exec("nslookup ".escapeshellarg($hostname)." ns1.slicehost.com | grep Address | tail -1 | awk '{print $2}'");
		if ($record) {
			if ($current_record_value==$record['data']) {
				exit('No update required: DNS record == current IP ('.$record['data'].')');
			} else {
				if ($password && $recordid) {
					require('slicemanager.php');
					$slice=new SliceManager($password);
					$slice->updateRecord($recordid,$record);
					exit('DNS record updated: '.$hostname.' == '.$record['data']);
				} else {
					//no password or recordid
					exit($_SERVER['REMOTE_ADDR']);
				}
			}
		} else {
			//no current IP address
			exit($_SERVER['REMOTE_ADDR']);
		}
	} else {
		//no hostname
		exit($_SERVER['REMOTE_ADDR']);
	}
} else if ($current_host=='pointhq') {
	if ($hostname) {
		$current_record_value=exec("nslookup ".escapeshellarg($hostname)." dns1.pointhq.com | grep Address | tail -1 | awk '{print $2}'");
		if ($record) {
			if ($current_record_value==$record['data']) {
				exit('No update required: DNS record == current IP ('.$record['data'].')');
			} else {
				if ($password && $recordid) {
					print_r(exec("curl -H 'Accept: application/xml' -H 'Content-type: application/xml' http://pointhq.com/zones/".$zoneid."/records/".$recordid." -u ".$username.":".$password." -X PUT -d '<zone-record><data>".$record['data']."</data><name>".$hostname.".</name></zone-record>'"));
					exit('DNS record updated: '.$hostname.' == '.$record['data']);
				} else {
					//no password or recordid
					exit($_SERVER['REMOTE_ADDR']);
				}
			}
		} else {
			//no current IP address
			exit($_SERVER['REMOTE_ADDR']);
		}
	} else {
		//no hostname
		exit($_SERVER['REMOTE_ADDR']);
	}
}