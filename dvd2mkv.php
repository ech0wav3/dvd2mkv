<?php

###### Version #######
$d2m_ver = "0.4";

## Delcare global variables
$win = 1;
if ($win == 1) {
	# Start bold tag
	$sb = " [";
	# End bold tag
	$eb = " ] ";
	# Space
	$sp = " ";
	# Start title tag
	$st = "----------\n| ";
	# End title tag
	$et = " |\n----------";
} else {
	# Start bold tag
	$sb = "\033[1m";
	# End bold tag
	$eb = "\033[0m";
	# Space
	$sp = "";
	# Start title tag
	$st = "\033[1m";
	# End title tag
	$et = "\033[0m";
}

## Default Secondary Audio location
$def_saud_stream = "0x81";

# Disable file writing until all questions have been answered
$cont = 0;

## Load program location variables from outside source
if (file_exists("locations.php")) {
	include('locations.php');
} else {
	echo "The required locations.php file cannot be found. Please create it and try running this script again.";
	exit;
}

## Not my code. This is used to replace unusable characters in the final file name
function filename($filename) {
$temp = $filename;

// Lower case
$temp = strtolower($temp);

// Replace spaces with a '_'
$temp = str_replace(" ", "_", $temp);

// Loop through string
$result = '';
for ($i=0; $i<strlen($temp); $i++) {
	if (preg_match('([0-9]|[a-z]|_)', $temp[$i])) {
		$result = $result . $temp[$i];
	}
}

// Replace triple underscores with a single underscore
$result = str_replace("___", "_", $result);

// Replace double underscores with a single underscore
$result = str_replace("__", "_", $result);

// Return filename
return $result;
}

## Open user input stream
$handle = fopen ("php://stdin","r");

## Question 1
echo "How many items will be encoded from this DVD?\n[ ]: ";
$answer = fgets($handle);
if ((int)trim($answer) != 0) {
	$num_items = (int)trim($answer);
} else {
	echo "Invalid answer. Defaulting to 1.";
	echo "\n";
	$num_items = 1;
}
echo "\n";

## Question 2
echo "Are these episodes, or is this a movie?\n[$sb ep$eb /$sp mv ]: ";
$answer = fgets($handle);
if (trim($answer) == "ep" || trim($answer) == "") {
	$dvd_type = 1;
} elseif (trim($answer) == "mv") {
	$dvd_type = 0;
} else {
	echo "Invalid answer. Defaulting to episodes.";
	echo "\n";
	$dvd_type = 1;
}
echo "\n";

## Question 3
if ($dvd_type == 1) {
	echo "What is the show's title?\n[ ]: ";
} else {
	echo "What is the movie's title?\n[ ]: ";
}
$answer = fgets($handle);
if (trim($answer) != "") {
	$content_title = trim($answer);
} else {
	echo "Invalid answer. Defaulting to a generic title.";
	echo "\n";
	$content_title = "Generic Title";
}
echo "\n";

## Question 4
if ($dvd_type == 1) {
	echo "Does each episode contain chapters?\n[$sb no$eb /$sp yes ]: ";
} else {
	echo "Does the movie contain chapters?\n[$sb no$eb /$sp yes ]: ";
}
$answer = fgets($handle);
if (trim($answer) == "no" || trim($answer) == "") {
	$has_chap = 0;
} elseif (trim($answer) == "yes") {
	$has_chap = 1;
} else {
	echo "Invalid answer. Defaulting to no chapters.";
	echo "\n";
	$has_chap = 0;
}
echo "\n";

## Question 5
if ($dvd_type == 1) {
	echo "Will the episodes contain subtitles?\n[$sb yes$eb /$sp no ]: ";
} else {
	echo "Will the movie contain subtitles?\n[$sb yes$eb /$sp no ]: ";
}
$answer = fgets($handle);
if (trim($answer) == "yes" || trim($answer) == "") {
	$has_subs = 1;
} elseif (trim($answer) == "yes") {
	$has_subs = 0;
} else {
	echo "Invalid answer. Defaulting to media containing subtitles.";
	echo "\n";
	$has_subs = 1;
}
echo "\n";

