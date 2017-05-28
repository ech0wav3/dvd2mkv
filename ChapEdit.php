<?php

$chapfile = file($argv[1]);
$outfile = fopen($argv[1], 'w');

$line_total = count($chapfile);

foreach($chapfile as $linenum => $line) {
	if ($linenum != ($line_total - 2) && $linenum != ($line_total - 1) && $linenum != ($line_total)) {
		$output = $line;
		fwrite($outfile, $output);
	}
}

fclose($outfile);
exit;

?>