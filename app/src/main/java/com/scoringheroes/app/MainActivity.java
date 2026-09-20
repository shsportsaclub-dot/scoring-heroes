package com.scoringheroes.app;
import android.app.*; import android.os.*; import android.webkit.*; import android.content.*; import android.net.Uri;
public class MainActivity extends Activity {
 private WebView web; private final String BASE="https://scoringheroes.com/";
 @Override public void onCreate(Bundle b){super.onCreate(b);setContentView(R.layout.activity_main);web=findViewById(R.id.web);WebSettings s=web.getSettings();s.setJavaScriptEnabled(true);s.setDomStorageEnabled(true);s.setMediaPlaybackRequiresUserGesture(false);web.setWebViewClient(new WebViewClient(){@Override public boolean shouldOverrideUrlLoading(WebView v,WebResourceRequest r){Uri u=r.getUrl();if("scoringheroes.com".equals(u.getHost()))return false;startActivity(new Intent(Intent.ACTION_VIEW,u));return true;}});bind(R.id.home,BASE+"service-hub-v2/platform/mobile/");bind(R.id.discover,BASE+"service-hub-v2/platform/mobile/?screen=discover");bind(R.id.score,BASE+"service-hub-v2/scorer-pad/");bind(R.id.community,BASE+"service-hub-v2/platform/mobile/?screen=community");bind(R.id.profile,BASE+"join.php");web.loadUrl(BASE+"service-hub-v2/platform/mobile/");}
 private void bind(int id,String url){findViewById(id).setOnClickListener(v->web.loadUrl(url));}
 @Override public void onBackPressed(){if(web.canGoBack())web.goBack();else super.onBackPressed();}
}