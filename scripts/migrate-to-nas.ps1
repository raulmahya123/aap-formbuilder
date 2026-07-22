<#
.SYNOPSIS
    Copy all files from storage/app/public to NAS (\\160.19.165.217\E-Mandala\uploads)
.DESCRIPTION
    Mirrors the local public storage to the NAS share.
    Run this after confirming NAS is accessible.
    After copying, run: php artisan config:clear
#>

$LocalRoot = Join-Path $PSScriptRoot "..\storage\app\public"
$NasRoot   = "\\160.19.165.217\E-Mandala\uploads"

Write-Host "=== Mandala NAS Migration ===" -ForegroundColor Cyan
Write-Host "Source:      $LocalRoot"
Write-Host "Destination: $NasRoot"

# Test NAS accessibility
if (-not (Test-Path $NasRoot -ErrorAction SilentlyContinue)) {
    Write-Host "[ERROR] NAS path not accessible: $NasRoot" -ForegroundColor Red
    Write-Host "Make sure you are connected to the same network as the NAS." -ForegroundColor Yellow
    exit 1
}

$files = Get-ChildItem -Path $LocalRoot -Recurse -File
$total = $files.Count
$sizeMB = [math]::Round(($files | Measure-Object -Property Length -Sum).Sum / 1MB, 2)

Write-Host "`nFound $total files ($sizeMB MB) to migrate.`n" -ForegroundColor Yellow

$copied = 0
$skipped = 0
$errors = 0

foreach ($file in $files) {
    $rel = $file.FullName.Substring($LocalRoot.Length).TrimStart('\','/')
    $destPath = Join-Path $NasRoot $rel
    $destDir  = Split-Path $destPath -Parent

    if (-not (Test-Path $destDir -ErrorAction SilentlyContinue)) {
        try {
            New-Item -Path $destDir -ItemType Directory -Force -ErrorAction Stop | Out-Null
        } catch {
            Write-Host "[ERR] mkdir $destDir : $_" -ForegroundColor Red
            $errors++
            continue
        }
    }

    if ((Test-Path $destPath -ErrorAction SilentlyContinue) -and
        (Get-Item $destPath).Length -eq $file.Length) {
        $skipped++
        continue
    }

    try {
        Copy-Item -Path $file.FullName -Destination $destPath -Force -ErrorAction Stop
        $copied++
        Write-Host "  [OK] $rel" -ForegroundColor Gray
    } catch {
        Write-Host "  [ERR] $rel : $_" -ForegroundColor Red
        $errors++
    }
}

Write-Host "`n=== Done ===" -ForegroundColor Cyan
Write-Host "Copied: $copied | Skipped (identical): $skipped | Errors: $errors"
Write-Host "`nNext steps:" -ForegroundColor Yellow
Write-Host "  1. php artisan config:clear"
Write-Host "  2. Test upload & serve on a form"
