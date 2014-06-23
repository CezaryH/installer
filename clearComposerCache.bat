@echo off
FOR /D %%p IN ("%UserProfile%\AppData\Local\Composer\files\*.*") DO rmdir "%%p" /s /q
FOR /D %%p IN ("%UserProfile%\AppData\Local\Composer\repo\*.*") DO rmdir "%%p" /s /q
FOR /D %%p IN ("%UserProfile%\AppData\Local\Composer\vcs\*.*") DO rmdir "%%p" /s /q