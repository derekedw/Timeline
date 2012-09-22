#!/bin/bash

echo Tapestry_sift_install.sh
echo
echo Installs the Tapestry web application for viewing super timelines
echo on the SIFT workstation.
echo 
echo Author:  Derek Edwards \(derekedw@yahoo.com\)
echo

echo Downloading the software
cd ~/Downloads
wget https://github.com/derekedw/Timeline/zipball/master
mv master Tapestry.zip

echo Extracting the installation package
unzip Tapestry.zip

echo Creating a configuration file
cd derekedw-Timeline*
cd webroot
sed -e '/TLNDBUSER/s/yourMySQLUser/tapestry/
	/TLNDBPASS/s/yourMySQLPassword/forensics/
	/TLNDBNAME/s/timeline/tapestry/' tln-config-default.php > tln-config.php

echo Creating a mysql database \'tapestry\'
echo and a database user \'tapestry\'@\'localhost\'
mysql -u root -p mysql --password=forensics <<'EOF'
create database tapestry;
grant all privileges on tapestry.* 
to 'tapestry'@'localhost' identified by 'forensics';
flush privileges;
exit
EOF

echo Installing webroot files
if [ ! -d "/var/www/tapestry" ]; then 
	sudo mkdir /var/www/tapestry
fi
sudo cp -r * /var/www/tapestry

echo Installing slider and date-chooser files
cd ..
sudo cp -r slider date-chooser /var/www/tapestry

echo Opening Firefox to install the database
set +x
firefox http://localhost/tapestry/install.php
