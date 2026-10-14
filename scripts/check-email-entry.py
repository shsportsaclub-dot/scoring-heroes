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
    with open("runtime-evidence/"+name+".png", "wb") as output:
        subprocess.run(["adb", "exec-out", "screencap", "-p"], stdout=output, check=True)

tap_text("runtime-evidence/welcome.xml", "Languages")
capture("registration")
root = ET.parse("runtime-evidence/registration.xml")
assert any("Email • 6-digit OTP" in n.get("text", "") for n in root.iter("node"))
tap_text("runtime-evidence/registration.xml", "Email • 6-digit OTP")
capture("email-webview")
root = ET.parse("runtime-evidence/email-webview.xml")
assert any("EMAIL / 6-DIGIT OTP" in n.get("text", "") for n in root.iter("node"))
print("PASS: native registration entry and email WebView opened. No email requested; inbox delivery not tested.")
