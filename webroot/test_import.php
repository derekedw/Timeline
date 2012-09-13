﻿<html>
<body>
<?php
require_once('tln-config.php');
require_once('TlnData.php');
require_once('Job.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);

if (mysqli_connect_errno()) {
	die('Connect error: ' . mysqli_connect_error());
}

$tln = new TlnData($db);
$text = '08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd /SecDesc ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd /SecDesc ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd /CurrVal ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd /CurrVal ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd  ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd  ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd /CupdTime ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd /CupdTime ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd /OldVal ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd /OldVal ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd /OupdTime ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd /OupdTime ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd /CurrVal ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd /CurrVal ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd /OldVal ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd /OldVal ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd /CupdTime ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd /CupdTime ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd  ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd  ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd /OupdTime ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd /OupdTime ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:01,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/TS:InternetConnectorPswd /SecDesc ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/TS:InternetConnectorPswd /SecDesc ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:11,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882  ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882  ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:11,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /OupdTime ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /OupdTime ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:12,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /CupdTime ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /CupdTime ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:11,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /SecDesc ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /SecDesc ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:12,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /OldVal ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /OldVal ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:12,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /CurrVal ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /CurrVal ,2,/mnt/msdos/winnt/system32/config/security,132802,-,Log2t::input::security,-
08/30/2003,13:12:12,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /CurrVal ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /CurrVal ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:12,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /OldVal ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /OldVal ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:12,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /CupdTime ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /CupdTime ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:11,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882  ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882  ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:11,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /OupdTime ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /OupdTime ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
08/30/2003,13:12:11,EST5EDT,MACB,REG,SECURITY key,Last Written,-,-,SECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /SecDesc ,Key name: HKLM/SECURITYSECURITY/Policy/Secrets/XATM:ffea5f8f-15fd-4786-9e70-f57bad1a6882 /SecDesc ,2,/mnt/msdos/winnt/repair/security,141701,-,Log2t::input::security,-
';
$tln->import($text);
$db->close();
print 'All done!<br />';

?>
</body>
</html>
