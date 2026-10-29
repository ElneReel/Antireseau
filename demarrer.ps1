$env:Path = "C:\Users\AllZ\.tools\php;C:\Users\AllZ\.tools;" + $env:Path
Set-Location $PSScriptRoot

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "   Insociable : Personne à suivre. Tout le monde ici." -ForegroundColor Cyan
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Lancement sur http://127.0.0.1:8000 ..." -ForegroundColor Green

Start-Process "http://127.0.0.1:8000"
php artisan serve --host=127.0.0.1 --port=8000
