# Scorer entry validation (development)

ScorerEventValidator validates normalized ball input, prevents duplicate revision numbers, requires consecutive sequence numbers for new balls, and permits explicit correction via event_id plus next revision. It rejects negative runs and malformed wicket/extras values.

Corrections are append-only and replayed by highest revision. A scorer may correct a prior ball without deleting journal history.

Limitations: no scorer account authentication or role permissions, no multi-writer transaction lock across read/validate/append, no complete ICC playing conditions, no formal undo command (undo requires an explicit replacement event), no scorer UI. Do not expose the CLI write path publicly. Do not deploy concurrent writers until atomic transactions are implemented.
