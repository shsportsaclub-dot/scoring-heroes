#!/usr/bin/env python3
"""Generate batch 01 of 50 separate PNG cricket-ball theme concept assets.
Not verified official association crests. Preserve originals separately.
"""
from PIL import Image, ImageDraw, ImageFont
import json, pathlib, math
names="""BCCI|Andhra Cricket Association|Arunachal Cricket Association|Assam Cricket Association|Baroda Cricket Association|Bihar Cricket Association|Chhattisgarh State Cricket Sangh|Cricket Association of Bengal|Cricket Association of Mizoram|Cricket Association of Pondicherry|Delhi and District Cricket Association|Goa Cricket Association|Gujarat Cricket Association|Haryana Cricket Association|Himachal Pradesh Cricket Association|Hyderabad Cricket Association|Jammu and Kashmir Cricket Association|Jharkhand State Cricket Association|Karnataka State Cricket Association|Kerala Cricket Association|Madhya Pradesh Cricket Association|Maharashtra Cricket Association|Manipur Cricket Association|Meghalaya Cricket Association|Mumbai Cricket Association|Nagaland Cricket Association|Odisha Cricket Association|Punjab Cricket Association|Rajasthan Cricket Association|Saurashtra Cricket Association|Sikkim Cricket Association|Tamil Nadu Cricket Association|Telangana Cricket Association|Tripura Cricket Association|Uttar Pradesh Cricket Association|Uttarakhand Cricket Association|Vidarbha Cricket Association|Railways Sports Promotion Board|Services Sports Control Board|India Cricket|Australia Cricket|England Cricket|Pakistan Cricket|Bangladesh Cricket|New Zealand Cricket|South Africa Cricket|Sri Lanka Cricket|West Indies Cricket|Afghanistan Cricket|Ireland Cricket""".split("|")
out=pathlib.Path("assets/ball-theme/batch-01")
out.mkdir(parents=True,exist_ok=True)
fontpath="/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
font=ImageFont.truetype(fontpath,42)
small=ImageFont.truetype(fontpath,23)
rows=[]
for i,name in enumerate(names,1):
    slug="".join(c.lower() if c.isalnum() else "_" for c in name).strip("_")
    W=1024
    im=Image.new("RGBA",(W,W),(0,0,0,0))
    px=im.load()
    cx=cy=512;r=472
    hue=(i*53)%360
    import colorsys
    a=tuple(int(v*255) for v in colorsys.hsv_to_rgb(hue/360,.77,.60))
    b=tuple(int(v*255) for v in colorsys.hsv_to_rgb(((hue+38)%360)/360,.75,.32))
    for y in range(38,986):
      dy=y-cy
      dxmax=int(math.sqrt(max(0,r*r-dy*dy)))
      for x in range(cx-dxmax,cx+dxmax+1):
        dx=x-cx
        t=(dx+dy+950)/1900
        light=max(.45,1-.55*((dx+130)**2+(dy+170)**2)/(r*r*2))
        base=a if t<.55 else b
        px[x,y]=(*(int(v*light) for v in base),255)
    d=ImageDraw.Draw(im)
    d.ellipse((40,40,984,984),outline=(225,183,84),width=9)
    # left curved cream seam: discrete stitches along curved ball longitude
    for j in range(90,930,24):
      x=int(290-75*math.sin((j-90)/840*math.pi))
      d.ellipse((x-9,j-13,x+9,j+13),fill=(255,245,218),outline=(155,113,58),width=2)
      d.ellipse((x+25-9,j-13,x+25+9,j+13),fill=(255,245,218),outline=(155,113,58),width=2)
    d.ellipse((380,290,855,760),outline=(238,194,96),width=12)
    d.ellipse((402,312,833,738),outline=(255,240,185),width=3)
    code=f"IN{i:03d}" if i<=39 else f"INT{i-39:03d}"
    initials="".join(w[0] for w in name.split() if w.lower() not in {"of","and","the"})[:5].upper()
    bbox=d.textbbox((0,0),initials,font=font)
    d.text((620-(bbox[2]-bbox[0])/2,440),initials,font=font,fill=(255,223,135),stroke_width=2,stroke_fill=(70,39,20))
    # name rendered as metadata, not squeezed onto small ball
    d.text((455,650),code,font=small,fill=(255,235,179))
    filename=f"{i:02d}_{slug}.png"
    im.save(out/filename,optimize=True)
    rows.append({"id":code,"name":name,"file":f"assets/ball-theme/batch-01/{filename}","style":"SCORING_HEROES_BALL_CONCEPT","official_logo_verified":False})
(out/"manifest.json").write_text(json.dumps(rows,indent=2)+"\n")
assert len(rows)==50
print("Generated 50 individual transparent PNGs")
