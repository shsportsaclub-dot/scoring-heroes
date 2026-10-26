# Public local viewer read-only snapshot gate

A distinct public read-only endpoint is now implemented: `GET /api/v1/public-local-view`. Configure web-server routing to `api/public/public-local-view.php`. It **never** accepts writes or a scorer token.

Flow: authenticated scorer POST to private local endpoint → private JSONL journal → trusted server-side CLI `php api/bin/publish-local-view.php` → atomic private JSON snapshot → public GET → browser viewer polls every 5 seconds.

Set `EVENT_JOURNAL_PATH` and `PUBLIC_SNAPSHOT_PATH` to **distinct absolute paths outside web root**. Run publisher on an appropriate server scheduler after new events; do not expose CLI or journal paths. The snapshot omits unrecognized top-level fields and is always labeled `unverified`; if older than 180 seconds, `stale=true`. Viewer polling is not equivalent to push delivery.

Test: `php api/tests/public_snapshot.php`, `php api/tests/live_view.php`, `php api/tests/scorecard.php`. These tests require an actual PHP runtime and have not been executed as part of the GitHub write.

Before production: secure scorer identity and match permissions; implement proper revision authorization and idempotency; add multi-match partitioning, field-level privacy review, request throttling, concurrency testing, real current-over rules and licensed provider feeds.
