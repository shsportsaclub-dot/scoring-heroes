"""OTP-free smoke test: welcome, native home, cricket and Back."""
import re
import subprocess
import time
import xml.etree.ElementTree as ET

def capture(name):
    subprocess.run(["adb", "shell", "uiautomator", "dump", "/sdcard/screen.xml"], check=True, timeout=45)
    subprocess.run(["adb", "pull", "/sdcard/screen.xml", "runtime-evidence/" + name + ".xml"], check=True, timeout=30)
    subprocess.run(["adb", "shell", "screencap", "-p", "/sdcard/screen.png"], check=True, timeout=30)
    subprocess.run(["adb", "pull", "/sdcard/screen.png", "runtime-evidence/" + name + ".png"], check=True, timeout=30)
    return list(ET.parse("runtime-evidence/" + name + ".xml").iter("node"))

def tap(nodes, label):
    node = next((n for n in nodes if label in n.get("text", "")), None)
    assert node is not None, "Missing UI control: " + label
    x1, y1, x2, y2 = map(int, re.findall(r"\d+", node.get("bounds", "")))
    subprocess.run(["adb", "shell", "input", "tap", str((x1+x2)//2), str((y1+y2)//2)], check=True, timeout=20)

home = None
for attempt in range(5):
    nodes = capture("welcome-attempt-" + str(attempt))
    if any("LIVE MATCH CENTRE" in n.get("text", "") for n in nodes):
        home = nodes
        break
    if any("Languages" in n.get("text", "") for n in nodes):
        tap(nodes, "Languages")
    time.sleep(2)

assert home is not None, "Native Home did not open without OTP; inspect welcome-attempt screenshots"
assert any("Cricket Live" in n.get("text", "") for n in home), "Cricket entry missing on Home"
print("PASS: Welcome to native sports Home without OTP.")
tap(home, "Cricket Live")
time.sleep(3)
nodes = capture("cricket-viewer")
assert any("CRICKET LIVE" in n.get("text", "") for n in nodes), "Cricket viewer missing"
subprocess.run(["adb", "shell", "input", "keyevent", "4"], check=True, timeout=20)
time.sleep(2)
nodes = capture("home-after-back")
assert any("LIVE MATCH CENTRE" in n.get("text", "") for n in nodes), "Android Back did not restore native Home"
print("PASS: Cricket viewer and Back navigation without OTP.")
