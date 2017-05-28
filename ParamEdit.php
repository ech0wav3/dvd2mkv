<?php

$parmfile = file($argv[1] . $argv[2]);
$outfile = fopen($argv[1] . $argv[2], 'w');

foreach($parmfile as $line) {
	if (substr($line, -13) == "IFO_REPLACE\r\n") {
		exec("cmd /c dir /b \"" . $argv[1] . "*.ifo\"", $ifo_file);
		$output = str_replace("IFO_REPLACE", $ifo_file[0], $line);
		fwrite($outfile, $output);
	} else {
		$output = $line;
		fwrite($outfile, $output);
	}
}

fclose($outfile);
exit;

?>