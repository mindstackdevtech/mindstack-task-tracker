@echo off
setlocal
rem ==================================================================
rem  MindStack Task Tracker: build the folder to upload to InfinityFree
rem
rem  Run it from your project folder:
rem      cd /d C:\xampp1\htdocs\mindstack-task-tracker
rem      deploy\prepare-deploy.bat
rem
rem  It makes a copy next to your project called mindstack-upload.
rem  Your project folder itself is not changed.
rem ==================================================================

set "SRC=%CD%"
for %%I in ("%SRC%\..") do set "PARENT=%%~fI"
set "DEST=%PARENT%\mindstack-upload"

if not exist "%SRC%\artisan" (
    echo.
    echo  This is not the project folder. First run:
    echo      cd /d C:\xampp1\htdocs\mindstack-task-tracker
    echo  and then run deploy\prepare-deploy.bat again.
    goto :fail
)
if not exist "%SRC%\deploy\env.production.txt" (
    echo  The deploy folder is incomplete. Extract the deploy kit zip into the project again.
    goto :fail
)
if exist "%DEST%" (
    echo.
    echo  The folder below already exists from an earlier run:
    echo      %DEST%
    echo  Delete it first, then run this again.
    goto :fail
)

where php >nul 2>nul || (echo  PHP was not found. Run this in the same window where "php artisan serve" works. & goto :fail)
where composer >nul 2>nul || (echo  Composer was not found. Install it from getcomposer.org, then run this again. & goto :fail)

echo.
echo [1/5] Copying the project to %DEST% ...
rem Leaves out: your local .env, your local database, logs, node_modules, .git and this deploy folder.
robocopy "%SRC%" "%DEST%" /E /NFL /NDL /NJH /NJS /NP /XD "%SRC%\deploy" "%SRC%\.git" node_modules /XF .env database.sqlite *.log
if errorlevel 8 (echo  Copying failed. & goto :fail)

echo [2/5] Clearing cached files ...
del /q "%DEST%\bootstrap\cache\*.php" 2>nul
del /q "%DEST%\storage\framework\views\*.php" 2>nul
del /q "%DEST%\storage\framework\sessions\*" 2>nul
if exist "%DEST%\storage\framework\cache\data" rmdir /s /q "%DEST%\storage\framework\cache\data"
if not exist "%DEST%\storage\framework\cache\data" mkdir "%DEST%\storage\framework\cache\data"
if not exist "%DEST%\storage\logs" mkdir "%DEST%\storage\logs"

echo [3/5] Adding the live-site settings and the htdocs routing file ...
copy /y "%SRC%\deploy\env.production.txt" "%DEST%\.env" >nul
copy /y "%SRC%\deploy\htdocs.htaccess" "%DEST%\.htaccess" >nul

echo [4/5] Installing only the packages the live site needs (takes a minute) ...
pushd "%DEST%"
rem InfinityFree ignores PHP files over 1 MB, so keep the autoloader files small.
call composer config optimize-autoloader false
call composer install --no-dev --no-interaction --no-progress
if errorlevel 1 (popd & echo  Composer failed. Read the message above. & goto :fail)

echo [5/5] Creating a new secret key for the live site ...
php artisan key:generate --force
if errorlevel 1 (popd & echo  Could not create the key. & goto :fail)
popd

echo.
echo  Checking for PHP files over 1 MB (InfinityFree would drop them) ...
set "BIG=0"
for /r "%DEST%" %%F in (*.php) do if %%~zF GEQ 1048576 (echo     TOO BIG: %%F & set "BIG=1")
if "%BIG%"=="0" echo     None found. Good.

for /f %%N in ('dir /s /b /a-d "%DEST%" ^| find /c /v ""') do set "COUNT=%%N"
echo.
echo  ==============================================================
echo   Done. Upload folder: %DEST%
echo   Files to upload: %COUNT%
echo.
echo   Next: open %DEST%\.env in Notepad and fill in the lines
echo   marked FILL IN. Then follow DEPLOY.md from step 4.
echo  ==============================================================
pause
endlocal
exit /b 0

:fail
echo.
echo  Stopped. Nothing was uploaded.
pause
endlocal
exit /b 1
