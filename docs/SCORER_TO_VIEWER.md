# Local scorer to viewer payload — foundation

The pipeline is: append validated delivery to EventJournal → project corrected event stream using ScorecardProjector → build read-only frontend-shaped JSON with LiveMatchView → publish using an authenticated, authorized server component (not yet built).

To inspect local projection:
```sh
EVENT_JOURNAL_PATH=/private/path/events.jsonl php api/bin/export-live-view.php
php api/tests/scorecard.php
php api/tests/live_view.php
```

**Not production-ready.** The exporter is CLI-only and does not expose a public write endpoint. No authentication, match ownership, scorer role checks, authorization, anti-replay, websocket, or publishing pipeline exists yet. Names currently use IDs. The current-over ball strip is a last-six-deliveries approximation, not a rules-accurate over; partnership and session fields are deliberately not invented. UI must not call this LIVE unless freshness and provenance are verified.
