@echo off
powershell -Command "Start-Process powershell -ArgumentList '-ExecutionPolicy Bypass -File \"%~dp0host-domain-watcher.ps1\"' -Verb RunAs"