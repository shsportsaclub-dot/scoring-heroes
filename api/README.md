# Scoring Heroes — PHP CricketData ingestion foundation

Requires PHP 8.1+, curl, writable private var/ directory and licensed CricketData (CricAPI) key. The provider endpoint and field mapping must be checked against your subscribed plan before deployment.

## Run locally / staging
```sh
export CRICKETDATA_API_KEY='your-private-key'
export MATCH_CACHE_PATH='/private/path/matches.json'
php api/bin/sync.php
php -S 127.0.0.1:8080 -t api/public api/public/index.php
curl http://127.0.0.1:8080/api/v1/health
curl http://127.0.0.1:8080/api/v1/matches
```

Schedule sync through a secured cron with rate limits consistent with your provider contract. Do not place API keys or cache under public web root. Public API intentionally excludes ball-by-ball until a licensed event endpoint is integrated. Do not label an unverified feed as 1-second LIVE. The browser UI can consume a same-origin proxy returning a single normalized match object, not the match-list envelope. No production deployment has been performed.
