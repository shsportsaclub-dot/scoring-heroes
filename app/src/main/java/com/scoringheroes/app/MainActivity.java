package com.scoringheroes.app;

import android.app.*;
import android.os.*;
import android.graphics.Color;
import android.graphics.Typeface;
import android.graphics.drawable.GradientDrawable;
import android.content.*;
import android.net.Uri;
import android.view.*;
import android.webkit.*;
import android.widget.*;
import java.util.*;

public class MainActivity extends Activity {
  final String BASE="https://scoringheroes.com/";
  LinearLayout body,bottom; TextView title,sub;
  int white=Color.WHITE, muted=Color.rgb(180,194,208), navy=Color.rgb(3,17,31), panel=Color.rgb(10,31,49), red=Color.rgb(211,26,48);
  @Override public void onCreate(Bundle b){super.onCreate(b); showSplash();}
  TextView t(String s,int sp,boolean bold){TextView v=new TextView(this);v.setText(s);v.setTextColor(white);v.setTextSize(sp);v.setTypeface(null,bold?Typeface.BOLD:Typeface.NORMAL);v.setPadding(dp(16),dp(10),dp(16),dp(10));return v;}
  GradientDrawable bg(int c,float r){GradientDrawable g=new GradientDrawable();g.setColor(c);g.setCornerRadius(dp((int)r));g.setStroke(dp(1),Color.rgb(35,63,84));return g;}
  void showSplash(){
    final FrameLayout root=new FrameLayout(this);
    root.setBackground(new WelcomeBackground());

    LinearLayout content=new LinearLayout(this);
    content.setOrientation(LinearLayout.VERTICAL);
    content.setGravity(Gravity.CENTER_HORIZONTAL);
    content.setPadding(dp(24),dp(28),dp(24),dp(28));
    root.addView(content,new FrameLayout.LayoutParams(-1,-1));

    LinearLayout top=new LinearLayout(this);
    top.setGravity(Gravity.CENTER_VERTICAL);
    TextView country=t("🇮🇳  India ⌄",16,false);
    country.setPadding(0,dp(6),0,dp(6));
    TextView language=t("Languages ⌄",16,false);
    language.setGravity(Gravity.END);
    language.setPadding(0,dp(6),0,dp(6));
    top.addView(country,new LinearLayout.LayoutParams(0,dp(48),1));
    top.addView(language,new LinearLayout.LayoutParams(0,dp(48),1));
    content.addView(top,new LinearLayout.LayoutParams(-1,dp(56)));

    Space upper=new Space(this);
    content.addView(upper,new LinearLayout.LayoutParams(1,0,0.20f));

    TextView welcome=t("W E L C O M E   T O",27,true);
    welcome.setGravity(Gravity.CENTER);
    welcome.setLetterSpacing(0.08f);
    content.addView(welcome,new LinearLayout.LayoutParams(-1,dp(74)));

    View flare=new View(this);
    GradientDrawable flareBg=new GradientDrawable(GradientDrawable.Orientation.LEFT_RIGHT,
      new int[]{Color.TRANSPARENT,Color.rgb(0,116,255),Color.WHITE,Color.rgb(236,28,48),Color.TRANSPARENT});
    flare.setBackground(flareBg);
    LinearLayout.LayoutParams fp=new LinearLayout.LayoutParams(-1,dp(2));
    fp.setMargins(dp(48),0,dp(48),dp(18));
    content.addView(flare,fp);

    LinearLayout brand=new LinearLayout(this);
    brand.setOrientation(LinearLayout.VERTICAL);
    brand.setGravity(Gravity.CENTER);
    TextView mark=t("SH",58,true);
    mark.setGravity(Gravity.CENTER);
    mark.setTextColor(Color.rgb(225,30,55));
    TextView brandName=t("SCORING HEROES",31,true);
    brandName.setGravity(Gravity.CENTER);
    TextView assetGuard=t("APPROVED SH LOGO ASSET PENDING REPOSITORY BINARY",9,false);
    assetGuard.setTextColor(Color.rgb(122,146,166));
    assetGuard.setGravity(Gravity.CENTER);
    brand.addView(mark,new LinearLayout.LayoutParams(-1,dp(74)));
    brand.addView(brandName,new LinearLayout.LayoutParams(-1,dp(58)));
    brand.addView(assetGuard,new LinearLayout.LayoutParams(-1,dp(34)));
    content.addView(brand,new LinearLayout.LayoutParams(-1,-2));

    Space middle=new Space(this);
    content.addView(middle,new LinearLayout.LayoutParams(1,0,0.28f));

    PlatformSymbols platforms=new PlatformSymbols(this);
    content.addView(platforms,new LinearLayout.LayoutParams(-1,dp(112)));

    Space lower=new Space(this);
    content.addView(lower,new LinearLayout.LayoutParams(1,0,0.14f));

    TextView continueHint=t("Select language to continue",12,false);
    continueHint.setTextColor(muted);
    continueHint.setGravity(Gravity.CENTER);
    content.addView(continueHint,new LinearLayout.LayoutParams(-1,dp(42)));

    language.setOnClickListener(v->showMobileEntry());
    setContentView(root);
  }

