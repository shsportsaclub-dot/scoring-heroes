#!/usr/bin/env python3
"""Scoring Heroes cricket catalogue: unattended, idempotent public-feed ingestion."""
import json, os, pathlib, datetime, hashlib, urllib.request, urllib.parse
ROOT=pathlib.Path(__file__).parent
DB=ROOT/"catalogue.json"
FEEDS=ROOT/"feeds.json"
def load(path,default):
    return json.loads(path.read_text(encoding="utf-8")) if path.exists() else default
def fetch(url):
    p=urllib.parse.urlparse(url)
    if p.scheme!="https": raise ValueError("Only HTTPS sources allowed")
    host=p.hostname or ""
    allowed=[h.strip().lower() for h in os.getenv("ALLOWED_FEED_HOSTS","").split(",") if h.strip()]
    if not allowed or host.lower() not in allowed: raise ValueError("Feed host not allowlisted: "+host)
    req=urllib.request.Request(url,headers={"User-Agent":"ScoringHeroesCatalogue/1.0"})
    with urllib.request.urlopen(req,timeout=20) as resp:
        if int(resp.headers.get("Content-Length","0"))>2_000_000: raise ValueError("Feed too large")
        body=resp.read(2_000_001)
        if len(body)>2_000_000: raise ValueError("Feed too large")
        return json.loads(body)
def normalize(item,source):
    name=str(item.get("competition","")).strip()
    country=str(item.get("country","")).strip()
    division=str(item.get("division","")).strip().lower()
    category=str(item.get("category","")).strip().lower()
    if not name or not country or division not in ("men","women") or category not in ("international","domestic","league"): return None
    season=str(item.get("season","")).strip()
    key="|".join((country.casefold(),category,division,name.casefold(),season.casefold()))
    return {"id":hashlib.sha256(key.encode()).hexdigest()[:20],"country":country,"category":category,"division":division,"competition":name,"season":season,"start":str(item.get("start","")),"end":str(item.get("end","")),"source":source,"status":"feed_unverified"}
def main():
    current=load(DB,{"records":[],"errors":[]})
    byid={r["id"]:r for r in current.get("records",[])}
    errors=[]
    feeds=load(FEEDS,{"feeds":[]}).get("feeds",[])
    for feed in feeds:
        try:
            data=fetch(feed["url"])
            if not isinstance(data,dict) or not isinstance(data.get("competitions"),list): raise ValueError("Expected JSON object with competitions array")
            for item in data["competitions"]:
                if not isinstance(item,dict): continue
                record=normalize(item,feed["url"])
                if record: byid[record["id"]]=record
        except Exception as exc: errors.append({"source":feed.get("url",""),"error":str(exc)[:250]})
    output={"updated_at":datetime.datetime.now(datetime.timezone.utc).isoformat(),"records":sorted(byid.values(),key=lambda r:(r["country"],r["category"],r["division"],r["competition"],r["season"])),"errors":errors}
    DB.write_text(json.dumps(output,indent=2,ensure_ascii=False)+"\n",encoding="utf-8")
    print(json.dumps({"records":len(byid),"feeds":len(feeds),"errors":len(errors)}))
if __name__=="__main__": main()
