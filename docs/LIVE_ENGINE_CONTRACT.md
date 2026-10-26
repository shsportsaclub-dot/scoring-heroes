# Live Match Engine Contract

## Event envelope
```json
{
  "event_id": "provider:match:innings:over:ball:revision",
  "match_id": "canonical-match-id",
  "provider": "licensed-source",
  "event_type": "ball|score_correction|innings_change|status_change|result",
  "source_timestamp": null,
  "received_at": "ISO-8601 UTC",
  "published_at": null,
  "revision": 1,
  "payload": {},
  "provenance": {"license": "required", "source_event_id": "id"}
}
```

## Processing
Ingest licensed data -> validate schema -> deduplicate by source event/revision -> persist append-only event -> update materialized match state -> cache -> push to subscribed clients -> record publish time. A correction must supersede previous derived totals without erasing audit history. Match state must be server-authoritative.

## Latency measurements
Record source_timestamp when trustworthy, received_at, normalized_at, pushed_at, client_rendered_at. Compare equivalent ball IDs and revisions, not screen screenshots taken at unrelated times. Report p50/p95/p99 and missing-event rates. Provider cadence sets a lower bound on freshness.

## Minimum APIs
GET /api/v1/matches?status=live
GET /api/v1/matches/{id}
GET /api/v1/matches/{id}/scorecard
GET /api/v1/matches/{id}/events?cursor=
GET /api/v1/matches/{id}/latency
GET /api/v1/health
WS /live?match_id= (authenticated as appropriate)

## QA acceptance
- duplicate ball does not add runs twice
- correction replays safely
- wicket, extras, 4/6 and innings changes update scorecard and commentary
- stale match not shown LIVE
- reconnect catches up via cursor
- no exposed API credentials
- UI shows loading/error/empty states honestly
- match status and result derived from official feed and validated rules
