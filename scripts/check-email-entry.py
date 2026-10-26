"""Check native email registration entry without sending any email."""
import re
import subprocess
import time
import xml.etree.ElementTree as ET

def tap_text(file, text):
    nodes = ET.parse(file).iter("node")
    node = next((n for n in nodes if text in n.get("text", "")), None)
    if node is None:
        raise AssertionError("Missing UI control: " + text)
    x1, y1, x2, y2 = map(int, re.findall("[0-9]+", node.get("bounds", "")))
    subprocess.run(["adb", "shell", "input", "tap", str((x1+x2)//2), str((y1+y2)//2)], check=True)

def capture(name):
    time.sleep(2)
    subprocess.run(["adb", "shell", "uiautomator", "dump", "/sdcard/screen.xml"], check=True)
    subprocess.run(["adb", "pull", "/sdcard/screen.xml", "runtime-evidence/"+name+".xml"], check=True)
    subprocess.run(["adb", "shell", "screencap", "-p", "/sdcard/screen.png"], check=True)
    subprocess.run(["adb", "pull", "/sdcard/screen.png", "runtime-evidence/"+name+".png"], check=True)

tap_text("runtime-evidence/welcome.xml", "Languages")
capture("registration")
root = ET.parse("runtime-evidence/registration.xml")
assert any("Email • 6-digit OTP" in n.get("text", "") for n in root.iter("node"))
tap_text("runtime-evidence/registration.xml", "Email • 6-digit OTP")
capture("email-webview")
root = ET.parse("runtime-evidence/email-webview.xml")
assert any("EMAIL / 6-DIGIT OTP" in n.get("text", "") for n in root.iter("node"))
print("PASS: native registration entry and email WebView opened. No email requested; inbox delivery not tested.")


# Back returns to native registration; use the bottom Home tab.
subprocess.run(["adb", "shell", "input", "keyevent", "4"], check=True)
capture("after-email-back")
root = ET.parse("runtime-evidence/after-email-back.xml")
home = next((n for n in root.iter("node") if "Home" in n.get("text", "")), None)
assert home is not None, "Native Home tab missing after email WebView Back"
x1, y1, x2, y2 = map(int, re.findall("[0-9]+", home.get("bounds", "")))
subprocess.run(["adb", "shell", "input", "tap", str((x1+x2)//2), str((y1+y2)//2)], check=True)
capture("native-sports-home")
assert any("Cricket Live" in n.get("text", "") for n in ET.parse("runtime-evidence/native-sports-home.xml").iter("node")), "Sports Home did not open"
wanted = {"Cricket Live", "Football Live", "Kabaddi Live", "Badminton Live", "Volleyball Live"}
seen = set()
for attempt in range(8):
    capture("sports-home")
    root = ET.parse("runtime-evidence/sports-home.xml")
    seen.update(n.get("text", "") for n in root.iter("node") if n.get("text", "") in wanted)
    if wanted <= seen:
        break
    subprocess.run(["adb", "shell", "input", "swipe", "540", "1800", "540", "650", "400"], check=True)
assert wanted <= seen, "Missing sports: " + str(wanted - seen)
for attempt in range(8):
    capture("sports-home")
    root = ET.parse("runtime-evidence/sports-home.xml")
    if any(n.get("text", "") == "Football Live" for n in root.iter("node")):
        break
    subprocess.run(["adb", "shell", "input", "swipe", "540", "650", "540", "1800", "400"], check=True)
tap_text("runtime-evidence/sports-home.xml", "Football Live")
capture("football-webview")
root = ET.parse("runtime-evidence/football-webview.xml")
assert any("FOOTBALL LIVE" in n.get("text", "") for n in root.iter("node"))
print("PASS: all five APK sport entries and Football viewer route opened; production scoring not tested here.")