## Question 6 (conditional)
if ($has_subs == 1) {
	echo "Are the subtitles from a sub-picture stream, or from closed captioning?\n[$sb cc$eb /$sp sp ]: ";
	$answer = fgets($handle);
	if (trim($answer) == "cc" || trim($answer) == "") {
		$cc_subs = 1;
	} elseif (trim($answer) == "sp") {
		$cc_subs = 0;
	} else {
		echo "Invalid answer. Defaulting to closed captioning subtitles.";
		echo "\n";
		$cc_subs = 1;
	}
	echo "\n";
}

## Question 7 (conditional)
if ($dvd_type != 0) {
	echo "What season is being encoded?\n";
	echo "(must be two digits, e.g. 01)\n[ ]: ";
	$answer = fgets($handle);
	if ((int)trim($answer) != 0 && strlen(trim($answer)) == 2) {
		$season = trim($answer);
	} else {
		echo "Invalid answer. Defaulting to 01.";
		echo "\n";
		$season = "01";
	}
	echo "\n";
}

## Question 8
echo "Was the video natively widescreen or full screen?\n[$sb fs$eb /$sp ws ]: ";
$answer = fgets($handle);
if (trim($answer) == "fs" || trim($answer) == "") {
	$org_ar = 1;
} elseif (trim($answer) == "ws") {
	$org_ar = 0;
} else {
	echo "Invalid answer. Defaulting to full screen.";
	echo "\n";
	$org_ar = 1;
}
echo "\n";

## Question 9
echo "Was the video natively progressive?\n[$sb no$eb /$sp yes ]: ";
$answer = fgets($handle);
if (trim($answer) == "no" || trim($answer) == "") {
	$ivtc = 1;
} elseif (trim($answer) == "yes") {
	$ivtc = 0;
} else {
	echo "Invalid answer. Defaulting to interlaced.";
	echo "\n";
	$ivtc = 1;
}
echo "\n";

## Question 10
echo "Is the content of the media film or animation?\n[$sb anim$eb /$sp film ]: ";
$answer = fgets($handle);
if (trim($answer) == "anim" || trim($answer) == "") {
	$ivtc_type = 1;
} elseif (trim($answer) == "film") {
	$ivtc_type = 0;
} else {
	echo "Invalid answer. Defaulting to animation.";
	echo "\n";
	$ivtc_type = 1;
}
echo "\n";

