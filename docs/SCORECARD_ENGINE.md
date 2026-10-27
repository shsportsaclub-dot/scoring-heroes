# Scorecard projector v0

Pure PHP normalized ball-event projector. Computes innings runs/wickets/overs, batter runs/balls/4s/6s/strike rate, bowler conceded runs/wickets/overs/economy. Deduplicates events and applies the highest revision. Includes a focused test fixture.

This is a foundation, not yet a full official cricket laws engine. Not implemented: overthrow attribution, penalty allocation nuance, retired-hurt treatment, innings forfeits, non-striker run-outs, substitutions, full partnership calculation, batter strike rotation, match result or provider-specific dismissal semantics. Those require explicit schema and rules validation.

Execute:
php -l api/src/ScorecardProjector.php
php api/tests/scorecard.php

Do not feed currentMatches summary objects into this projector. Connect only a licensed ball-by-ball feed after mapping and validating fields.
