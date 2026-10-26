# Scoring Heroes — Integrated Product Master (2026-10-26)

## Product promise
One original sports ecosystem for viewers, scorers, athletes, clubs, academies, tournament organizers, fantasy players, media creators, buyers, retailers, manufacturers and officials. Do not clone CREX, Cricbuzz, Cricket Australia, Dream11, Instagram or OLX branding/UI/code.

## Modules (all in scope)
1. Cricket-first live match centre: live, scorecard, ball commentary, XI, squads, overs, graphs, H2H, team strength, insights, venue, highlights, news, review.
2. Real-time ingestion: licensed provider adapters, normalized ball events, corrections, idempotency, cache, WebSocket/SSE, timestamps, stale-match guard.
3. Professional scoring: teams, squads, toss, striker, bowler, extras, wickets, undo, innings, DLS where licensed/validated, super over, match result, offline recovery.
4. Tournament OS: registration, officials, sponsors, auction, fixtures, points, knockouts, awards, live broadcasting.
5. Player identity and career: verified profiles, provenance-aware import, records, academy tracking, permissions.
6. Fantasy: free-to-play team creation, credits, captain/vice-captain, configurable scoring, private leagues, rankings; legal review before any cash contest.
7. E-paper: match reports, editions, regional languages, tournament stories, PDF export, ad slots.
8. Social: player feed, reels, posts, stories, follows, comments, moderation, consent, reporting.
9. Classifieds: location-based new/used sporting equipment listings, seller verification, chat, abuse prevention.
10. E-commerce: catalog, cart, orders, payments, returns, logistics, customer support.
11. B2B: factory outlets, manufacturers, wholesale RFQs, quotations, custom kits, retailer/distributor dashboards.
12. Multisport: sport-specific rules, scorers, fixtures, standings, records and match centres.
13. Roles, search, notifications, audit, localization, privacy, analytics, observability.

## UI contract
Compact cricket-first dashboard, no unnecessary scroll, responsive mobile/desktop, top search and registration, functional 14-tab match centre, original cinematic navy/blue/red with restrained gold, realistic team logos when licensed. All buttons must have working states; no fake LIVE or placeholders presented as functional.

## Delivery gates
G0 inventory actual deployed source and secrets boundaries.
G1 design system + clickable functional UI acceptance.
G2 event model + provider adapter with tests.
G3 real match timing benchmark against CREX (manual observation for comparison only).
G4 scorer and match centre end-to-end.
G5 fantasy, social, media, commerce and B2B incremental modules.
G6 security, load, rights and legal checks.
G7 staging deploy, screenshots, JSON QA evidence; production only after approval and verified rollback.

## Non-negotiable
No CREX APK republishing, reverse engineering protected feeds, or unauthorized redistribution. Never claim sub-second updates without measured evidence. Never expose provider keys in frontend or Git.