## Content questions group
$i = 1;
while ($i <= $num_items) {
	if ($num_items != 1) {
		echo $st . "Item " . $i . "$et\n";
	}
	
	# Question 11
	if ($dvd_type == 1) {
		echo "What is the episode's title?\n[ ]: ";
		$answer = fgets($handle);
		if (trim($answer) != "") {
			$episode_title[$i] = trim($answer);
		} else {
			echo "Invalid answer. Defaulting to a generic title.";
			echo "\n";
			$episode_title[$i] = "Generic Title";
		}
		echo "\n";
	}
	
	# Question 12
	if ($dvd_type == 1) {
		echo "What is the episode's number in this season?\n";
		echo "(must be two digits, e.g. 01)\n[ ]: ";
		$answer = fgets($handle);
		if ((int)trim($answer) != 0 && strlen(trim($answer)) == 2) {
			$ep_num[$i] = trim($answer);
		} else {
			echo "Invalid answer. Defaulting to 01.";
			echo "\n";
			$ep_num[$i] = "01";
		}
		echo "\n";
	}
	
	# Question 13
	echo "Which VTS on the DVD contains this item?\n[ ]: ";
	$answer = fgets($handle);
	if ((int)trim($answer) != 0) {
		$vts_num[$i] = (int)trim($answer);
	} else {
		echo "Invalid answer. Defaulting to 1.";
		echo "\n";
		$vts_num[$i] = 1;
	}
	echo "\n";
	
	# Question 14
	$j = $i - 1;
	$pgc_plus = $pgc_num[$j] + 1;
	if ($pgc_num[$j] == "") {
		$prev_pgc = 0;
		echo "Which PGC within the VTS contains this item?\n[ ]: ";
	} else {
		$prev_pgc = 1;
		echo "Which PGC within the VTS contains this item?\n[$sb " . $pgc_plus . "$eb]: ";
	}
	$answer = fgets($handle);
	if ($prev_pgc == 1) {
		if (trim($answer) == "") {
			$pgc_num[$i] = $pgc_num[$j] + 1;
		} else {
			$pgc_num[$i] = (int)trim($answer);
		}
	} else {
		if ((int)trim($answer) != 0) {
			$pgc_num[$i] = (int)trim($answer);
		} else {
			echo "Invalid answer. Defaulting to 1.";
			echo "\n";
			$pgc_num[$i] = 1;
		}
	}
	echo "\n";
	
	# Question 15
	echo "Which stream contains the video?\n[$sb 0xE0$eb]: ";
	$answer = fgets($handle);
	if (trim($answer) == "0xE0" || trim($answer) == "") {
		$stream_vid[$i] = "0xE0";
	} else {
		$stream_vid[$i] = trim($answer);
	}
	echo "\n";
	
	# Question 16
	echo "Which stream contains the primary audio?\n[$sb 0x80$eb]: ";
	$answer = fgets($handle);
	if (trim($answer) == "0x80" || trim($answer) == "") {
		$stream_paud[$i] = "0x80";
	} else {
		$stream_paud[$i] = trim($answer);
	}
	echo "\n";
	
	# Question 17
	echo "What language is the primary audio in?\n";
	echo "(must be the three character language code, e.g. eng)\n[$sb eng$eb]: ";
	$answer = fgets($handle);
	if (trim($answer) == "eng" || trim($answer) == "") {
		$paud_lang[$i] = "eng";
	} elseif (strlen(trim($answer)) == 3) {
		$aud_lang[$i] = trim($answer);
	} else {
		echo "Invalid answer. Defaulting to eng.";
		echo "\n";
		$paud_lang[$i] = "eng";
	}
	echo "\n";
	
	# Question 18
	echo "Is there another audio track to include?\n[$sb yes$eb /$sp no ]: ";
	$answer = fgets($handle);
	if (trim($answer) == "yes" || trim($answer) == "") {
		$saud[$i] = 1;
	} elseif (trim($answer) == "no") {
		$saud[$i] = 0;
	} else {
		echo "Invalid answer. Defaulting to yes.";
		echo "\n";
		$saud[$i] = 1;
	}
	echo "\n";
	
	# Question 19 (conditional)
	if ($saud[$i] != 0) {
		echo "Does this audio track contain secondary audio or director's comments?\n[$sb dc$eb /$sp sa ]: ";
		$answer = fgets($handle);
		if (trim($answer) == "dc" || trim($answer) == "") {
			$saud_type[$i] = 1;
		} elseif (trim($answer) == "sa") {
			$saud_type[$i] = 0;
		} else {
			echo "Invalid answer. Defaulting to director's comments.";
			echo "\n";
			$saud_type[$i] = 1;
		}
		echo "\n";
	}
	
	# Question 20 (conditional)
	if ($saud[$i] != 0) {
		if ($saud_type[$i] == 0) {
			echo "Which stream contains the secondary audio?\n[$sb $def_saud_stream" . $eb . "]: ";
		} else {
			echo "Which stream contains the director's comments?\n[$sb $def_saud_stream" . $eb . "]: ";
		}
		$answer = fgets($handle);
		if (trim($answer) == "") {
			$stream_saud[$i] = $def_saud_stream;
		} else {
			$stream_saud[$i] = trim($answer);
			$def_saud_stream = trim($answer);
		}
		echo "\n";
	}
	
	# Question 21 (conditional)
	if ($saud[$i] != 0) {
		if ($saud_type[$i] == 0) {
			echo "What language is the secondary audio in?\n";
			echo "(must be the three character language code, e.g. eng)\n[$sb eng$eb]: ";
		} else {
			echo "What language is the director's comments in?\n";
			echo "(must be the three character language code, e.g. eng)\n[$sb eng$eb]: ";
		}
		$answer = fgets($handle);
		if (trim($answer) == "eng" || trim($answer) == "") {
			$saud_lang[$i] = "eng";
		} elseif (strlen(trim($answer)) == 3) {
			$sud_lang[$i] = trim($answer);
		} else {
			echo "Invalid answer. Defaulting to eng.";
			echo "\n";
			$saud_lang[$i] = "eng";
		}
		echo "\n";
	}
	
	if ($i == $num_items) {
		$cont = 1;
	}
	
	$i++;
}
$i = 1;

echo "\n\n\nWriting the script files...";
sleep (2);

