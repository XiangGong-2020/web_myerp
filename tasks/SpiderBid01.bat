@echo off
:: /d 参数支持跨盘符切换目录，%~dp0.. 就是 bat 所在目录的上一级
cd /d "%~dp0.."

:: 现在当前工作目录就是项目根目录，直接写文件名即可
php cli.php Api SpiderBid01 index