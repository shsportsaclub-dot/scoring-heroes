#!/usr/bin/env python3
"""Read-only Sportmonks Cricket API coverage probe. Never place API tokens in an APK.
Usage: SPORTMONKS_TOKEN=... python3 scripts/probe-sportmonks-cricket.py
Requires only Python standard library. No keys or raw response dumps are logged.
"""
import json
import os
import sys
import urllib.parse
import urllib.request
from collections import Counter

BASE = "https://cricket.sportmonks.com/api/v2.0"
TOKEN = os.environ.get("SPORTMONKS_TOKEN", "").strip()

def fetch(path, params=None):
    query = dict(params or {})
    query["api_token"] = TOKEN
    url = BASE + path + "?" + urllib.parse.urlencode(query)
    req = urllib.request.Request(url, headers={"Accept": "application/json", "User-Agent": "ScoringHeroesCoverageProbe/1.0"})
    with urllib.request.urlopen(req, timeout=25) as response:
        data = json.load(response)
    if not isinstance(data, dict):
        raise ValueError("Unexpected provider response")
    return data

def main():
    if not TOKEN:
        print("NOT_CONFIGURED: Set SPORTMONKS_TOKEN on a trusted server; never in Android source or APK.")
        return 2
    results = {}
    for label, path in (("livescores", "/livescores"), ("fixtures", "/fixtures")):
        try:
            payload = fetch(path, {"per_page": 50})
            matches = payload.get("data") or []
            if not isinstance(matches, list):
                raise ValueError("Unexpected data field")
            leagues = Counter(str(m.get("league_id", "unknown")) for m in matches if isinstance(m, dict))
            results[label] = {
                "status": "OK",
                "returned_matches": len(matches),
                "league_ids": dict(leagues),
                "pagination": payload.get("meta", {}).get("pagination", {}) if isinstance(payload.get("meta"), dict) else {},
            }
        except Exception as exc:
            results[label] = {"status": "ERROR", "error_type": type(exc).__name__}
    print(json.dumps(results, indent=2, ensure_ascii=False))
    return 0 if all(v["status"] == "OK" for v in results.values()) else 1

if __name__ == "__main__":
    sys.exit(main())
