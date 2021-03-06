<?php

###### Version #######
$d2m_ver = "2020.05";

## Load program location variables from outside source
if (file_exists("settings.conf")) {
	include('settings.conf');
} else {
	echo "The required settings.conf file cannot be found. Please create it and try running this script again.";
	exit;
}

## Declare global variables
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

# Disable file writing until all questions have been answered
$cont = 0;

## Function to edit the subtitle parameters
function ParamEdit($filefolder, $filename) {
	$parmfile = file($filefolder . $filename);
	$param_outfile = fopen($filefolder . $filename, 'w');

	foreach($parmfile as $line) {
		if (substr($line, -13) == "IFO_REPLACE\r\n") {
			exec("cmd /c dir /b \"" . $filefolder . "*.ifo\"", $ifo_file);
			echo $ifo_file . "\r\n";
			$output = str_replace("IFO_REPLACE", $ifo_file[0], $line);
			echo $output . "\r\n";
			fwrite($param_outfile, $output);
		} else {
			$output = $line;
			echo $output . "\r\n";
			fwrite($param_outfile, $output);
		}
	}

	fclose($param_outfile);
}

## Function to edit the chapter markers
function ChapterEdit($filefolder, $filename, $chapters) {
	$chapterfile = file($filefolder . $filename);
	$chapter_outfile = fopen($filefolder . $filename, 'w');
	$chapters_keep = explode(" ", $chapters);
	$x = 0;
	
	foreach($chapters_keep as $chapter) {
		$line_num = $chapter * 2 - 1;
		fwrite($chapter_outfile, $chapterfile[$line_num-1]);
		fwrite($chapter_outfile, $chapterfile[$line_num]);
	}

	fclose($chapter_outfile);
}

function SanitizeName($strToUse) {
	$temp = $strToUse;

	// Replace spaces with a '_'
	$temp = str_replace(" ", "_", $temp);
	$temp = str_replace(".", "_", $temp);
	$temp = str_replace(":", "_", $temp);
	$result = $temp;
	
	// Replace triple underscores with a single underscore
	$result = str_replace("___", "_", $result);

	// Replace double underscores with a single underscore
	$result = str_replace("__", "_", $result);
	
	// Return filename
	return $result;
}

function ScriptSanitize($strTitle) {
	$temp = $strTitle;

	$temp = str_replace("&", "^&", $temp);
	$result = $temp;
	
	// Return filename
	return $result;
}

