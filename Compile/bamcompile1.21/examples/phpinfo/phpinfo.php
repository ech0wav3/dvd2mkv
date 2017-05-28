<?

ob_start();
phpinfo();
$info = ob_get_contents();
ob_end_clean();
$f = fopen("phpinfo.html","w");
fwrite($f,$info);
fclose($f);
exec("start phpinfo.html");

?>