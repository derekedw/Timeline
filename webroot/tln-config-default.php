<?php
// Database constants
define('TLNDBHOST', 'localhost');
define('TLNDBUSER', 'yourMySQLUser');
define('TLNDBPASS', 'yourMySQLPassword');
define('TLNDBNAME', 'timeline');

// File upload defaults and limits
define('TLNULPATH', 'D:\temp\uploaded');
define('TLNULMAX', 6*1024*1024);  // Max file upload size = 6 MB

// Buttons
define('TLNUPLOAD', 'Upload File');
define('TLNIMPORT', 'Daily Import');

date_default_timezone_set('UTC');
?>