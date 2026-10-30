#!/usr/bin/env python3
"""Generate 1000 original, editable SH placeholder crest SVGs and identity manifest.
Not representations of verified existing teams; replace with licensed originals later.
"""
import csv, hashlib, json, pathlib, re
ROOT=pathlib.Path(__file__).resolve().parents[1]/"assets"/"logo-library"
ROOT.mkdir(parents=True,exist_ok=True)
rows=[]
for n in range(1,1001):
    code=f"SH{n:04d}"
    name=f"Team {n:04d}"
    h=(n*137)%360
    h2=(h+115)%360
    badge=["shield","round","hex","diamond","banner"][n%5]
    # Original neutral emblems, not falsely identified as any real team.
    shapes={
      "shield":'M128 14 L223 49 L211 167 Q194 222 128 246 Q62 222 45 167 L33 49 Z',
      "round":'M128 16 A112 112 0 1 1 127.9 16 Z',
      "hex":'M72 22 H184 L239 128 L184 234 H72 L17 128 Z',
      "diamond":'M128 9 L246 128 L128 247 L10 128 Z',
      "banner":'M31 23 H225 V204 L128 247 L31 204 Z'
    }
    svg=f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" role="img" aria-label="{name} placeholder crest">
<title>{name} — Scoring Heroes editable placeholder</title>
<defs><linearGradient id="g" x2="1" y2="1"><stop stop-color="hsl({h},70%,35%)"/><stop offset="1" stop-color="hsl({h2},75%,22%)"/></linearGradient></defs>
<path d="{shapes[badge]}" fill="url(#g)" stroke="#f7d36a" stroke-width="8"/>
<circle cx="128" cy="107" r="49" fill="none" stroke="#fff" stroke-width="4" opacity=".7"/>
<path d="M94 107h68M128 74v66" stroke="#fff" stroke-width="4" opacity=".65"/>
<text x="128" y="182" font-family="Arial,sans-serif" font-size="31" font-weight="bold" text-anchor="middle" fill="#fff">{code}</text>
<text x="128" y="207" font-family="Arial,sans-serif" font-size="12" text-anchor="middle" fill="#fff">SCORING HEROES</text></svg>'''
    path=ROOT/f"{code}.svg"
    path.write_text(svg,encoding="utf-8")
    rows.append({"asset_id":code,"display_name":name,"team_code":code,"category":"UNASSIGNED","sport":"UNASSIGNED","logo_path":f"assets/logo-library/{code}.svg","primary_hue":h,"secondary_hue":h2,"verification_status":"GENERATED_PLACEHOLDER","original_uploaded":False})
(ROOT/"manifest.json").write_text(json.dumps(rows,indent=2)+"\n",encoding="utf-8")
with (ROOT/"manifest.csv").open("w",newline="",encoding="utf-8") as f:
    w=csv.DictWriter(f,fieldnames=list(rows[0]));w.writeheader();w.writerows(rows)
print(f"Generated {len(rows)} SVG placeholders, manifest.json and manifest.csv in {ROOT}")
