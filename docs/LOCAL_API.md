# Local scorer API prototype

This is a **single-match, single-secret pilot**, not a production authentication system. Route only `/api/v1/local-events` and `/api/v1/local-view` to `api/public/local.php` via the server/router. Require HTTPS. Set `EVENT_JOURNAL_PATH` to an absolute writable path outside the public web root, `SCORER_INGEST_TOKEN` to a random secret of at least 32 characters, and optionally `LOCAL_MATCH_ID` and `LOCAL_MATCH_TITLE`. Do not commit tokens.

- `POST /api/v1/local-events` with `Authorization: Bearer <secret>`, `Content-Type: application/json`, and a normalized delivery JSON. Returns 202 after journal append.
- `GET /api/v1/local-view` with the same Authorization header. Returns projected match JSON for the frontend.

Example test event:
```json
{"type":"delivery","event_id":"m1-ball-1","revision":1,"sequence":1,"innings":1,"batter_id":"b1","bowler_id":"p1","bat_runs":4,"extra_runs":0,"extra_type":"none","legal_delivery":true}
```

**Security limitations:** This shared bearer secret is not appropriate for a browser frontend, because exposing it would permit anyone to write scores. Keep it server-side or in an authorized native scorer client only; production requires per-match roles, user identity, rate limiting, replay control, revision authorization, audit trails, and token rotation. There is no public viewer endpoint or live push yet. The GET endpoint is private. The append journal is not transactionally coupled with a materialized cache, and duplicate events remain in the journal but are deduplicated during projection. Validate with a local PHP runtime before deployment.
