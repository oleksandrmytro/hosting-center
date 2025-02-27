$domainsDir = Join-Path -Path $PSScriptRoot -ChildPath "domains"
$pendingDomainsFile = Join-Path -Path $domainsDir -ChildPath "pending_domains.json"
$processedDomainsFile = Join-Path -Path $domainsDir -ChildPath "processed_domains.json"
$ipCounterFile = Join-Path -Path $domainsDir -ChildPath "ip_counter.txt"
$hostsFile = "C:\Windows\System32\drivers\etc\hosts"

# Create domains directory if it doesn't exist
if (-not (Test-Path $domainsDir)) {
    New-Item -ItemType Directory -Path $domainsDir | Out-Null
}

# Create processed domains file if it doesn't exist
if (-not (Test-Path $processedDomainsFile)) {
    Set-Content -Path $processedDomainsFile -Value "[]"
}

# Initialize or load the current IP counter
if (-not (Test-Path $ipCounterFile)) {
    # Start from 127.0.0.2
    Set-Content -Path $ipCounterFile -Value "2"
}

Write-Host "Domain watcher started. Press Ctrl+C to exit."
Write-Host "Watching for domain registrations in $pendingDomainsFile"

# Load processed domains
$processedDomains = @{}
if (Test-Path $processedDomainsFile) {
    Get-Content $processedDomainsFile | ForEach-Object {
        if ($_.Trim()) {
            $domain = ($_ | ConvertFrom-Json).domain
            $processedDomains[$domain] = $true
        }
    }
}

while ($true) {
    # Check if pending domains file exists
    if (Test-Path $pendingDomainsFile) {
        $changed = $false
        
        # Process each line in the file
        Get-Content $pendingDomainsFile | ForEach-Object {
            if ($_.Trim()) {
                try {
                    $domainData = $_ | ConvertFrom-Json
                    $domain = $domainData.domain
                    
                    # Використовуємо IP з файлу, а не генеруємо новий
                    $currentIP = $domainData.ip
                    
                    # Skip if already processed
                    if (-not $processedDomains.ContainsKey($domain)) {
                        Write-Host "Adding domain: $domain with IP: $currentIP"
                        
                        # Add to hosts file with admin privileges
                        Start-Process powershell -Verb RunAs -ArgumentList "-Command Add-Content -Path '$hostsFile' -Value '`n$currentIP $domain' -Force"
                        
                        # Mark as processed
                        $processedDomains[$domain] = $true
                        Add-Content -Path $processedDomainsFile -Value $_
                        $changed = $true
                    }
                } catch {
                    Write-Host "Error processing domain entry: $_"
                }
            }
        }
        
        # Clear the pending file after processing
        if ($changed) {
            Clear-Content -Path $pendingDomainsFile
        }
    }
    
    # Wait before checking again
    Start-Sleep -Seconds 5
}