## Not my code. This is used to replace unusable characters in the final file name
function filename($filename) {
	$temp = $filename;

	// Lower case
	$temp = strtolower($temp);

	// Replace spaces with a '_'
	$temp = str_replace(" ", "_", $temp);
	$temp = str_replace(".", "_", $temp);
	$temp = str_replace(":", "_", $temp);
	$temp = str_replace("&", "and", $temp);

	// Loop through string
	$result = '';
	for ($i=0; $i<strlen($temp); $i++) {
		if (preg_match('([0-9]|[a-z]|_|-)', $temp[$i])) {
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

if ($argv[1] == "--param") {
	ParamEdit($argv[2], $argv[3]);
	exit;
} elseif ($argv[1] == "--chapteredit") {
	ChapterEdit($argv[2], $argv[3], $argv[4]);
	exit;
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
	echo "Will the episodes contain subtitles?\n[$sb yes$eb /$sp no ]: ";
} else {
	echo "Will the movie contain subtitles?\n[$sb yes$eb /$sp no ]: ";
}
$answer = fgets($handle);
if (trim($answer) == "yes" || trim($answer) == "") {
	$has_subs = 1;
} elseif (trim($answer) == "no") {
	$has_subs = 0;
} else {
	echo "Invalid answer. Defaulting to media containing subtitles.";
	echo "\n";
	$has_subs = 1;
}
echo "\n";

## Question 6 (conditional)
if ($dvd_type == 1) {
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

## Question 7
echo "Was the video natively widescreen or full screen?\n[$sb fs$eb /$sp ws ]: ";
$answer = fgets($handle);
if (trim($answer) == "fs" || trim($answer) == "") {
	$aspect_ratio = 1;
} elseif (trim($answer) == "ws") {
	$aspect_ratio = 0;
} else {
	echo "Invalid answer. Defaulting to full screen.";
	echo "\n";
	$aspect_ratio = 1;
}
echo "\n";

## Question 9
echo "Should the content be encoded as film or animation?\n[$sb anim$eb /$sp film ]: ";
$answer = fgets($handle);
if (trim($answer) == "anim" || trim($answer) == "") {
	$content_type = 1;
} elseif (trim($answer) == "film") {
	$content_type = 0;
} else {
	echo "Invalid answer. Defaulting to animation.";
	echo "\n";
	$content_type = 1;
}
echo "\n";

## Question 10
echo "What type of post processing should be performed?\n[$sb ivtc$eb /" . $sp . "deint / both / none ]: ";
$answer = fgets($handle);
if (trim($answer) == "ivtc" || trim($answer) == "") {
	$post_proc = 1;
} elseif (trim($answer) == "deint") {
	$post_proc = 2;
} elseif (trim($answer) == "both") {
	$post_proc = 3;
} elseif (trim($answer) == "none") {
	$post_proc = 0;
} else {
	echo "Invalid answer. Defaulting to IVTC only.";
	echo "\n";
	$post_proc = 1;
}
echo "\n";

## Question 11
echo "Should the content be cropped along the vertical edges?\n[$sb no$eb /$sp yes ]: ";
$answer = fgets($handle);
if (trim($answer) == "no" || trim($answer) == "") {
	$crop_content = 0;
} elseif (trim($answer) == "yes") {
	$crop_content = 1;
} else {
	echo "Invalid answer. Defaulting to no cropping.";
	echo "\n";
	$crop_content = 0;
}
echo "\n";

## Content questions group
$i = 1;
while ($i <= $num_items) {
	if ($num_items != 1) {
		echo $st . "Item " . $i . "$et\n";
	}
	
	# Question 12
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
	
	# Question 13
	if ($dvd_type == 1) {
		$prev_ep_number = $episode_number[$i-1];
		if ($prev_ep_number != "") {
			if (((int)$prev_ep_number + 1) < 10) {
				(string)$next_ep_number = "0" . ($prev_ep_number + 1);
			} else {
				$next_ep_number = (int)$prev_ep_number + 1;
			}
		}
			
		echo "What is the episode's number in this season?\n";
		if ($prev_ep_number == "") {
			echo "(must be two digits, e.g. 01)\n[ ]: ";
		} else {
			echo "(must be two digits, e.g. 01)\n[$sb " . $next_ep_number . "$eb]: ";
		}
		$answer = fgets($handle);
		if ($prev_ep_number != "" && trim($answer) == "") {
			$episode_number[$i] = $next_ep_number;
		} elseif ($prev_ep_number == "") {
			if ((int)trim($answer) != 0 && strlen(trim($answer)) == 2) {
				$episode_number[$i] = trim($answer);
			} else {
				echo "Invalid answer. Defaulting to 01.";
				echo "\n";
				$episode_number[$i] = "01";
			}
		} else {
			echo "Invalid answer. Defaulting to 01.";
			echo "\n";
			$episode_number[$i] = "01";
		}
		echo "\n";
	}
	
	$hist_1 = $i - 1;
	$hist_2 = $i - 2;
	
	# Question 14
	if ($vts_id[$hist_1] != "" && $vts_id[$hist_2] != "") {
		if ($vts_id[$hist_1] == ($vts_id[$hist_2] + 1)) {
			$prev_vts = 1;
			$next_vts = $vts_id[$hist_1] + 1;
		} elseif ($vts_id[$hist_1] == $vts_id[$hist_2]) {
			$prev_vts = 1;
			$next_vts = $vts_id[$hist_1];
		}
	} elseif ($vts_id[$hist_1] != "" && $vts_id[$hist_2] == "") {
		$prev_vts = 1;
		$next_vts = $vts_id[$hist_1] + 1;
	} else {
		$prev_vts = 0;
	}
	
	if ($prev_vts == 0) {
		echo "Which VTS on the DVD contains this item?\n[ ]: ";
	} else {
		echo "Which VTS on the DVD contains this item?\n[$sb " . $next_vts . "$eb]: ";
	}
	$answer = fgets($handle);
	if ($prev_vts == 1) {
		if (trim($answer) == "") {
			$vts_id[$i] = $next_vts;
		} else {
			$vts_id[$i] = (int)trim($answer);
		}
	} else {
		if ((int)trim($answer) != 0) {
			$vts_id[$i] = (int)trim($answer);
		} else {
			echo "Invalid answer. Defaulting to 1.";
			echo "\n";
			$vts_id[$i] = 1;
		}
	}
	echo "\n";
	
	# Question 15
	if ($pgc_id[$hist_1] != "" && $pgc_id[$hist_2] != "") {
		if ($pgc_id[$hist_1] == ($pgc_id[$hist_2] + 1)) {
			$prev_pgc = 1;
			$next_pgc = $pgc_id[$hist_1] + 1;
		} elseif ($pgc_id[$hist_1] == $pgc_id[$hist_2]) {
			$prev_pgc = 1;
			$next_pgc = $pgc_id[$hist_1];
		}
	} elseif ($pgc_id[$hist_1] != "" && $pgc_id[$hist_2] == "") {
		$prev_pgc = 1;
		$next_pgc = $pgc_id[$hist_1] + 1;
	} else {
		$prev_pgc = 0;
	}
	
	if ($prev_pgc == 0) {
		echo "Which PGC within the VTS contains this item?\n[ ]: ";
	} else {
		echo "Which PGC within the VTS contains this item?\n[$sb " . $next_pgc . "$eb]: ";
	}
	$answer = fgets($handle);
	if ($prev_pgc == 1) {
		if (trim($answer) == "") {
			$pgc_id[$i] = $next_pgc;
		} else {
			$pgc_id[$i] = (int)trim($answer);
		}
	} else {
		if ((int)trim($answer) != 0) {
			$pgc_id[$i] = (int)trim($answer);
		} else {
			echo "Invalid answer. Defaulting to 1.";
			echo "\n";
			$pgc_id[$i] = 1;
		}
	}
	echo "\n";
	
	# Question 16
	$next_chapter_list = $chapter_list[$i-1];
	if ($next_chapter_list != "") {
		echo "Which chapters should be used for this item?\n";
		echo "(space-delimited list of numbers or * for all, e.g. 1 2 3 or *)\n[$sb $next_chapter_list" . $eb . "]: ";
	} else {
		echo "Which chapters should be used for this item?\n";
		echo "(space-delimited list of numbers or * for all, e.g. 1 2 3 or *)\n[$sb *$eb]: ";
	}
	$answer = fgets($handle);
	if (trim($answer) == "" && $next_chapter_list != "") {
		$chapter_list[$i] = $next_chapter_list;
	} elseif (trim($answer) == "" && $next_chapter_list != "") {
		$chapter_list[$i] = "*";
	} elseif (trim($answer) != "") {
		$chapter_list[$i] = trim($answer);
	} else {
		echo "Invalid answer. Defaulting to all chapters in the title.";
		echo "\n";
		$chapter_list[$i] = "*";
	}
	echo "\n";
	
	# Question 17
	echo "How many audio tracks will be included?\n[ ]: ";
	$answer = fgets($handle);
	if (trim($answer) != "") {
		$audio_quantity[$i] = trim($answer);
	} else {
		echo "Invalid answer. Defaulting to 1 audio track.";
		echo "\n";
		$audio_quantity[$i] = 1;
	}
	echo "\n";
	
	# Question 18
	echo "Which stream contains the video?\n[$sb 0xE0$eb]: ";
	$answer = fgets($handle);
	if (trim($answer) == "0xE0" || trim($answer) == "") {
		$video_stream[$i] = "0xE0";
	} else {
		$video_stream[$i] = trim($answer);
	}
	echo "\n";
	
	# Question 19
	$h = 0;
	echo "Which stream contains the primary audio?\n[$sb 0x80$eb]: ";
	$answer = fgets($handle);
	if (trim($answer) == "0x80" || trim($answer) == "") {
		$audio_stream[$i][$h] = "0x80";
		$audio_type[$i][$h] = 2;
	} else {
		$audio_stream[$i][$h] = trim($answer);
		$audio_type[$i][$h] = 2;
	}
	echo "\n";
	
	# Question 20
	echo "What language is the primary audio in?\n";
	echo "(must be the three character language code, e.g. eng)\n[$sb eng$eb]: ";
	$answer = fgets($handle);
	if (trim($answer) == "eng" || trim($answer) == "") {
		$audio_language[$i][$h] = "eng";
	} elseif (strlen(trim($answer)) == 3) {
		$audio_language[$i][$h] = trim($answer);
	} else {
		echo "Invalid answer. Defaulting to eng.";
		echo "\n";
		$audio_language[$i][$h] = "eng";
	}
	echo "\n";
	
	# Question 21
	$next_pri_audio_title = $audio_title[$i-1][$h];
	if ($next_pri_audio_title != "") {
		echo "What should the title of the primary audio track be?\n[$sb $next_pri_audio_title" . $eb . "]: ";
	} else {
		echo "What should the title of the primary audio track be?\n[ ]: ";
	}
	$answer = fgets($handle);
	if (trim($answer) == "" && $next_pri_audio_title != "") {
		$audio_title[$i][$h] = $next_pri_audio_title;
	} elseif (trim($answer) != "") {
		$audio_title[$i][$h] = trim($answer);
	} else {
		echo "Invalid answer. Defaulting to a generic title.";
		echo "\n";
		$audio_title[$i][$h] = "Primary Audio";
	}
	echo "\n";
	
	$x = 1;
	if ($audio_quantity[$i] != 1) {
		while ($x <= ($audio_quantity[$i] - 1)) {
			echo $st . "Additional Audio " . $x . "$et\n";
	
			# Question 22 (conditional)
			echo "Does this audio track contain additional audio or director's comments?\n[$sb dc$eb /$sp aa ]: ";
			$answer = fgets($handle);
			if (trim($answer) == "dc" || trim($answer) == "") {
				$additional_audio_type[$i][$x] = 1;
			} elseif (trim($answer) == "aa") {
				$additional_audio_type[$i][$x] = 0;
			} else {
				echo "Invalid answer. Defaulting to director's comments.";
				echo "\n";
				$audio_type[$i][$x] = 1;
			}
			echo "\n";
		
			# Question 23 (conditional)
			if ($audio_stream[$i-1][$x] == "") {
				$next_additional_audio_stream = $default_additional_audio_stream;
			} elseif ($audio_stream[$i-1][$x] != "" && $audio_stream[$i-1][$x] != $default_additional_audio_stream) {
				$next_additional_audio_stream = $audio_stream[$i-1][$x];
			} else {
				$next_additional_audio_stream = $default_additional_audio_stream;
			}
			if ($additional_audio_type[$i][$x] == 0) {
				echo "Which stream contains the additional audio?\n[$sb $next_additional_audio_stream" . $eb . "]: ";
			} else {
				echo "Which stream contains the director's comments?\n[$sb $next_additional_audio_stream" . $eb . "]: ";
			}
			$answer = fgets($handle);
			if (trim($answer) == "") {
				$audio_stream[$i][$x] = $next_additional_audio_stream;
			} else {
				$audio_stream[$i][$x] = trim($answer);
				#$default_additional_audio_stream = trim($answer);
			}
			echo "\n";
		
			# Question 24 (conditional)
			if ($additional_audio_type[$i][$x] == 0) {
				echo "What language is the additional audio in?\n";
				echo "(must be the three character language code, e.g. eng)\n[$sb eng$eb]: ";
			} else {
				echo "What language are the director's comments in?\n";
				echo "(must be the three character language code, e.g. eng)\n[$sb eng$eb]: ";
			}
			$answer = fgets($handle);
			if (trim($answer) == "eng" || trim($answer) == "") {
				$audio_language[$i][$x] = "eng";
			} elseif (strlen(trim($answer)) == 3) {
				$audio_language[$i][$x] = trim($answer);
			} else {
				echo "Invalid answer. Defaulting to eng.";
				echo "\n";
				$audio_language[$i][$x] = "eng";
			}
			echo "\n";
		
			# Question 25 (conditional)
			$next_sec_audio_title = $audio_title[$i-1][$x];
			if ($additional_audio_type[$i][$x] == 0) {
				if ($next_sec_audio_title != "") {
					echo "What should the title of this additional audio track be?\n[$sb $next_sec_audio_title" . $eb . "]: ";
				} else {
					echo "What should the title of this additional audio track be?\n[ ]: ";
				}
				$answer = fgets($handle);
			} else {
				$answer = "Commentary";
			}
			if (trim($answer) == "" && $next_sec_audio_title != "") {
				$audio_title[$i][$x] = $next_sec_audio_title;
			} elseif (trim($answer) != "") {
				$audio_title[$i][$x] = trim($answer);
			} else {
				echo "Invalid answer. Defaulting to a generic title.";
				echo "\n";
				$audio_title[$i][$x] = "Additional Audio";
			}
			echo "\n";
		
			if ($x == $aaud_quan[$i]) {
				$cont_aud = 1;
				$cont = 0;
			}
		
			$x++;
		}
	}
	
#	$default_additional_audio_stream = "0x81";
	
	if ($i == $num_items) {
		$complete = 1;
	}
	
	$i++;
}
$i = 1;

echo "\n\n\nWriting the script files...";
sleep (2);

## Write file script file
if ($complete == 1) {
	if ($dvd_type == 1) {
		$batfile = fopen(SanitizeName($content_title) . '_Season' . $season . '_' . $episode_number[1] . '-' . $episode_number[$num_items] . '.bat', 'w');
	} else {
		$batfile = fopen(SanitizeName($content_title) . '.bat', 'w');
	}
	
	# Create folder structure
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			if (is_dir($rd_loc . "\\RIPPED\\") == false) {
				mkdir($rd_loc . "\\RIPPED\\");
			}
			if (is_dir($rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\") == false) {
				mkdir($rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\");
			}
			if (is_dir($rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\") == false) {
				mkdir($rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\");
			}
			if (is_dir($rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\") == false) {
				mkdir($rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\");
			}
			$i++;
		}
		$i = 1;
	} else {
		if (is_dir($rd_loc . "\\RIPPED\\") == false) {
			mkdir($rd_loc . "\\RIPPED\\");
		}
		if (is_dir($rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\") == false) {
			mkdir($rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\");
		}
	}

	# Write the rip code to the script
	fwrite($batfile, "@ECHO OFF\r\n");
	fwrite($batfile, "REM \r\n");
	fwrite($batfile, "REM Write the rip code to the script\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$vob_destination[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\";
			fwrite($batfile, "ECHO Ripping episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
			if ($chapter_list[$i] != "*" ) {
				fwrite($batfile, "\"" . $dd_loc . "\" /MODE IFO /SRC " . $dl_loc . " /DEST \"" . $vob_destination[$i] . "\" /VTS " . $vts_id[$i] . " /PGC " . $pgc_id[$i] . " /SPLIT NONE /CHAPTERS " . $chapter_list[$i] . " /START /CLOSE\r\n");
			} else {
				fwrite($batfile, "\"" . $dd_loc . "\" /MODE IFO /SRC " . $dl_loc . " /DEST \"" . $vob_destination[$i] . "\" /VTS " . $vts_id[$i] . " /PGC " . $pgc_id[$i] . " /SPLIT NONE /START /CLOSE\r\n");
			}
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$vob_destination[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\";
		fwrite($batfile, "ECHO Ripping " . ScriptSanitize($content_title) . "...\r\n");
		fwrite($batfile, "\"" . $dd_loc . "\" /MODE IFO /SRC " . $dl_loc . " /DEST \"" . $vob_destination[1] . "\" /VTS " . $vts_id[1] . " /PGC " . $pgc_id[1] . " /SPLIT NONE /START /CLOSE\r\n");
		echo ".";
	}

	# Write the sub-picture subtitle extraction code to the script
	if ($has_subs == 1) {
		fwrite($batfile, "\r\nECHO.\r\n");
		fwrite($batfile, "REM \r\n");
		fwrite($batfile, "REM Write the subpicture extraction code to the script\r\n");
		fwrite($batfile, "REM \r\n");
		if ($dvd_type == 1) {
			while ($i <= $num_items) {
				$idx_source[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\IFO_REPLACE";
				$idx_destination[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\MAIN";
				$prm_destination[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\MAIN.vsparam";
				$prmfile = fopen($prm_destination[$i], 'w');
				fwrite($prmfile, $idx_source[$i] . "\r\n");
				fwrite($prmfile, $idx_destination[$i] . "\r\n");
				fwrite($prmfile, $pgc_id[$i] . "\r\n");
				fwrite($prmfile, "0\r\n");
				fwrite($prmfile, "en\r\n");
				fwrite($prmfile, "CLOSE\r\n");
				fclose($prmfile);
				fwrite($batfile, "ECHO Creating subtitles for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
				fwrite($batfile, "START \"Fixing vsparam files for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /D \"". $d2m_loc . "\" /wait /min \"" . $pe_loc . "\" \"--param\" \"" . $vob_destination[$i] . "\\\" \"MAIN.vsparam\"\r\n");
				fwrite($batfile, "START \"Creating subtitles for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /wait /min rundll32 \"" . $vu_loc . "\",Configure " . $prm_destination[$i] . "\r\n");
				$i++;
				echo ".";
			}
			$i = 1;
		} else {
			$idx_source[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\IFO_REPLACE";
			$idx_destination[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\MAIN";
			$prm_destination[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\MAIN.vsparam";
			$prmfile = fopen($prm_destination[1], 'w');
			fwrite($prmfile, $idx_source[1] . "\r\n");
			fwrite($prmfile, $idx_destination[1] . "\r\n");
			fwrite($prmfile, $pgc_id[1] . "\r\n");
			fwrite($prmfile, "0\r\n");
			fwrite($prmfile, "en\r\n");
			fwrite($prmfile, "CLOSE\r\n");
			fclose($prmfile);
			fwrite($batfile, "ECHO Creating subtitles for " . ScriptSanitize($content_title) . "...\r\n");
			fwrite($batfile, "START \"Fixing vsparam files for " . ScriptSanitize($content_title) . "...\" /D \"". $d2m_loc . "\" /wait /min \"" . $pe_loc . "\" --param \"" . $vob_destination[1] . "\\\" \"MAIN.vsparam\"\r\n");
			fwrite($batfile, "START \"Creating subtitles for " . ScriptSanitize($content_title) . "...\" /wait /min rundll32 \"" . $vu_loc . "\",Configure " . $prm_destination[1] . "\r\n");
			echo ".";
		}
	}
	
	# Rename VOBs for demuxing
	fwrite($batfile, "\r\nECHO.\r\n");
	fwrite($batfile, "REM \r\n");
	fwrite($batfile, "REM Rename VOBs (and IFOs) for demuxing\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$vob_destination[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\";
			fwrite($batfile, "ECHO Renaming VOB files for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
			fwrite($batfile, "ren " . $vob_destination[$i] . "*.vob MAIN.vob\r\n");
			fwrite($batfile, "ren " . $vob_destination[$i] . "*.ifo MAIN.ifo\r\n");
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$vob_destination[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\";
		fwrite($batfile, "ECHO Renaming VOB files for " . ScriptSanitize($content_title) . "...\r\n");
		fwrite($batfile, "ren " . $vob_destination[1] . "*.vob MAIN.vob\r\n");
		fwrite($batfile, "ren " . $vob_destination[1] . "*.ifo MAIN.ifo\r\n");
		echo ".";
	}

	# Write the VOB demux code to the script
	fwrite($batfile, "\r\nECHO.\r\n");
	fwrite($batfile, "REM \r\n");
	fwrite($batfile, "REM Write the VOB demux code to the script\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$m2v_destination[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\VIDEO.m2v";
			fwrite($batfile, "ECHO Demuxing video information for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
			fwrite($batfile, "START \"Demuxing video information for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /wait /min \"" . $vs_loc . "\" \"" . $vob_destination[$i] . "MAIN.vob\" -!do\"" . $m2v_destination[$i] . "\" " . $video_stream[$i] . "\r\n");
			if ($audio_quantity[$i] > 1) {
				$r = 1;
				while ($r <= $audio_quantity[$i]) {
					$audio_destination[$i][$r] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\AUDIO-" . ($r - 1) . ".ac3";
					fwrite($batfile, "ECHO Demuxing audio track " . $r . " for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
					fwrite($batfile, "START \"Demuxing audio track " . $r . " for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /wait /min \"" . $vs_loc . "\" \"" . $vob_destination[$i] . "MAIN.vob\" -!do\"" . $audio_destination[$i][$r] . "\" 0xBD " . $audio_stream[$i][$r-1] . "\r\n");
					$r++;
				}
			} else {
				$audio_destination[$i][1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\AUDIO-0.ac3";
				fwrite($batfile, "ECHO Demuxing audio track 1 for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
				fwrite($batfile, "START \"Demuxing audio track 1 for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /wait /min \"" . $vs_loc . "\" \"" . $vob_destination[$i] . "MAIN.vob\" -!do\"" . $audio_destination[$i][1] . "\" 0xBD " . $audio_stream[$i][0] . "\r\n");
			}
			$i++;
			echo ".";
		}
		$i = 1;
		$r = 1;
	} else {
		$m2v_destination[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\MAIN.m2v";
		fwrite($batfile, "ECHO Demuxing video information for " . ScriptSanitize($content_title) . "...\r\n");
		fwrite($batfile, "START \"Demuxing video information for " . ScriptSanitize($content_title) . "...\" /wait /min \"" . $vs_loc . "\" \"" . $vob_destination[1] . "MAIN.vob\" -!do\"" . $m2v_destination[1] . "\" " . $video_stream[1] . "\r\n");
		if ($audio_quantity[$i] > 1) {
			$r = 1;
			while ($r <= $audio_quantity[1]) {
				$audio_destination[1][$r] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\AUDIO-" . ($r - 1) . ".ac3";
				fwrite($batfile, "ECHO Demuxing audio track " . $r . " for " . ScriptSanitize($content_title) . "...\r\n");
				fwrite($batfile, "START \"Demuxing audio track " . $r . " for " . ScriptSanitize($content_title) . "...\" /wait /min \"" . $vs_loc . "\" \"" . $vob_destination[1] . "MAIN.vob\" -!do\"" . $audio_destination[1][$r] . "\" 0xBD " . $audio_stream[1][$r-1] . "\r\n");
				$r++;
			}
		} else {
			$audio_destination[1][1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\AUDIO-0.ac3";
			fwrite($batfile, "ECHO Demuxing audio track 1 for " . ScriptSanitize($content_title) . "...\r\n");
			fwrite($batfile, "START \"Demuxing audio track 1 for " . ScriptSanitize($content_title) . "...\" /wait /min \"" . $vs_loc . "\" \"" . $vob_destination[1] . "MAIN.vob\" -!do\"" . $audio_destination[1][1] . "\" 0xBD " . $audio_stream[1][0] . "\r\n");
		}
		echo ".";
		$i = 1;
		$r = 1;
	}
	
	# Write the code to get the chapters to the script
	fwrite($batfile, "\r\nECHO.\r\n");
	fwrite($batfile, "REM \r\n");
	fwrite($batfile, "REM Write the code to get the chapters to the script\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$chapter_file[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\CHAPTERS.txt";
			fwrite($batfile, "ECHO Getting chapter markers for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
			fwrite($batfile, "START \"Getting chapter markers for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /wait /min \"" . $cx_loc . "\" \"" . $vob_destination[$i] . "MAIN.ifo\" \"" . $chapter_file[$i] . "\" -p5 -t" . $pgc_id[$i] . "\r\n");
			if ($chapter_list[$i] != "*") {
				fwrite($batfile, "ECHO Correcting chapter markers for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
				fwrite($batfile, "START \"Correcting chapter markers for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /D \"". $d2m_loc . "\" /wait /min \"" . $ce_loc . "\" \"--chapteredit\" \"" . $vob_destination[$i] . "\\\" \"CHAPTERS.txt\" \"". $chapter_list[$i] . "\"\r\n");
			}
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$chapter_file[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\CHAPTERS.txt";
		fwrite($batfile, "ECHO Getting chapter markers for " . ScriptSanitize($content_title) . "...\r\n");
		fwrite($batfile, "START \"Getting chapter markers for " . ScriptSanitize($content_title) . "...\" /wait /min \"" . $cx_loc . "\" \"" . $vob_destination[1] . "MAIN.ifo\" \"" . $chapter_file[1] . "\" -p5 -t" . $pgc_id[1] . "\r\n");
		if ($chapter_list[$i] != "*") {
			fwrite($batfile, "ECHO Correcting chapter markers for " . ScriptSanitize($content_title) . "...\r\n");
			fwrite($batfile, "START \"Correcting chapter markers for " . ScriptSanitize($content_title) . "...\" /D \"". $d2m_loc . "\" /wait /min \"" . $ce_loc . "\" \"--chapteredit\" \"" . $vob_destination[1] . "\\\" \"CHAPTERS.txt\" \"". $chapter_list[1] . "\"\r\n");
		}
		echo ".";
	}

	# Write the d2v compilation code to the script
	fwrite($batfile, "\r\nECHO.\r\n");
	fwrite($batfile, "REM \r\n");
	fwrite($batfile, "REM Write the d2v compilation code to the script\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$d2v_destination[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\MAIN";
			fwrite($batfile, "ECHO Creating MPEG summary file for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
			fwrite($batfile, "START \"Creating MPEG summary file for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /wait /min \"" . $dg_loc . "\" -i \"" . $m2v_destination[$i] . "\" -o \"" . $d2v_destination[$i] . "\" -ia 5 -fo 0 -yr 1 -om 0 -exit\r\n");
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$d2v_destination[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\MAIN";
		fwrite($batfile, "ECHO Creating MPEG summary file for " . ScriptSanitize($content_title) . "...\r\n");
		fwrite($batfile, "START \"Creating MPEG summary file for " . ScriptSanitize($content_title) . "...\" /wait /min \"" . $dg_loc . "\" -i \"" . $m2v_destination[1] . "\" -o \"" . $d2v_destination[1] . "\" -ia 5 -fo 0 -yr 1 -om 0 -exit\r\n");
		echo ".";
	}

	# Write the AviSynth script file
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$avs_destination[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\MAIN.avs";
			$avsfile = fopen($avs_destination[$i], 'w');
			fwrite($avsfile, "LoadPlugin(\"" . $gd_loc . "\")" . "\r\n");
			fwrite($avsfile, "LoadPlugin(\"" . $tc_loc . "\")" . "\r\n");
			fwrite($avsfile, "LoadPlugin(\"" . $td_loc . "\")" . "\r\n");
			fwrite($avsfile, "LoadPlugin(\"" . $mt_loc . "\")" . "\r\n");
			fwrite($avsfile, "LoadPlugin(\"" . $dp_loc . "\")" . "\r\n");
			fwrite($avsfile, "LoadPlugin(\"" . $de_loc . "\")" . "\r\n");
			fwrite($avsfile, "LoadPlugin(\"" . $nn_loc . "\")" . "\r\n");
			fwrite($avsfile, "LoadPlugin(\"" . $rg_loc . "\")" . "\r\n");
			fwrite($avsfile, "LoadPlugin(\"" . $dc_loc . "\")" . "\r\n");
			fwrite($avsfile, "Import(\"" . $sm_loc . "\")" . "\r\n");
			fwrite($avsfile, "Import(\"" . $qt_loc . "\")" . "\r\n");
			fwrite($avsfile, "MPEG2Source(\"" . $d2v_destination[$i] . ".d2v\")" . "\r\n");
			if ($post_proc == 2 || $post_proc == 3) {
				fwrite($avsfile, "A=Last\r\n");
				fwrite($avsfile, "B=A.QTGMC()\r\n");
				fwrite($avsfile, "C=A\r\n");
				fwrite($avsfile, "D=A.TDeint(tryWeave=true,mode=2,cthresh=3,mtnmode=3,blim=100,slow=2,edeint=B)\r\n");
				fwrite($avsfile, "ConditionalFilter(A,D,C, \"IsCombed(5)\", \"equals\", \"true\", show=false)\r\n");
			} elseif ($post_proc == 1) {
				fwrite($avsfile, "tfm(d2v=\"" . $d2v_destination[$i] . ".d2v\")" . "\r\n");
			}
			if ($post_proc == 1 || $post_proc == 3) {
				if ($content_type == 1) {
					fwrite($avsfile, "TDecimate(mode=1)" . "\r\n");
				} elseif ($content_type == 0) {
					fwrite($avsfile, "TDecimate()" . "\r\n");
				}
			}
			if ($crop_content == 1) {
				fwrite($avsfile, "crop(8,0,-8,0)" . "\r\n");
			}
			if ($aspect_ratio == 1) {
				fwrite($avsfile, "BicubicResize(640,480,0,0.5)" . "\r\n");
			} else {
				fwrite($avsfile, "BicubicResize(848,480,0,0.5)" . "\r\n");
			}
			fclose($avsfile);
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$avs_destination[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\MAIN.avs";
		$avsfile = fopen($avs_destination[1], 'w');
		fwrite($avsfile, "LoadPlugin(\"" . $gd_loc . "\")" . "\r\n");
		fwrite($avsfile, "LoadPlugin(\"" . $tc_loc . "\")" . "\r\n");
		fwrite($avsfile, "LoadPlugin(\"" . $td_loc . "\")" . "\r\n");
		fwrite($avsfile, "LoadPlugin(\"" . $mt_loc . "\")" . "\r\n");
		fwrite($avsfile, "LoadPlugin(\"" . $dp_loc . "\")" . "\r\n");
		fwrite($avsfile, "LoadPlugin(\"" . $de_loc . "\")" . "\r\n");
		fwrite($avsfile, "LoadPlugin(\"" . $nn_loc . "\")" . "\r\n");
		fwrite($avsfile, "LoadPlugin(\"" . $rg_loc . "\")" . "\r\n");
		fwrite($avsfile, "LoadPlugin(\"" . $dc_loc . "\")" . "\r\n");
		fwrite($avsfile, "Import(\"" . $sm_loc . "\")" . "\r\n");
		fwrite($avsfile, "Import(\"" . $qt_loc . "\")" . "\r\n");
		fwrite($avsfile, "MPEG2Source(\"" . $d2v_destination[1] . ".d2v\")" . "\r\n");
		if ($post_proc == 2 || $post_proc == 3) {
			fwrite($avsfile, "A=Last\r\n");
			fwrite($avsfile, "B=A.QTGMC()\r\n");
			fwrite($avsfile, "C=A\r\n");
			fwrite($avsfile, "D=A.TDeint(tryWeave=true,mode=2,cthresh=3,mtnmode=3,blim=100,slow=2,edeint=B)\r\n");
			fwrite($avsfile, "ConditionalFilter(A,D,C, \"IsCombed(5)\", \"equals\", \"true\", show=false)\r\n");
		} elseif ($post_proc == 1) {
			fwrite($avsfile, "tfm(d2v=\"" . $d2v_destination[1] . ".d2v\")" . "\r\n");
		}
		if ($post_proc == 1 || $post_proc == 3) {
			if ($content_type == 1) {
				fwrite($avsfile, "TDecimate(mode=1)" . "\r\n");
			} elseif ($content_type == 0) {
				fwrite($avsfile, "TDecimate()" . "\r\n");
			}
		}
		if ($crop_content == 1) {
			fwrite($avsfile, "crop(8,0,-8,0)" . "\r\n");
		}
		if ($aspect_ratio == 1) {
			fwrite($avsfile, "BicubicResize(640,480,0,0.5)" . "\r\n");
		} else {
			fwrite($avsfile, "BicubicResize(848,480,0,0.5)" . "\r\n");
		}
		fclose($avsfile);
		echo ".";
	}

	# Write the commands to encode the video to the script
	fwrite($batfile, "\r\nECHO.\r\n");
	fwrite($batfile, "REM \r\n");
	fwrite($batfile, "REM Write the commands to encode the video to the script\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$x264_destination[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\MAIN.x264";
			if ($content_type == 1) {
				$x264_tune[$i] = "animation";
			} else {
				$x264_tune[$i] = "film";
			}
			fwrite($batfile, "ECHO Encoding episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
			fwrite($batfile, "START \"Encoding episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /wait /min \"" . $a26x_loc . "\" --x26x-binary \"" . $x264_loc . "\" --seek-mode safe --profile high --preset slow --crf 19 --tune " . $x264_tune[$i] . " -o \"" . $x264_destination[$i] . "\" \"" . $avs_destination[$i] . "\"\r\n");
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$x264_destination[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\MAIN.x264";
		if ($content_type == 1) {
				$x264_tune[1] = "animation";
			} else {
				$x264_tune[1] = "film";
			}
		fwrite($batfile, "ECHO Encoding " . ScriptSanitize($content_title) . "...\r\n");
		fwrite($batfile, "START \"Encoding " . ScriptSanitize($content_title) . "...\" /wait /min \"" . $a26x_loc . "\" --x26x-binary \"" . $x264_loc . "\" --seek-mode safe --profile high --preset slow --crf 19 --tune " . $x264_tune[1] . " -o \"" . $x264_destination[1] . "\" \"" . $avs_destination[1] . "\"\r\n");
		echo ".";
	}
	
	# Write version ID files
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$version_destination[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\COMPILED_USING_DVD2MKV_" . $d2m_ver;
			$verfile = fopen($version_destination[$i], 'w');
			fwrite($verfile, $content_title . ": Season " . $season . ", Episode " . $episode_number[$i] . "\r\n");
			fwrite($verfile, "\"" . $episode_title[$i] . "\"\r\n");
			fwrite($verfile, "Compiled using dvd2mkv version " . $d2m_ver . "\r\n");
			fclose($verfile);
			$i++;
			echo ".";
		}
		$i = 1;
	} else {
		$version_destination[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\COMPILED_USING_DVD2MKV_" . $d2m_ver;
		$verfile = fopen($version_destination[1], 'w');
		fwrite($verfile, $content_title . "\r\n");
		fwrite($verfile, "Compiled using dvd2mkv version " . $d2m_ver . "\r\n");
		fclose($verfile);
		echo ".";
	}

	# Write the commands to the script to create the mkv file
	fwrite($batfile, "\r\nECHO.\r\n");
	fwrite($batfile, "REM \r\n");
	fwrite($batfile, "REM Write the commands to the script to create the mkv file\r\n");
	fwrite($batfile, "REM \r\n");
	if ($dvd_type == 1) {
		while ($i <= $num_items) {
			$mkv_destination[$i] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\SEASON" . $season . "\\EPISODE" . $episode_number[$i] . "\\" . filename($content_title . "-S" . $season . "E" . $episode_number[$i] . "-" . $episode_title[$i]) . ".mkv";
			if ($aspect_ratio == 0) {
				$video_ar = "16/9";
				$video_dimensions = "848x480";
			} elseif ($aspect_ratio == 1) {
				$video_ar = "4/3";
				$video_dimensions = "640x480";
			}
			if ($post_proc == 2) {
				$video_options = " --language 0:und --track-name 0:Video --aspect-ratio 0:" . $video_ar . " --display-dimensions 0:" . $video_dimensions . " --compression 0:none --default-duration 0:30000/1001p ^\"^(^\" ^\"" . $x264_destination[$i] . "^\" ^\"^)^\"";
			} elseif ($post_proc == 1 || $post_proc == 3) {
				$video_options = " --language 0:und --track-name 0:Video --aspect-ratio 0:" . $video_ar . " --display-dimensions 0:" . $video_dimensions . " --compression 0:none --default-duration 0:24000/1001p ^\"^(^\" ^\"" . $x264_destination[$i] . "^\" ^\"^)^\"";
			}
			if ($audio_quantity[$i] > 1) {
				$r = 1;
				while ($r <= $audio_quantity[$i]) {
					if ($r == 1) {
						$audio_options = " --language 0:eng --track-name ^\"0:" . $audio_title[$i][$r-1] . "^\" --default-track 0:yes --compression 0:none ^\"^(^\" ^\"" . $audio_destination[$i][$r] . "^\" ^\"^)^\"";
					} else {
						$audio_options = $audio_options . " --language 0:eng --track-name ^\"0:" . $audio_title[$i][$r-1] . "^\" --default-track 0:no --compression 0:none ^\"^(^\" ^\"" . $audio_destination[$i][$r] . "^\" ^\"^)^\"";
					}
					$r++;
				}
			} else {
				$audio_options = " --language 0:eng --track-name ^\"0:" . $audio_title[$i][0] . "^\" --default-track 0:yes --compression 0:none ^\"^(^\" ^\"" . $audio_destination[$i][1] . "^\" ^\"^)^\"";
			}
			$chapter_options = " --chapter-language und --chapters ^\"" . $chapter_file[$i] . "^\"";
			if ($has_subs == 1) {
				$sub_options = " --language 0:eng --track-name 0:English --default-track 0:no --compression 0:none ^\"^(^\" ^\"" . $idx_destination[$i] . ".idx^\" ^\"^)^\"";
			} else {
				$sub_options = "";
			}
			$m = 0;
			while ($m <= $r+1) {
				if ($m == 0) {
					$track_order = $m . ":0,";
				} elseif ($m == $r+1) {
					$track_order = $track_order . $m . ":0";
				} else {
					$track_order = $track_order . $m . ":0,";
				}
				$m++;
			}
			$generator_options = " --track-order " . $track_order . " --attachment-name COMPILED_USING_DVD2MKV_" . $d2m_ver . " --attachment-mime-type text/plain --attach-file ^\"" . $version_destination[$i] . "^\" --title ^\"" . ScriptSanitize($episode_title[$i]) . "^\"";
			fwrite($batfile, "ECHO Compiling output for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\r\n");
			fwrite($batfile, "START \"Compiling output for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /wait /min \"" . $mk_loc . "\" --ui-language en --output ^\"" . $mkv_destination[$i] . "^\"" .  $video_options . $audio_options . $sub_options . $generator_options . $chapter_options . "\r\n");
			fwrite($batfile, "START \"Modifying header properties for episode " . $episode_number[$i] . ": " . ScriptSanitize($episode_title[$i]) . "...\" /wait /min \"" . $mp_loc . "\" \"" . $mkv_destination[$i] . "\" --edit track:v1 --set display-unit=3" . "\r\n");
			$i++;
			$r = 1;
			echo ".";
		}
		$i = 1;
	} else {
		$mkv_destination[1] = $rd_loc . "\\RIPPED\\" . SanitizeName($content_title) . "\\" . filename($content_title) . ".mkv";
		if ($aspect_ratio == 0) {
			$video_ar = "16/9";
			$video_dimensions = "848x480";
		} elseif ($aspect_ratio == 1) {
			$video_ar = "4/3";
			$video_dimensions = "640x480";
		}
		if ($post_proc == 2) {
				$video_options = " --language 0:und --track-name 0:Video --aspect-ratio 0:" . $video_ar . " --display-dimensions 0:" . $video_dimensions . " --compression 0:none --default-duration 0:30000/1001p ^\"^(^\" ^\"" . $x264_destination[1] . "^\" ^\"^)^\"";
			} elseif ($post_proc == 1 || $post_proc == 3) {
				$video_options = " --language 0:und --track-name 0:Video --aspect-ratio 0:" . $video_ar . " --display-dimensions 0:" . $video_dimensions . " --compression 0:none --default-duration 0:24000/1001p ^\"^(^\" ^\"" . $x264_destination[1] . "^\" ^\"^)^\"";
			}
		if ($audio_quantity[1] > 1) {
			$r = 1;
			while ($r <= $audio_quantity[1]) {
				if ($r == 1) {
					$audio_options = " --language 0:eng --track-name ^\"0:" . $audio_title[1][$r-1] . "^\" --default-track 0:yes --compression 0:none ^\"^(^\" ^\"" . $audio_destination[1][$r] . "^\" ^\"^)^\"";
				} else {
					$audio_options = $audio_options . " --language 0:eng --track-name ^\"0:" . $audio_title[1][$r-1] . "^\" --default-track 0:no --compression 0:none ^\"^(^\" ^\"" . $audio_destination[1][$r] . "^\" ^\"^)^\"";
				}
				$r++;
			}
		} else {
			$audio_options = " --language 0:eng --track-name ^\"0:" . $audio_title[1][0] . "^\" --default-track 0:yes --compression 0:none ^\"^(^\" ^\"" . $audio_destination[1][1] . "^\" ^\"^)^\"";
		}
		$chapter_options = " --chapter-language und --chapters ^\"" . $chapter_file[1] . "^\"";
		if ($has_subs == 1) {
			$sub_options = " --language 0:eng --track-name 0:English --default-track 0:no --compression 0:none ^\"^(^\" ^\"" . $idx_destination[1] . ".idx^\" ^\"^)^\"";
		} else {
			$sub_options = "";
		}
		$m = 0;
		while ($m <= $r+1) {
			if ($m == 0) {
				$track_order = $m . ":0,";
			} elseif ($m == $r+1) {
				$track_order = $track_order . $m . ":0";
			} else {
				$track_order = $track_order . $m . ":0,";
			}
			$m++;
		}
		$generator_options = " --track-order " . $track_order . " --attachment-name COMPILED_USING_DVD2MKV_" . $d2m_ver . " --attachment-mime-type text/plain --attach-file ^\"" . $version_destination[1] . "^\" --title ^\"" . ScriptSanitize($content_title) . "^\"";
		fwrite($batfile, "ECHO Compiling output for " . ScriptSanitize($content_title) . "...\r\n");
		fwrite($batfile, "START \"Compiling output for " . ScriptSanitize($content_title) . "...\" /wait /min \"" . $mk_loc . "\" --ui-language en --output ^\"" . $mkv_destination[1] . "^\"" .  $video_options . $audio_options . $sub_options . $generator_options . $chapter_options . "\r\n");
		fwrite($batfile, "START \"Modifying header properties for " . ScriptSanitize($content_title) . "...\" /wait /min \"" . $mp_loc . "\" \"" . $mkv_destination[1] . "\" --edit track:v1 --set display-unit=3" . "\r\n");
		$r = 1;
		echo ".";
	}
}

fwrite($batfile, "\r\nECHO.\r\nECHO.\r\nECHO Process complete, shutting down...\r\n");
fwrite($batfile, "PAUSE\r\n");
echo " Done!";
sleep (5);


## Close streams and shutdown script
fclose($handle);
fclose($batfile);
exit;

?>