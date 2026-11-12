// Independent Sports Media Engine — pilot validation core
// No external scraping or publishing without licensed source adapters.
export function normalizeEvent(event) {
  if (!event || !event.sport || !event.date || !Array.isArray(event.teams) || event.teams.length !== 2)
    throw new Error("Missing sport/date/two teams");
  const teams=event.teams.map(x=>String(x).trim());
  if (!teams[0] || !teams[1] || teams[0].toLowerCase()===teams[1].toLowerCase())
    throw new Error("Invalid team identity");
  return {...event, teams, sport:String(event.sport).toLowerCase(), sources:event.sources||[]};
}
export function verifyForPublishing(input) {
  const e=normalizeEvent(input);
  const issues=[];
  if (!e.sources.some(s=>s.url && s.verified && s.rights !== "prohibited")) issues.push("No verified factual source");
  if (!e.score || !e.result) issues.push("Missing score or result");
  if (!e.brand || !e.brand.masterAssetId || !e.brand.sha256) issues.push("Official brand asset not locked");
  if (!Array.isArray(e.photos) || !e.photos.some(p=>p.playerId && p.verifiedIdentity && p.licensed && p.assetUrl))
    issues.push("No identity-verified licensed player photo");
  return {event:e, publishable:issues.length===0, issues};
}
export function mediaJobs(input) {
  const check=verifyForPublishing(input);
  if (!check.publishable) return {status:"blocked",issues:check.issues,jobs:[]};
  return {status:"ready_for_render",jobs:["social-poster","epaper","reel-mp4"].map(type=>({type,event:check.event,requiresOutputQA:true}))};
}
