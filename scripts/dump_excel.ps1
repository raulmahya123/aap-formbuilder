$filePath = 'C:\Users\raulm\Downloads\Menu SHE dalam A-Rise (3).xlsx'
$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false

try {
    $wb = $excel.Workbooks.Open($filePath)
    foreach ($ws in $wb.Sheets) {
        Write-Host "========================================"
        Write-Host "SHEET: $($ws.Name) (Rows: $($ws.UsedRange.Rows.Count), Cols: $($ws.UsedRange.Columns.Count))"
        Write-Host "========================================"
        $rowCount = [Math]::Min($ws.UsedRange.Rows.Count, 40)
        $colCount = [Math]::Min($ws.UsedRange.Columns.Count, 12)
        
        for ($r = 1; $r -le $rowCount; $r++) {
            $rowVals = @()
            for ($c = 1; $c -le $colCount; $c++) {
                $val = $ws.Cells.Item($r, $c).Text
                $rowVals += $val
            }
            if (($rowVals -join "").Trim() -ne "") {
                Write-Host "R$r`: $($rowVals -join ' | ')"
            }
        }
    }
    $wb.Close($false)
}
finally {
    $excel.Quit()
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($excel) | Out-Null
}
