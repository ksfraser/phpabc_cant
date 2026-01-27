param(
  [string]$TunesDir = "tunes",
  [switch]$Overwrite
)

$ErrorActionPreference = "Stop"

$root = Resolve-Path $TunesDir

$cmd = @("php", "bin/abc-from-jpg-stubs-cli.php", "--tunes-dir", $root.Path)
if ($Overwrite) { $cmd += "--overwrite" }

Write-Host ("Running: " + ($cmd -join " "))
& $cmd[0] $cmd[1..($cmd.Length-1)]
