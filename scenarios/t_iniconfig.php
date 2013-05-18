<?php
include("browser.php");

$conf = new ifyConfig('/var/www/ify/config.ini');
$conf->raw();

echo $conf->getUser("login");
echo "</br>";
echo "</br>";
echo "-- jez";
echo $conf->setUser("jez");
echo "</br>";
echo "</br>";
echo $conf->getUser("login");
echo "</br>";
echo $conf->getUser("path");
echo "</br>";
echo "</br>";


echo "== jedsqz";
echo $conf->setUser("jedsqz");
echo "</br>";
echo $conf->getUser("login");
echo "</br>";
echo $conf->getUser("path");
echo "</br>";
echo "</br>";


echo "=== open";
echo $conf->setUser("open");
echo "</br>";
echo $conf->getUser("login");
echo "</br>";
echo $conf->getUser("path");
echo "</br>";


echo "=== users";
echo $conf->setUser("users");
echo "</br>";
echo $conf->getUser("login");
echo "</br>";
echo $conf->getUser("path");
echo "</br>";

echo "=== admin";
echo $conf->setUser("admin");
echo "</br>";
echo $conf->getUser("login");
echo "</br>";
echo $conf->getUser("path");
echo "</br>";

echo "-- jez";
echo $conf->setUser("jez");
echo "</br>";
echo "</br>";
echo $conf->getUser("login");
echo "</br>";
echo $conf->getUser("path");
echo "</br>";
echo "</br>";

?>
