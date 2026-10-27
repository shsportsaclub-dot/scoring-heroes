# Live match projection foundation

LiveMatchProjector combines normalized delivery events with ScorecardProjector to produce innings totals, a current-over strip and simple event descriptions. It is intended as an intermediate backend transformation, not as official ball-by-ball commentary.

Run:
php -l api/src/LiveMatchProjector.php
php api/tests/live_projection.php

Limitations: does not yet track strike rotation, partnership, session breaks, multi-innings active batter selection, DLS, official commentary or provider latency. Requires a licensed normalized ball event feed and server integration. Do not show synthesized commentary as official.
