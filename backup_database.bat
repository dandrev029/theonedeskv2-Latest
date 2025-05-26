@echo off
REM Automated Database Backup Script for TheOneDesk
REM This script creates daily backups of the database

REM Set variables
set MYSQL_PATH=C:\xampp\mysql\bin
set DB_NAME=theonedesk
set DB_USER=root
set DB_PASS=
set BACKUP_DIR=C:\Users\Sta Rosa\Desktop\TheOneDesk Daily Backups\Database Backups
set DATE=%date:~-4,4%-%date:~-10,2%-%date:~-7,2%
set TIME=%time:~0,2%-%time:~3,2%-%time:~6,2%
set BACKUP_FILE=%BACKUP_DIR%\theonedesk_backup_%DATE%_%TIME%.sql

REM Create backup directory if it doesn't exist
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM Create database backup
echo Creating database backup...
"%MYSQL_PATH%\mysqldump.exe" -u %DB_USER% %DB_NAME% > "%BACKUP_FILE%"

REM Check if backup was successful
if %errorlevel% equ 0 (
    echo Backup created successfully: %BACKUP_FILE%
    
    REM Keep only last 7 days of backups (cleanup old files)
    forfiles /p "%BACKUP_DIR%" /s /m *.sql /d -7 /c "cmd /c del @path" 2>nul
    
    echo Old backups cleaned up (keeping last 7 days)
) else (
    echo Backup failed!
)

pause