  void showMobileEntry(){
    shell("MOBILE / OTP REQUEST","Secure authorized verification");
    hero("GET STARTED","Enter your mobile number to continue");
    card("India (+91)","Mobile number entry • server verification required",BASE+"join.php","MOBILE / OTP");
  }

  final class WelcomeBackground extends android.graphics.drawable.Drawable {
    final android.graphics.Paint p=new android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG);
    @Override public void draw(android.graphics.Canvas c){
      android.graphics.Rect b=getBounds();
      p.setShader(new android.graphics.LinearGradient(b.left,b.top,b.right,b.bottom,
        new int[]{Color.rgb(0,39,96),Color.rgb(1,16,43),Color.rgb(35,5,34),Color.rgb(115,0,10)},
        null,android.graphics.Shader.TileMode.CLAMP));
      c.drawRect(b,p); p.setShader(null);
      p.setStyle(android.graphics.Paint.Style.STROKE);
      for(int i=0;i<7;i++){
        p.setStrokeWidth(dp(i==0?2:1));
        p.setColor(i<3?Color.argb(115,0,108,255):Color.argb(100,220,10,30));
        float inset=dp(24+i*34);
        android.graphics.RectF r=new android.graphics.RectF(b.left-inset,b.top+dp(110+i*35),b.right+inset,b.bottom+dp(260+i*45));
        c.drawArc(r,198,132,false,p);
      }
      p.setStyle(android.graphics.Paint.Style.FILL);
    }
    @Override public void setAlpha(int a){p.setAlpha(a);}
    @Override public void setColorFilter(android.graphics.ColorFilter f){p.setColorFilter(f);}
    @Override public int getOpacity(){return android.graphics.PixelFormat.OPAQUE;}
  }

  final class PlatformSymbols extends View {
    final android.graphics.Paint p=new android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG);
    PlatformSymbols(Context c){super(c);}
    @Override protected void onDraw(android.graphics.Canvas c){
      super.onDraw(c); float w=getWidth(),cy=getHeight()*0.48f;
      drawAndroid(c,w*0.20f,cy,dp(25));
      drawApple(c,w*0.50f,cy,dp(26));
      drawWindows(c,w*0.80f,cy,dp(27));
    }
    void drawAndroid(android.graphics.Canvas c,float x,float y,float s){
      p.setColor(Color.rgb(123,224,28)); p.setStyle(android.graphics.Paint.Style.FILL);
      c.drawRoundRect(x-s*.70f,y-s*.35f,x+s*.70f,y+s*.65f,s*.18f,s*.18f,p);
      c.drawArc(x-s*.70f,y-s*.85f,x+s*.70f,y+s*.10f,180,180,true,p);
      p.setStrokeWidth(dp(2)); p.setStyle(android.graphics.Paint.Style.STROKE);
      c.drawLine(x-s*.45f,y-s*.70f,x-s*.68f,y-s*1.0f,p); c.drawLine(x+s*.45f,y-s*.70f,x+s*.68f,y-s*1.0f,p);
      p.setStyle(android.graphics.Paint.Style.FILL);
    }
    void drawApple(android.graphics.Canvas c,float x,float y,float s){
      p.setColor(Color.WHITE); p.setStyle(android.graphics.Paint.Style.FILL);
      c.drawOval(x-s*.72f,y-s*.55f,x+s*.10f,y+s*.70f,p); c.drawOval(x-s*.08f,y-s*.55f,x+s*.72f,y+s*.70f,p);
      p.setColor(navy); c.drawCircle(x+s*.70f,y-s*.20f,s*.30f,p);
      p.setColor(Color.WHITE); android.graphics.Path leaf=new android.graphics.Path();
      leaf.moveTo(x,y-s*.65f);leaf.quadTo(x+s*.12f,y-s*1.15f,x+s*.48f,y-s*1.18f);leaf.quadTo(x+s*.40f,y-s*.78f,x,y-s*.65f);c.drawPath(leaf,p);
    }
    void drawWindows(android.graphics.Canvas c,float x,float y,float s){
      p.setColor(Color.rgb(0,174,239)); float g=dp(2);
      c.drawRect(x-s,y-s,x-g,y-g,p);c.drawRect(x+g,y-s,x+s,y-g,p);c.drawRect(x-s,y+g,x-g,y+s,p);c.drawRect(x+g,y+g,x+s,y+s,p);
    }
  }

  void shell(String h,String s){
    LinearLayout root=new LinearLayout(this);root.setOrientation(LinearLayout.VERTICAL);root.setBackgroundColor(navy);
    LinearLayout top=new LinearLayout(this);top.setGravity(Gravity.CENTER_VERTICAL);top.setPadding(dp(12),dp(8),dp(8),dp(8));top.setBackgroundColor(Color.rgb(5,25,42));
    TextView logo=t("SH",24,true);logo.setTextColor(Color.rgb(230,31,55));top.addView(logo,new LinearLayout.LayoutParams(dp(52),dp(52)));
    LinearLayout names=new LinearLayout(this);names.setOrientation(LinearLayout.VERTICAL);title=t(h,19,true);title.setPadding(0,0,0,0);sub=t(s,10,false);sub.setTextColor(muted);sub.setPadding(0,0,0,0);names.addView(title);names.addView(sub);top.addView(names,new LinearLayout.LayoutParams(0,-2,1));
    String[] acts={"⌕","✉","●","☰"};for(String a:acts){TextView z=t(a,21,false);z.setGravity(Gravity.CENTER);top.addView(z,new LinearLayout.LayoutParams(dp(42),dp(48)));}
    root.addView(top,new LinearLayout.LayoutParams(-1,dp(68)));
    ScrollView sc=new ScrollView(this);body=new LinearLayout(this);body.setOrientation(LinearLayout.VERTICAL);body.setPadding(dp(14),dp(14),dp(14),dp(92));sc.addView(body);root.addView(sc,new LinearLayout.LayoutParams(-1,0,1));
    bottom=new LinearLayout(this);bottom.setGravity(Gravity.CENTER);bottom.setPadding(dp(4),dp(4),dp(4),dp(4));bottom.setBackgroundColor(Color.rgb(4,22,38));
    nav("⌂\nHome",()->showHome());nav("◎\nDiscover",()->showDiscover());nav("＋\nSCORE",()->open(BASE+"service-hub-v2/scorer-pad/","SCORER PAD"));nav("◉\nCricket",()->showCricket());nav("♙\nCommunity",()->showCommunity());
    root.addView(bottom,new LinearLayout.LayoutParams(-1,dp(72)));setContentView(root);
  }
  void nav(String label,Runnable r){TextView v=t(label,11,true);v.setGravity(Gravity.CENTER);v.setOnClickListener(x->r.run());bottom.addView(v,new LinearLayout.LayoutParams(0,-1,1));}
  void hero(String a,String b){
    LinearLayout c=new LinearLayout(this);c.setOrientation(LinearLayout.VERTICAL);c.setPadding(dp(4),dp(12),dp(4),dp(12));c.setBackground(bg(Color.rgb(8,35,57),20));
    TextView A=t(a,25,true);TextView B=t(b,13,false);B.setTextColor(muted);c.addView(A);c.addView(B);body.addView(c,new LinearLayout.LayoutParams(-1,-2));
  }
  void section(String s){TextView v=t(s.toUpperCase(),14,true);v.setTextColor(Color.rgb(152,202,232));v.setPadding(dp(2),dp(22),0,dp(8));body.addView(v);}
  void card(String name,String desc,String url,String screenName){
    LinearLayout c=new LinearLayout(this);c.setOrientation(LinearLayout.VERTICAL);c.setPadding(dp(2),dp(5),dp(2),dp(5));c.setBackground(bg(panel,16));
    TextView a=t(name,17,true),b=t(desc,12,false);b.setTextColor(muted);c.addView(a);c.addView(b);c.setOnClickListener(v->open(url,screenName));
    LinearLayout.LayoutParams p=new LinearLayout.LayoutParams(-1,-2);p.setMargins(0,0,0,dp(9));body.addView(c,p);
  }
  void showHome(){
    shell("SCORING HEROES","Grassroots to Global");
    hero("LIVE MATCH CENTRE","Live • Schedule • Fixtures • Upcoming • Results");
    section("Match");
    card("SCORING HEROES LIVE","SH-scored matches and live match centre",BASE,"LIVE MATCH CENTRE");
    card("International & Leagues","Authorized feed area • no fabricated scores",BASE,"INTERNATIONAL & LEAGUES");
    card("Start / Continue Scoring","Professional scorer pad with match controls",BASE+"service-hub-v2/scorer-pad/","SCORER PAD");
    section("Explore");
    card("Highlights & Replay","Match clips, replay and record room",BASE+"service-hub-v2/broadcast/studio-v10.php","REPLAY");
    card("Grounds & Bookings","Ground discovery, availability and booking",BASE+"service-hub-v2/platform/mobile/?screen=grounds","GROUNDS");
    card("Academy","Academy, attendance, fees and performance",BASE+"service-hub-v2/platform/mobile/?screen=academy","ACADEMY");
  }
  void showDiscover(){shell("DISCOVER","Sports ecosystem");hero("DISCOVER","Players • Teams • Grounds • Tournaments • Services");card("Auction","Tournament auction control",BASE+"service-hub-v2/auction/","AUCTION");card("Marketplace / OLX","Sports marketplace and used gear",BASE+"service-hub-v2/marketplace/","MARKETPLACE");card("Streaming","Broadcast and live production",BASE+"service-hub-v2/broadcast/studio-v10.php","STREAMING");card("Auto Advertising","Sponsor campaigns and event triggers",BASE+"service-hub-v2/advertising/","ADVERTISING");}
  void showCricket(){shell("CRICKET","Match intelligence");hero("CRICKET CONTROL","Score • Field • Review • Performance");card("Field View","Oval field animation and player positions",BASE+"service-hub-v2/field-animation/","FIELD VIEW");card("Technical Performance","Bowling, batting and fielding measurements",BASE+"service-hub-v2/technical-performance/","TECHNICAL PERFORMANCE");card("Player Status","Match, season and career status",BASE+"service-hub-v2/player-status/","PLAYER STATUS");card("Broadcast / DRS","Camera, replay, review and graphics control",BASE+"service-hub-v2/broadcast/studio-v10.php","BROADCAST / DRS");}
  void showCommunity(){shell("COMMUNITY","Connect around the game");hero("SPORTS COMMUNITY","Players • Teams • Academies • Fans");card("Player Profile / Login","WhatsApp OTP and Player ID",BASE+"join.php","PLAYER PROFILE");card("Commentary Studio","Text, voice and match commentary",BASE+"service-hub-v2/commentary/studio.php","COMMENTARY");card("Notifications","Match and account alerts",BASE+"service-hub-v2/platform/mobile/?screen=notifications","NOTIFICATIONS");}
  void open(String url,String name){
    LinearLayout root=new LinearLayout(this);root.setOrientation(LinearLayout.VERTICAL);root.setBackgroundColor(navy);
    LinearLayout bar=new LinearLayout(this);bar.setGravity(Gravity.CENTER_VERTICAL);TextView back=t("‹",34,true);back.setGravity(Gravity.CENTER);back.setOnClickListener(v->showHome());bar.addView(back,new LinearLayout.LayoutParams(dp(56),dp(56)));TextView n=t(name,17,true);bar.addView(n,new LinearLayout.LayoutParams(0,dp(56),1));root.addView(bar);
    WebView w=new WebView(this);WebSettings s=w.getSettings();s.setJavaScriptEnabled(true);s.setDomStorageEnabled(true);s.setMediaPlaybackRequiresUserGesture(false);s.setAllowFileAccess(false);w.setWebViewClient(new WebViewClient());w.loadUrl(url);root.addView(w,new LinearLayout.LayoutParams(-1,0,1));setContentView(root);
  }
  int dp(int n){return (int)(n*getResources().getDisplayMetrics().density+0.5f);}
}