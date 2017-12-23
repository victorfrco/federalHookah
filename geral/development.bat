@echo off
start C:\starthttpd.vbs
start C:\startmysqld.vbs
start chrome http://localhost:8000
cd \Users\Federal Hookah Pub\federalHookah\federalHookah
php artisan serve
Exit