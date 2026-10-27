# Scorer to Viewer preview bridge

New integration: ScorerService validates normalized delivery events, persists them to a private append-only journal, and creates a viewer projection from the same history. No public scoring write endpoint exists; the writer is CLI-only until proper scorer authentication, authorization and rate limiting are implemented.

Write (staging):
  MATCH_EVENTS_PATH=/private/events php api/bin/score-event.php demo-match /private/input-ball.json

Read (staging, only if the PHP routing and private path are configured):
  GET /api/public/live.php?match_id=demo-match

Smoke test:
  php -l api/src/ScorerService.php
  php api/tests/scorer_flow.php

The read endpoint returns a projection envelope, not the web/index.html match schema. A dedicated frontend mapper is still required. No polling, WebSocket, provider ball feed, or production deployment is claimed. Only expose staging viewer data after privacy and tenancy checks.
