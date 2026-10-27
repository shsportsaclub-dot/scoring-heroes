# Staging verification — Scoring Heroes Match API

## Preconditions
- PHP 8.1+ with curl extension
- CricketData subscription key, held only in server environment
- Private writable MATCH_CACHE_PATH outside web root
- HTTP rewrite routing to api/public/index.php
- Separate staging hostname; do not overwrite production

## Checks
```sh
php -l api/src/MatchStore.php
php -l api/src/CricketDataAdapter.php
php -l api/bin/sync.php
php -l api/public/index.php
php api/tests/smoke.php
CRICKETDATA_API_KEY="$CRICKETDATA_API_KEY" MATCH_CACHE_PATH="$MATCH_CACHE_PATH" php api/bin/sync.php
curl -fsS https://STAGING_HOST/api/v1/health
curl -fsS https://STAGING_HOST/api/v1/matches
curl -fsS 'https://STAGING_HOST/api/v1/matches?status=live'
```

## Acceptance
1. Provider key not logged or committed.
2. Stale cache (>180 seconds) changes live status to stale for both match-list and match-detail endpoints.
3. Live filter never returns stale match.
4. Invalid status returns HTTP 400, POST returns HTTP 405.
5. Match detail returns HTTP 404 for unknown ID.
6. Provider failure does not destroy last successful cache.
7. Compare ball event timestamp with source provider before claiming low latency.
8. Review match names, team score ordering and currentMatches availability against real subscription response.
9. Do not expose staging to users as a completed live-scoring product.

## Limitations
Current provider adapter supplies match summaries only. Ball events, scorecards, real-time streaming and social/commerce/fantasy remain future implementation tasks. No automated test execution or deployment is asserted by this checklist.
