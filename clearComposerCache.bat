FOR /D %%p IN ("C:\Users\lap\AppData\Local\Composer\files\*.*") DO rmdir "%%p" /s /q
FOR /D %%p IN ("C:\Users\lap\AppData\Local\Composer\repo\*.*") DO rmdir "%%p" /s /q
FOR /D %%p IN ("C:\Users\lap\AppData\Local\Composer\vcs\*.*") DO rmdir "%%p" /s /q