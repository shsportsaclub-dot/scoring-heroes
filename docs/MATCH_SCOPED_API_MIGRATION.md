# Match-scoped API integration — 2026-10-26

The private local API now uses `SCORER_SIGNING_SECRET` (32+ bytes), `MATCH_EVENT_DIRECTORY` (absolute private directory), and an `X-Match-Id` header. `Authorization: Bearer <signed match token>` is required for GET and POST. A trusted authenticated backend must issue tokens using `ScorerAccess::issue`; never generate them in the public browser. A scorer may create revision 1; only admin role may submit corrections. Journal writes use an exclusive lock and enforce per-match idempotency, contiguous revisions, and monotonic sequence for new event IDs.

Tests to run in PHP: `php api/tests/scorer_access.php`, `php api/tests/match_event_journal.php`, `php api/tests/scorecard.php`, `php api/tests/over_timeline.php`, `php api/tests/live_view.php`.

**Migration warning:** The old `EVENT_JOURNAL_PATH`-based CLI publisher does not yet read the new match-partitioned `MATCH_EVENT_DIRECTORY`. Public viewer snapshots will NOT reflect new match-scoped submissions until a new publisher is implemented and tested. Do not enable the new API on a production server without coordinating migration, token issuance, revocation, monitoring, secure TLS, throttling and full tests. Current correction permission is role-based only; ownership and prior-event actor audit policy still need design.
