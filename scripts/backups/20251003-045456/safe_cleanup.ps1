# Safe cleanup script (Opção A) for Projeto-TCC
# - cria backup em backups\<timestamp>
# - remove trailing whitespace, colapsa múltiplas linhas vazias, garante 1 newline no fim
# - preserva estilo de quebra de linha por arquivo
# Uso: powershell -ExecutionPolicy Bypass -File .\scripts\safe_cleanup.ps1

$root = Split-Path -Parent $MyInvocation.MyCommand.Definition
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupDir = Join-Path $root "backups\$timestamp"

Write-Host "Raiz do projeto: $root"
Write-Host "Criando backup em: $backupDir"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

# Copia todo o conteúdo (exceto backups) para o backup
Get-ChildItem -Path $root -Force | Where-Object { $_.Name -ne 'backups' } | ForEach-Object {
    $dest = Join-Path $backupDir $_.Name
    if ($_.PSIsContainer) {
        Copy-Item -Path $_.FullName -Destination $dest -Recurse -Force
    } else {
        Copy-Item -Path $_.FullName -Destination $dest -Force
    }
}

# Extensões a processar
$exts = '*.html','*.css','*.js','*.php','*.txt','*.md'
$files = Get-ChildItem -Path $root -Include $exts -Recurse -File -ErrorAction SilentlyContinue | Where-Object { $_.FullName -notmatch '\\backups\\' }

$report = @()
foreach ($f in $files) {
    try {
        $orig = Get-Content -Raw -Encoding UTF8 -ErrorAction Stop -Path $f.FullName
    } catch {
        # tentar com default encoding se UTF8 falhar
        $orig = Get-Content -Raw -ErrorAction SilentlyContinue -Path $f.FullName
    }
    if ($null -eq $orig) { continue }

    # detectar EOL
    $eol = "`n"
    if ($orig -match "`r`n") { $eol = "`r`n" }
    elseif ($orig -match "`n") { $eol = "`n" }
    else { $eol = [Environment]::NewLine }

    $new = $orig

    # remover trailing whitespace (espaços/tabs antes de newline)
    $new = [regex]::Replace($new, "[ \t]+(?=\r?\n)", "", [System.Text.RegularExpressions.RegexOptions]::Multiline)

    # colapsar 3+ quebras em exatamente 2 (uma linha vazia)
    $new = [regex]::Replace($new, "(\r?\n){3,}", ($eol + $eol))

    # remover espaços em branco no fim do arquivo (incluindo quebras)
    $new = [regex]::Replace($new, "(\s|\r|\n)+$", "")

    # garantir exatamente um newline final
    $new = $new + $eol

    if ($new -ne $orig) {
    # gravar com UTF8 (sem BOM)
    $utf8NoBom = [System.Text.Encoding]::UTF8
    [System.IO.File]::WriteAllText($f.FullName, $new, $utf8NoBom)
        $report += $f.FullName
        Write-Host "Atualizado: $($f.FullName)"
    }
}

# salvar relatório
$reportPath = Join-Path $backupDir 'cleanup_report.txt'
$report | Out-File -FilePath $reportPath -Encoding UTF8
Write-Host "Backup criado em: $backupDir"
Write-Host "Relatório gravado em: $reportPath"
Write-Host "Total de arquivos processados: $($files.Count)"
Write-Host "Total de arquivos modificados: $($report.Count)"

# fim do script
