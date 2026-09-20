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
    LinearLayout x=new LinearLayout(this);x.setOrientation(LinearLayout.VERTICAL);x.setGravity(Gravity.CENTER);x.setPadding(dp(28),dp(28),dp(28),dp(28));
    GradientDrawable g=new GradientDrawable(GradientDrawable.Orientation.TL_BR,new int[]{Color.rgb(2,23,42),Color.rgb(31,7,24)});x.setBackground(g);
    TextView mark=t("SH",52,true);mark.setGravity(Gravity.CENTER);mark.setTextColor(Color.rgb(225,30,55));x.addView(mark,new LinearLayout.LayoutParams(-1,dp(90)));
    TextView brand=t("SCORING HEROES",28,true);brand.setGravity(Gravity.CENTER);x.addView(brand);
    TextView tag=t("GRASSROOTS TO GLOBAL",13,true);tag.setTextColor(Color.rgb(175,205,225));tag.setGravity(Gravity.CENTER);x.addView(tag);
    setContentView(x);new Handler(Looper.getMainLooper()).postDelayed(()->showHome(),900);
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
  void card(String name,String desc,String url){
    LinearLayout c=new LinearLayout(this);c.setOrientation(LinearLayout.VERTICAL);c.setPadding(dp(2),dp(5),dp(2),dp(5));c.setBackground(bg(panel,16));
    TextView a=t(name,17,true),b=t(desc,12,false);b.setTextColor(muted);c.addView(a);c.addView(b);c.setOnClickListener(v->open(url,name));
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