## Write file script file
if ($cont == 1) {
	if ($dvd_type == 1) {
		$batfile = fopen(str_replace(" ", "_", $content_title) . '_Season' . $season . '_' . $ep_num[1] . '-' . $ep_num[$num_items] . '.bat', 'w');
	} else {
		$batfile = fopen(str_replace(" ", "_", $content_title) . '.bat', 'w');
	}

	# Write the rip code to the script
	fwrite($batfile, "REM \r\n");
	fwrite($batfile, "REM Write the rip code to the script\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$vob_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\";
			fwrite($batfile, "\"" . $dd_loc . "\" /MODE IFO /SRC " . $dl_loc . " /DEST \"" . $vob_dest[$i] . "\" /VTS " . $vts_num[$i] . " /PGC " . $pgc_num[$i] . " /SPLIT NONE /START /CLOSE\r\n");
			if ($has_chap == 1) {
				fwrite($batfile, "ren " . $vob_dest[$i] . "*OGG.txt CHAPTERS.txt\r\n");
			} else {
				fwrite($batfile, "del " . $vob_dest[$i] . "*OGG.txt\r\n");
			}
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$vob_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\";
		fwrite($batfile, "\"" . $dd_loc . "\" /MODE IFO /SRC " . $dl_loc . " /DEST \"" . $vob_dest[1] . "\" /VTS " . $vts_num[1] . " /PGC " . $pgc_num[1] . " /SPLIT NONE /START /CLOSE\r\n");
		if ($has_chap == 1) {
			fwrite($batfile, "ren " . $vob_dest[1] . "*OGG.txt CHAPTERS.txt\r\n");
		} else {
			fwrite($batfile, "del " . $vob_dest[1] . "*OGG.txt\r\n");
		}
		echo ".";
	}
	
	# Write the sub-picture subtitle extraction code to the script
	if ($cc_subs == 0) {
		fwrite($batfile, "\r\nREM \r\n");
		fwrite($batfile, "REM Write the sub-picture subtitle extraction code to the script\r\n");
		fwrite($batfile, "REM \r\n");
		if ($dvd_type == 1) {
			while ($i <= $num_items) {
				if (is_dir($rd_loc . "\\RIPPED\\") == false) {
					mkdir($rd_loc . "\\RIPPED\\");
				}
				if (is_dir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\") == false) {
					mkdir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\");
				}
				if (is_dir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\") == false) {
					mkdir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\");
				}
				if (is_dir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\") == false) {
					mkdir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\");
				}
				$idx_src[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\IFO_REPLACE";
				$idx_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\MAIN";
				$prm_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\MAIN.vsparam";
				$prmfile = fopen($prm_dest[$i], 'w');
				fwrite($prmfile, $idx_src[$i] . "\r\n");
				fwrite($prmfile, $idx_dest[$i] . "\r\n");
				fwrite($prmfile, $pgc_num[$i] . "\r\n");
				fwrite($prmfile, "0\r\n");
				fwrite($prmfile, "en\r\n");
				fwrite($prmfile, "CLOSE\r\n");
				fclose($prmfile);
				fwrite($batfile, $pe_loc . " \"" . $vob_dest[$i] . "\\\" \"MAIN.vsparam\"\r\n");
				fwrite($batfile, $vu_loc . " " . $prm_dest[$i] . "\r\n");
				$i++;
				echo ".";
			}
			$i = 1;
		} else {
			if (is_dir($rd_loc . "\\RIPPED\\") == false) {
				mkdir($rd_loc . "\\RIPPED\\");
			}
			if (is_dir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\") == false) {
				mkdir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\");
			}
			$idx_src[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\IFO_REPLACE";
			$idx_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\MAIN";
			$idx_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\MAIN.vsparam";
			$prmfile = fopen($prm_dest[1], 'w');
			fwrite($prmfile, $idx_src[1] . "\r\n");
			fwrite($prmfile, $idx_dest[1] . "\r\n");
			fwrite($prmfile, $pgc_num[1] . "\r\n");
			fwrite($prmfile, "0\r\n");
			fwrite($prmfile, "en\r\n");
			fwrite($prmfile, "CLOSE\r\n");
			fclose($prmfile);
			fwrite($batfile, $pe_loc . " \"" . $vob_dest[1] . "\\\" \"MAIN.vsparam\"\r\n");
			fwrite($batfile, $vu_loc . " " . $prm_dest[1] . "\r\n");
			echo ".";
		}
	}
	
	# Rename VOBs for demuxing
	fwrite($batfile, "\r\nREM \r\n");
	fwrite($batfile, "REM Rename VOBs for demuxing\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$vob_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\";
			fwrite($batfile, "ren " . $vob_dest[$i] . "*.vob MAIN.vob\r\n");
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$vob_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\";
		fwrite($batfile, "ren " . $vob_dest[1] . "*.vob MAIN.vob\r\n");
		echo ".";
	}

	# Write the VOB demux code to the script
	fwrite($batfile, "\r\nREM \r\n");
	fwrite($batfile, "REM Write the VOB demux code to the script\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$m2v_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\MAIN.m2v";
			$paud_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\PAUD.ac3";
			fwrite($batfile, "\"" . $vs_loc . "\" \"" . $vob_dest[$i] . "MAIN.vob\" -!do\"" . $m2v_dest[$i] . "\" " . $stream_vid[$i] . "\r\n");
			fwrite($batfile, "\"" . $vs_loc . "\" \"" . $vob_dest[$i] . "MAIN.vob\" -!do\"" . $paud_dest[$i] . "\" 0xBD " . $stream_paud[$i] . "\r\n");
			if ($saud[$i] == 1) {
				$saud_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\SAUD.ac3";
				fwrite($batfile, "\"" . $vs_loc . "\" \"" . $vob_dest[$i] . "MAIN.vob\" -!do\"" . $saud_dest[$i] . "\" 0xBD " . $stream_saud[$i] . "\r\n");
			}
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$m2v_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\MAIN.m2v";
		$paud_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\PAUD.ac3";
		fwrite($batfile, "\"" . $vs_loc . "\" \"" . $vob_dest[1] . "MAIN.vob\" -!do\"" . $m2v_dest[1] . "\" " . $stream_vid[1] . "\r\n");
		fwrite($batfile, "\"" . $vs_loc . "\" \"" . $vob_dest[1] . "MAIN.vob\" -!do\"" . $paud_dest[1] . "\" " . $stream_paud[1] . "\r\n");
		if ($saud[1] == 1) {
			$saud_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SAUD.ac3";
			fwrite($batfile, "\"" . $vs_loc . "\" \"" . $vob_dest[1] . "MAIN.vob\" -!do\"" . $saud_dest[1] . "\" " . $stream_saud[1] . "\r\n");
		}
		echo ".";
	}

	# Write the closed captioning subtitle extraction code to the script
	if ($cc_subs == 1) {
		fwrite($batfile, "\r\nREM \r\n");
		fwrite($batfile, "REM Write the closed captioning subtitle extraction code to the script\r\n");
		fwrite($batfile, "REM \r\n");
		if ($dvd_type == 1) {
			while ($i <= $num_items) {
				$srt_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\MAIN.srt";
				fwrite($batfile, "\"" . $cc_loc . "\" -ps -utf8 -trim -out=srt \"" . $vob_dest[$i] . "MAIN.vob\" -o \"" . $srt_dest[$i] . "\"\r\n");
				$i++;
				echo ".";
			}
			$i = 1;
		} else {
			$srt_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\MAIN.srt";
			fwrite($batfile, "\"" . $cc_loc . "\" -ps -utf8 -trim -out=srt \"" . $vob_dest[1] . "MAIN.vob\" -o \"" . $srt_dest[1] . "\"\r\n");
			echo ".";
		}
	}
	
	# Write the code to correct the chapters to the script
	if ($has_chap == 1) {
		fwrite($batfile, "\r\nREM \r\n");
		fwrite($batfile, "REM Write the code to correct the chapters to the script\r\n");
		fwrite($batfile, "REM \r\n");
		if ($dvd_type == 1) {
			while ($i <= $num_items) {
				$chap_file[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\CHAPTERS.txt";
				fwrite($batfile, "\"" . $ce_loc . "\" \"" . $chap_file[$i] . "\"\r\n");
				$i++;
				echo ".";
			}
			$i = 1;
		} else {
			$chap_file[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\CHAPTERS.txt";
			fwrite($batfile, "\"" . $ce_loc . "\" \"" . $chap_file[1] . "\"\r\n");
			echo ".";
		}
	}

	# Write the d2v compilation code to the script
	fwrite($batfile, "\r\nREM \r\n");
	fwrite($batfile, "REM Write the d2v compilation code to the script\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$d2v_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\MAIN";
			fwrite($batfile, "\"" . $dg_loc . "\" -i \"" . $m2v_dest[$i] . "\" -o \"" . $d2v_dest[$i] . "\" -ia 5 -fo 0 -yr 1 -om 0 -exit\r\n");
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$d2v_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\MAIN";
		fwrite($batfile, "\"" . $dg_loc . "\" -i \"" . $m2v_dest[1] . "\" -o \"" . $d2v_dest[1] . "\" -ia 5 -fo 0 -yr 1 -om 0 -exit\r\n");
		echo ".";
	}

	# Write the AviSynth script file
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			if (is_dir($rd_loc . "\\RIPPED\\") == false) {
				mkdir($rd_loc . "\\RIPPED\\");
			}
			if (is_dir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\") == false) {
				mkdir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\");
			}
			if (is_dir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\") == false) {
				mkdir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\");
			}
			if (is_dir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\") == false) {
				mkdir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\");
			}
			$avs_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\MAIN.avs";
			$avsfile = fopen($avs_dest[$i], 'w');
			fwrite($avsfile, "LoadPlugin(\"" . $gd_loc . "\")" . "\r\n");
			fwrite($avsfile, "MPEG2Source(\"" . $d2v_dest[$i] . ".d2v\")" . "\r\n");
			if ($ivtc == 1) {
				fwrite($avsfile, "tfm(d2v=\"" . $d2v_dest[$i] . ".d2v\")" . "\r\n");
				if ($ivtc_type == 1) {
					fwrite($avsfile, "tdecimate(mode=1)" . "\r\n");
				} else {
					fwrite($avsfile, "tdecimate()" . "\r\n");
				}
			}
			if ($org_ar == 1) {
				fwrite($avsfile, "BicubicResize(640,480,0,0.5)" . "\r\n");
				fwrite($avsfile, "Crop(0,60,0,-60)" . "\r\n");
			} else {
				fwrite($avsfile, "BicubicResize(640,360,0,0.5)" . "\r\n");
			}
			fclose($avsfile);
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		if (is_dir($rd_loc . "\\RIPPED\\") == false) {
			mkdir($rd_loc . "\\RIPPED\\");
		}
		if (is_dir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\") == false) {
			mkdir($rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\");
		}
		$avs_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\MAIN.avs";
		$avsfile = fopen($avs_dest[1], 'w');
		fwrite($avsfile, "LoadPlugin(\"" . $gd_loc . "\")" . "\r\n");
		fwrite($avsfile, "MPEG2Source(\"" . $d2v_dest[1] . ".d2v\")" . "\r\n");
		if ($ivtc == 1) {
			fwrite($avsfile, "tfm(d2v=\"" . $d2v_dest[1] . ".d2v\")" . "\r\n");
			if ($ivtc_type == 1) {
				fwrite($avsfile, "tdecimate(mode=1)" . "\r\n");
			} else {
				fwrite($avsfile, "tdecimate()" . "\r\n");
			}
		}
		if ($org_ar == 1) {
			fwrite($avsfile, "BicubicResize(640,480,0,0.5)" . "\r\n");
			fwrite($avsfile, "Crop(0,60,0,-60)" . "\r\n");
		} else {
			fwrite($avsfile, "BicubicResize(640,360,0,0.5)" . "\r\n");
		}
		fclose($avsfile);
		echo ".";
	}

	# Write the commands to encode the video to the script
	fwrite($batfile, "\r\nREM \r\n");
	fwrite($batfile, "REM Write the commands to encode the video to the script\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$stat_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\MAIN.stats";
			$pass_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\MAIN.pass";
			$avi_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\MAIN.avi";
			fwrite($batfile, "\"" . $xv_loc . "\" -i \"" . $avs_dest[$i] . "\" -type 2 -o \"" . $pass_dest[$i] . "\" -pass1 \"" . $stat_dest[$i] . "\" -framerate 23.976 -progress 24\r\n");
			fwrite($batfile, "\"" . $xv_loc . "\" -i \"" . $avs_dest[$i] . "\" -type 2 -o \"" . $avi_dest[$i] . "\" -pass2 \"" . $stat_dest[$i] . "\" -framerate 23.976 -bitrate 2109 -quality 6 -vhqmode 1 -bvhq -qtype 0 -imin 1 -imax 31 -bmin 1 -bmax 31 -pmin 1 -pmax 31 -par 1 -progress 24\r\n");
			fwrite($batfile, "del " . $pass_dest[$i] . "\r\n");
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$stat_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\MAIN.stats";
		$pass_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\MAIN.pass";
		$avi_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\MAIN.avi";
		fwrite($batfile, "\"" . $xv_loc . "\" -i \"" . $avs_dest[1] . "\" -type 2 -o \"" . $pass_dest[1] . "\" -pass1 \"" . $stat_dest[1] . "\" -framerate 23.976 -progress 24\r\n");
		fwrite($batfile, "\"" . $xv_loc . "\" -i \"" . $avs_dest[1] . "\" -type 2 -o \"" . $avi_dest[1] . "\" -pass2 \"" . $stat_dest[1] . "\" -framerate 23.976 -bitrate 2109 -quality 6 -vhqmode 1 -bvhq -qtype 0 -imin 1 -imax 31 -bmin 1 -bmax 31 -pmin 1 -pmax 31 -par 1 -progress 24\r\n");
		fwrite($batfile, "del " . $pass_dest[1] . "\r\n");
		echo ".";
	}
	
	# Write version ID files
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$ver_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\COMPILED_USING_DVD2MKV_" . $d2m_ver;
			$verfile = fopen($ver_dest[$i], 'w');
			fwrite($verfile, $content_title . ": Season " . $season . ", Episode " . $ep_num[$i] . "\r\n");
			fwrite($verfile, "\"" . $episode_title[$i] . "\"\r\n");
			fwrite($verfile, "Compiled using dvd2mkv version " . $d2m_ver . "\r\n");
			fclose($verfile);
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$ver_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\COMPILED_USING_DVD2MKV_" . $d2m_ver;
		$verfile = fopen($ver_dest[1], 'w');
		fwrite($verfile, $content_title . "\r\n");
		fwrite($verfile, "Compiled using dvd2mkv version " . $d2m_ver . "\r\n");
		fclose($verfile);
		echo ".";
	}

	# Write the commands to the script to create the mkv file
	fwrite($batfile, "\r\nREM \r\n");
	fwrite($batfile, "REM Write the commands to the script to create the mkv file\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$mkv_dest[$i] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\SEASON" . $season . "\\EPISODE" . $ep_num[$i] . "\\" . $ep_num[$i] . "-" . filename($episode_title[$i]) . ".mkv\"";
			$vid_opt = "  \"--track-name\" \"0:Video\" \"--default-track\" \"0:yes\" \"--forced-track\" \"0:no\" \"--aspect-ratio\" \"0:16/9\" \"--fourcc\" \"0:XVID\" \"--default-duration\" \"0:24000/1001fps\" \"-d\" \"0\" \"-A\" \"-S\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $avi_dest[$i] . "\"";
			$paud_opt = " \"--language\" \"0:" . $paud_lang[$i] . "\" \"--sync\" \"0:0\" \"--track-name\" \"0:Primary Audio\" \"--default-track\" \"0:yes\" \"--forced-track\" \"0:no\" \"-a\" \"0\" \"-D\" \"-S\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $paud_dest[$i] . "\"";
			if ($saud[$i] == 1) {
				if ($saud_type[$i] == 1) {
					$saud_opt = " \"--language\" \"0:" . $saud_lang[$i] . "\" \"--sync\" \"0:0\" \"--track-name\" \"0:Director's Comments\" \"--forced-track\" \"0:no\" \"-a\" \"0\" \"-D\" \"-S\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $paud_dest[$i] . "\"";
				} else {
					$saud_opt = " \"--language\" \"0:" . $saud_lang[$i] . "\" \"--sync\" \"0:0\" \"--track-name\" \"0:Secondary Audio\" \"--forced-track\" \"0:no\" \"-a\" \"0\" \"-D\" \"-S\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $paud_dest[$i] . "\"";
				}
			} else {
				$saud_opt = "";
			}
			if ($has_chap == 1) {
				$chap_opt = " \"--chapter-language\" \"und\" \"--chapters\" \"" . $vob_dest[$i] . "CHAPTERS.txt\"";
			} else {
				$chap_opt = "";
			}
			if ($has_subs == 1) {
				if ($cc_subs == 1) {
					$sub_opt = " \"--sub-charset\" \"0:UTF-8\" \"--language\" \"0:eng\" \"--track-name\" \"0:Subtitles\" \"--forced-track\" \"0:no\" \"-s\" \"0\" \"-D\" \"-A\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $srt_dest[$i] . "\"";
				} else {
					$sub_opt = " \"--language\" \"0:eng\" \"--track-name\" \"0:Subtitles\" \"--forced-track\" \"0:no\" \"-s\" \"0\" \"-D\" \"-A\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $idx_dest[$i] . ".idx\"";
				}
			} else {
				$sub_opt = "";
			}
			$gen_opt = " \"--track-order\" \"0:0,1:0,2:0,3:0\" \"--attachment-mime-type\" \"text/plain\" \"--attachment-description\" \"COMPILED_USING_DVD2MKV_" . $d2m_ver . "\" \"--attachment-name\" \"COMPILED_USING_DVD2MKV_" . $d2m_ver . "\" \"--attach-file\" \"" . $ver_dest[$i] . "\" \"--title\" \"" . $episode_title[$i] . "\"";
			fwrite($batfile, "\"" . $mk_loc . "\" -o \"" . $mkv_dest[$i] . $vid_opt . $paud_opt . $saud_opt . $sub_opt . $gen_opt . $chap_opt . "\r\n");
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$mkv_dest[1] = $rd_loc . "\\RIPPED\\" . str_replace(" ", "_", $content_title) . "\\" . filename($content_title) . ".mkv\"";
		$vid_opt = "  \"--track-name\" \"0:Video\" \"--default-track\" \"0:yes\" \"--forced-track\" \"0:no\" \"--aspect-ratio\" \"0:16/9\" \"--fourcc\" \"0:XVID\" \"--default-duration\" \"0:24000/1001fps\" \"-d\" \"0\" \"-A\" \"-S\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $avi_dest[1] . "\"";
		$paud_opt = " \"--language\" \"0:" . $paud_lang[1] . "\" \"--sync\" \"0:0\" \"--track-name\" \"0:Primary Audio\" \"--default-track\" \"0:yes\" \"--forced-track\" \"0:no\" \"-a\" \"0\" \"-D\" \"-S\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $paud_dest[1] . "\"";
		if ($saud[1] == 1) {
			if ($saud_type[1] == 1) {
				$saud_opt = " \"--language\" \"0:" . $saud_lang[1] . "\" \"--sync\" \"0:0\" \"--track-name\" \"0:Director's Comments\" \"--forced-track\" \"0:no\" \"-a\" \"0\" \"-D\" \"-S\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $paud_dest[1] . "\"";
			} else {
				$saud_opt = " \"--language\" \"0:" . $saud_lang[1] . "\" \"--sync\" \"0:0\" \"--track-name\" \"0:Secondary Audio\" \"--forced-track\" \"0:no\" \"-a\" \"0\" \"-D\" \"-S\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $paud_dest[1] . "\"";
			}
		} else {
			$saud_opt = "";
		}
		if ($has_chap == 1) {
			$chap_opt = " \"--chapter-language\" \"und\" \"--chapters\" \"" . $vob_dest[1] . "CHAPTERS.txt\"";
		} else {
			$chap_opt = "";
		}
		if ($has_subs == 1) {
			if ($cc_subs == 1) {
				$sub_opt = " \"--sub-charset\" \"0:UTF-8\" \"--language\" \"0:eng\" \"--track-name\" \"0:Subtitles\" \"--forced-track\" \"0:no\" \"-s\" \"0\" \"-D\" \"-A\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $srt_dest[1] . "\"";
			} else {
				$sub_opt = " \"--language\" \"0:eng\" \"--track-name\" \"0:Subtitles\" \"--forced-track\" \"0:no\" \"-s\" \"0\" \"-D\" \"-A\" \"-T\" \"--no-global-tags\" \"--no-chapters\" \"" . $idx_dest[1] . ".idx\"";
			}
		} else {
			$sub_opt = "";
		}
		$gen_opt = " \"--track-order\" \"0:0,1:0,2:0,3:0\" \"--attachment-mime-type\" \"text/plain\" \"--attachment-description\" \"COMPILED_WITH_DVD2MKV_" . $d2m_ver . "\" \"--attachment-name\" \"COMPILED_WITH_DVD2MKV_" . $d2m_ver . "\" \"--attach-file\" \"" . $ver_dest[1] . "\" \"--title\" \"" . $content_title . "\"";
		fwrite($batfile, "\"" . $mk_loc . "\" -o \"" . $mkv_dest[1] . $vid_opt . $paud_opt . $saud_opt . $sub_opt . $gen_opt . $chap_opt . "\r\n");
		echo ".";
	}
}

echo " Done!";
sleep (5);


## Close streams and shutdown script
fclose($handle);
fclose($batfile);
exit;

?>