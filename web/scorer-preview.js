"use strict";
(function(){
const id=new URLSearchParams(location.search).get("match_id");
if(!id)return;
const notice=document.getElementById("notice");
if(!/^[a-zA-Z0-9_-]{1,80}$/.test(id)){notice.textContent="Invalid match ID";return;}
let count=-1,busy=false;
async function refresh(){
 if(document.hidden||busy)return;
 busy=true;
 try{
  const response=await fetch("/api/public/live.php?match_id="+encodeURIComponent(id),{cache:"no-store"});
  if(!response.ok)throw Error("HTTP "+response.status);
  const data=await response.json();
  if(!data.projection||!Number.isInteger(data.event_count))throw Error("Invalid snapshot");
  if(data.event_count!==count){
   count=data.event_count;
   const p=data.projection;
   const innings=p.scorecard.innings||{};
   const keys=Object.keys(innings).sort((a,b)=>Number(a)-Number(b));
   const key=keys[keys.length-1],current=innings[key];
   const batters=(p.scorecard.batters||[]).filter(x=>String(x.innings)===key);
   const bowlers=(p.scorecard.bowlers||[]).filter(x=>String(x.innings)===key);
   const bowler=bowlers[bowlers.length-1];
   match={id,title:"Scoring Heroes · "+id,status:"scorer_preview",teams:[{name:"Batting side",score:current?current.runs+"/"+current.wickets:"—"},{name:"Opposition",score:"—"}],inningsLabel:key?"Innings "+key:"No innings",overs:current?.overs??"0.0",breakLabel:"PREVIEW",crr:current?.legal_balls?(current.runs*6/current.legal_balls).toFixed(2):"—",session:"—",oversLeftToday:"—",summary:"Scorer preview, not an official live feed",currentOver:{number:p.current_over?.number??"—",balls:(p.current_over?.balls||[]).map(x=>x.token),runs:"—"},batters:batters.slice(-2).map(x=>({name:x.id,runs:x.runs,balls:x.balls,fours:x.fours,sixes:x.sixes,sr:x.strike_rate??"—"})),partnership:"—",lastWicket:"—",bowler:bowler?{name:bowler.id,wickets:bowler.wickets,runs:bowler.runs,overs:bowler.overs,economy:bowler.economy??"—"}:null,events:(p.commentary||[]).map(x=>({text:x.text})),scorecard:keys.map(k=>({name:"Innings "+k,runs:innings[k].runs,wickets:innings[k].wickets}))};
   render();
  }
  notice.textContent="SCORER PREVIEW · "+count+" stored events · refresh every 3 seconds while visible";
 }catch(error){notice.textContent="Scorer feed unavailable: "+error.message+". Display may be outdated.";}
 finally{busy=false;}
}
refresh();
setInterval(refresh,3000);
document.addEventListener("visibilitychange",()=>{if(!document.hidden)refresh();});
})();
