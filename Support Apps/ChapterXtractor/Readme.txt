==========================================================================
                            Chapter-X-tractor
==========================================================================


## What is it ?
---------------

ChapterXtractor is made to extract chapters' timing from IFO files.
It can be used to generate part of the INI file for Micro DVD Player.

The result look like this in Micro DVD format :

[CHAPTERS]
1=0 Chapters 1
2=6213 Chapters 2
3=8726 Chapters 3
4=12665 Chapters 4
....

You can add an offset (in frames) to adjust timing.

Also, you can synchronize this result with an AVI file, so
the frame number for a chapter will be the nearest key frame.
This allow fastest seeking with the AVI file.

When you use a time based format (like in BSPlayer or with SVCD) the offset
is specified in ms. Like this format is not key frame based the AVI video 
file is not use.

In 'hh:mm:ss:cc' mode (special SVCD) :
To make your SVCD split the DVD in two part.
ex : cd1 chapter 1 to 10, cd2 chapter 11 to 20 ...
Getting timing for cd1 is easy, get the 10 first chapters.
For the second CD uncheck 10 first chapters & set them to don't use
 timestamp (right click) in custom mode then go back to hh:mm:ss:cc.

On some DVDs, the last chapter doesn't exist really. (in fact
it's probably a logo, so check it length).
ChapterXtractor (when you check 'Last chapter bug fix') will auto
disable last chapter if it's length is less than 5s.
PowerDVD, WinDVD, SmartRipper confirm that this is a chapter, but
you probably don't want it.


## Thanks to :
--------------

This tool use :
- The vStrip source code (in a modified DLL) by maven
  http://www.maven.de
- The libifo library by Thomas Mirlacher.
  see The Linux Video and DVD Project (LiViD) for more informations.
  http://www.linuxvideo.org/  
- The Ultimate Packer for eXecutables
  Copyright (c) 1996-2001 Markus Oberhumer & Laszlo Molnar
  http://wildsau.idv.uni-linz.ac.at/mfx/upx.html
  http://upx.tsx.org

## Versions :
-------------

05/2002 - v0.962 : Added -t1..99 command line parameter to choose title.
Command line is now :
ChapterXtractor ifo_file_name [text_file -p0..10 [-t1..99]]
Changed in RAW data listing : PGC# pgc_idx -> PGC# pgc_idx/pgc_count 
Formating change (for BeSweet split parameter) :
Now you must force line feed.
Footer support.
Added %as, %asf, %ams, %sp, %file and %dir.
Added filename & directory textbox.
Added Go button to execute command line that appears in the textbox.
(command line is executed in ChapterXtractor directory)

04/2002 - v0.961 : Added %ms (milliseconds) for new OGG format.

03/2002 - v0.96 : Save button
Added %ff in presets (for DVDMaestro)
Added %C and \n (line feed) in presets (for OGG)
Added header support in format

09/2001 - v0.95 : Format can be customized and saved.
Auto selection of longest title (is it the best way to select the movie?)
Added command line support :
ChapterXtractor ifo_file_name [text_file -p0..10]
p is the preset (0 mean RAW data).
ex: ChapterXtractor Matrix.ifo info.txt -p1
Also you can associate ChapterXtractor with ifo files.

08/2001 - v0.94 : Using negative offset is allowed again (bug introduced
in 0.93 version). A warning is displayed if last chapter's length is less
than 5 seconds. Modification done in the customize mode can be seen on
the hh:mm:ss:cc format (good for SVCD).

06/2001 - v0.93 : Change to vStrip library (based on libifo too).
A new custom mode allow to set the state (enable, disable) or change
 links of each cell.

03/2001 - v0.92 : Now you can choose the title to process.

02/2001 - v0.91 : Now you can manually change the video frame rate.

01/2001 - v0.9  : First public release.

## Author :
-----------

For bug report,  new feature use :
Christophe.Paris@free.fr

