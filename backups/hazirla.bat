@echo off
echo ROTAREHBER Klasor Hazirlayici - Yonetici olarak calistirin...
echo.

echo 1. Servisler durduruluyor...
net stop Apache24 /y
net stop mysql8 /y

echo.
echo 2. Islemler sonlandiriliyor (Zorunlu)...
taskkill /F /IM httpd.exe /T
taskkill /F /IM mysqld.exe /T
taskkill /F /IM php-cgi.exe /T

echo.
echo 3. Gecici dosyalar temizleniyor...
if exist "C:\AppServ\www\REHBER\sessions" (
    del /F /Q "C:\AppServ\www\REHBER\sessions\sess_*"
)
if exist "C:\AppServ\www\REHBER\auth_debug.log" del /F /Q "C:\AppServ\www\REHBER\auth_debug.log"
if exist "C:\AppServ\www\REHBER\sms_debug.log" del /F /Q "C:\AppServ\www\REHBER\sms_debug.log"

echo.
echo ------------------------------------------------------------
echo Islem tamamlandi. Artik REHBER klasorunu silebilir, tasiyabilir veya ziplebilirsiniz.
echo Not: Servisler kapandigi icin yerel site (localhost) su an calismayacaktir.
echo Tekrar calistirmak icin bilgisayari yeniden baslatin veya servisleri manuel acin.
echo ------------------------------------------------------------
pause
