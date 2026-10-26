#!/usr/bin/env bash
set -euo pipefail
mkdir -p runtime-evidence
adb_ready() {
  for attempt in 1 2 3 4 5 6; do
    if timeout 12s adb shell getprop sys.boot_completed > runtime-evidence/boot-status.txt 2>&1 &&
       grep -q '^1' runtime-evidence/boot-status.txt; then return 0; fi
    echo "ADB recovery attempt $attempt" >> runtime-evidence/adb-recovery.log
    timeout 8s adb reconnect offline || true
    sleep 3
    timeout 15s adb wait-for-device || true
    sleep 5
  done
  return 1
}
echo 'Initial emulator connectivity check'
adb_ready
adb install -r app/build/outputs/apk/debug/app-debug.apk
adb shell am force-stop com.scoringheroes.app
adb shell monkey -p com.scoringheroes.app -c android.intent.category.LAUNCHER 1
echo 'App launch event sent; checking emulator health'
sleep 3
adb_ready
adb devices -l | tee runtime-evidence/adb-devices.txt
captured=0
for attempt in 1 2 3 4; do
  echo "Screenshot attempt $attempt"
  if timeout 20s adb shell screencap -p /sdcard/welcome.png &&
     timeout 20s adb pull /sdcard/welcome.png runtime-evidence/welcome.png &&
     test -s runtime-evidence/welcome.png; then
    captured=1
    break
  fi
  rm -f runtime-evidence/welcome.png
  adb_ready || true
done
test "$captured" -eq 1
timeout 40s adb shell uiautomator dump /sdcard/welcome.xml
timeout 20s adb pull /sdcard/welcome.xml runtime-evidence/welcome.xml
python3 -c 'import xml.etree.ElementTree as E; r=E.parse("runtime-evidence/welcome.xml"); assert list(r.iter("node")), "Empty UI hierarchy"'
python3 scripts/check-email-entry.py > runtime-evidence/ui-test.log 2>&1 || { cat runtime-evidence/ui-test.log; exit 1; }
cat runtime-evidence/ui-test.log
