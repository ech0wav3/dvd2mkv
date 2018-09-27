@echo off
SET installdir=%CD:~0,3%

mkdir %installdir%dvd2mkv-2018.09\
echo Compiling dvd2mkv...
bamcompile1.21\bamcompile -c ..\dvd2mkv.php %installdir%dvd2mkv-2018.09\dvd2mkv.exe
echo Copying settings...
copy /y ..\settings.conf %installdir%dvd2mkv-2018.09\settings.conf
echo Copying required files...
robocopy "..\Support Apps " %installdir%dvd2mkv-2018.09\ /s /e
echo Setup complete!
echo dvd2mkv can be found at: %installdir%dvd2mkv-2018.09\
pause