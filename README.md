Dynamic DNS for PointHQ
=======================

A script for using PointHQ [https://pointhq.com/] as a dynamic DNS service. The script takes measures to be polite to PointHQ, as all dynamic dns scripts should: PointHQ's update process is only involved if the IP needs to be changed.

Basic usage
-----------
Install this script on a PHP-capable webhost with a URL you trust to not change (such as a web server!), ensure curl is installed, and then configure your local machine to query a specially-formatted URL at intervals:

	http://ip.your.webhost.com/?host=pointhq&h=hostname&r=recordid&z=zoneid&u=username&p=password&i=ipaddress

*  hostname = The hostname you want to change dynamically (this is the "name" field from the record, e.g., dynamic.hostname.com)
*  recordid = The record ID for the hostname you want to change dynamically. To find it, check the value of the edit link for the hostname on pointhq's site; the URL should contain "/zones/1234/records/678910/edit". In this example, "678910" is the record ID.
*  zoneid = The zone ID. To find it, click on the domain you want on pointhq's site; the URL will contain "/zones/1234/records". In this example, "1234" is the zone ID.
*  username = This is generally the primary email address associated with your account. You can find it [here] (https://pointhq.com/identity/manage?to=/email_addresses).
*  password = Your API key. You can find it [here] (https://pointhq.com/settings).
*  ipaddress = *OPTIONAL* - the IP you want to set the hostname to. If you want this to be determined automatically, do not define this variable.



The basic functionality of the script
-------------------------------------

*  Without any options or if the options are somehow invalid, it simply returns the ip of the requesting machine (REMOTE_ADDR from apache).
*  If the info is valid, it performs a simple DNS query of the hostname given to *ns1.pointhq.com* to confirm the current defined IP.
*  If the IP is the same as the IP requested (which is either determined using REMOTE_ADDR from apache or by using the contents of the "i" variable in the URL), the script reports that no update is required.
*  If the IP differs, the script executes a curl command to send an update request to pointhq using the provided authentication info.


I use this simple script on a linux machine I have running at my house as a file server, added in as a cron job at one hour intervals. It logs back the output of the command to /var/log/messages.

	#!/bin/bash
	curl "http://ip.your.webhost.com/?host=pointhq&h=hostname&r=recordid&z=zoneid&u=username&p=password" -s | logger >> /dev/null 2>&1