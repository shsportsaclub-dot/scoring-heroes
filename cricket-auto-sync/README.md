# Scoring Heroes — Cricket Auto Sync (starter)
A self-running **GitHub Actions** worker scheduled every six hours (subject to GitHub scheduling delays). It works without ChatGPT being online.

## Current state
- Ingests **approved JSON HTTPS feeds** from `feeds.json`.
- Deduplicates competitions by country, category, division, competition name and season.
- Stores a source URL and marks new feed entries `feed_unverified`; does **not** claim official verification.
- Logs feed errors and keeps previously ingested records.
- Commits catalogue updates automatically using GitHub Actions.

## Setup
1. In repository Settings → Actions → General, allow workflows to have **Read and write permissions**.
2. Add repository variable `CRICKET_FEED_HOSTS` with approved hostnames separated by commas.
3. Edit `feeds.json` to add **authorized HTTPS JSON** feeds. Expected format:
```json
{"competitions":[{"country":"India","category":"league","division":"men","competition":"Example League","season":"2026","start":"2026-06-01","end":"2026-06-20"}]}
```
4. Run the workflow manually once from Actions, then let its schedule run.

## Important limits
Cricbuzz and CREX are **research references**, not configured machine-readable APIs here. Do not bypass access restrictions or assume scraping rights. Obtain a licensed feed or add official board feeds with permission. No feed hosts are configured by default, so automatic discovery is **not yet operational**. Google Sheets synchronization and Render deployment are **not yet implemented**. Historical first-edition years require source verification. This starter only provides unattended infrastructure once feeds are configured.
