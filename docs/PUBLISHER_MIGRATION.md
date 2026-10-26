# Match-scoped publisher migration

The CLI publisher `api/bin/publish-local-view.php` now reads the same `MATCH_EVENT_DIRECTORY` used by the authenticated scorer API. Set `LOCAL_MATCH_ID` to the exact match identifier and `PUBLIC_SNAPSHOT_PATH` to a distinct private absolute snapshot path; optionally set `LOCAL_MATCH_TITLE`. The publisher refuses to overwrite the snapshot when there are no events.

```sh
MATCH_EVENT_DIRECTORY=/srv/private/matches LOCAL_MATCH_ID=match1 PUBLIC_SNAPSHOT_PATH=/srv/private/public/match1.json php api/bin/publish-local-view.php
php api/tests/scorer_viewer_e2e.php
```

Route public read endpoint to `api/public/public-local-view.php` and configure the same snapshot path. The browser viewer can poll `/api/v1/public-local-view` once routing exists. Each match currently requires its own publisher and configured public endpoint; dynamic multi-match routing is not implemented. The snapshot always says `unverified`.

**No server scheduler or deployment was configured by this change.** CLI publishing must run after event writes, for example through a trusted queue worker or scheduler. The test fixture is committed but must be run in PHP runtime before asserting PASS.
