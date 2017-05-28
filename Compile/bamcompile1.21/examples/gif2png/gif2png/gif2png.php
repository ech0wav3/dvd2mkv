<?

print "\nGIF2PNG - Convert gif files to png\n\n";

if($argc==1)
{
	print "Usage: gif2png infile.gif [outfile.png]\n";
	exit;
}

$infile = "";
$outfile = "";

while(list($nr,$val)=each($argv))
{
	if($nr>0)
	{
		$val = strtolower($val);
		if(strpos($val,'.gif')>-1)$infile = $val;
		else if(strpos($val,'.png')>-1)$outfile = $val;
	}
}

if($infile == "")
{
	print "You must specify a gif file to convert!\n";
	exit;
}

if($outfile == "")$outfile = str_replace('.gif','.png',$infile);

$img = imagecreatefromgif($infile);
imagepng($img,$outfile);
print "$infile converted to $outfile!\n";

?>