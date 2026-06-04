$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$backend = Join-Path $root 'backend'
$frontend = Join-Path $root 'frontend'
$venvPython = Join-Path $backend 'venv\Scripts\python.exe'
$python = if (Test-Path $venvPython) { $venvPython } else { 'python' }
$xamppPhp = 'C:\xampp\php\php.exe'
$php = if (Get-Command php -ErrorAction SilentlyContinue) {
  'php'
} elseif (Test-Path $xamppPhp) {
  $xamppPhp
} else {
  throw 'PHP was not found. Install PHP or check that C:\xampp\php\php.exe exists.'
}

Write-Host 'Starting PHP API on http://127.0.0.1:8000'
$phpApi = Start-Process `
  -FilePath $php `
  -ArgumentList @('-S', '127.0.0.1:8000', '-t', 'public') `
  -WorkingDirectory $backend `
  -PassThru

Write-Host 'Starting AI API on http://127.0.0.1:5000'
$aiApi = Start-Process `
  -FilePath $python `
  -ArgumentList @('ai_app.py') `
  -WorkingDirectory $backend `
  -PassThru

Write-Host 'Starting Vue frontend'
$frontendApp = Start-Process `
  -FilePath 'npm' `
  -ArgumentList @('run', 'dev') `
  -WorkingDirectory $frontend `
  -PassThru

Write-Host ''
Write-Host 'All services are running.'
Write-Host 'Frontend:   http://127.0.0.1:5173'
Write-Host 'PHP health: http://127.0.0.1:8000/api/health'
Write-Host 'AI health:  http://127.0.0.1:5000/api/health'
Write-Host 'Close this window or press Ctrl+C to stop all services.'

try {
  while ($true) {
    if ($phpApi.HasExited) {
      throw 'PHP API stopped unexpectedly.'
    }

    if ($aiApi.HasExited) {
      throw 'AI API stopped unexpectedly.'
    }

    if ($frontendApp.HasExited) {
      throw 'Vue frontend stopped unexpectedly.'
    }

    Start-Sleep -Seconds 1
  }
}
finally {
  foreach ($process in @($phpApi, $aiApi, $frontendApp)) {
    if ($process -and -not $process.HasExited) {
      Stop-Process -Id $process.Id -Force
    }
  }
}
