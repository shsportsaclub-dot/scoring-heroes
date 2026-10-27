# Ball event integration

BallEventReducer.php is a pure reducer for normalized delivery events. It is not yet connected to a live provider.

Required event fields: event_id (stable string), sequence (integer), revision (integer >=1), type=delivery, innings (positive integer), runs (nonnegative total including extras), wickets (0-2), legal_delivery (boolean).

It handles duplicates and corrections by event_id/revision, and counts overs from legal deliveries. Providers must supply actual ball IDs and legality; never infer legality from displayed overs.

Test commands:
- php -l api/src/BallEventReducer.php
- php api/tests/ball_events.php

Pending: licensed provider ball feed, durable append-only event storage, live push, batting/bowling figures, reconciliation and staging execution. No live data or runtime test success is claimed.
