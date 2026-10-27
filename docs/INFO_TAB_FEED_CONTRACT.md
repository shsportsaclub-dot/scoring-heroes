# INFO-first match centre feed contract

Tab order is INFO → PLAYING XI → DREAM11 → LIVE → SCORECARD → GRAPHS, followed by secondary sections.

The INFO tab consumes six optional top-level fields: `squads`, `weather`, `toss`, `pitchReport`, `headToHead`, and `prediction`. It also consumes optional `infoSources` keyed by the same field names, e.g.:

```json
{
  "id": "match-1",
  "title": "Example Match",
  "teams": [{"name":"A","score":"0/0"},{"name":"B","score":"—"}],
  "squads": {"A": ["Player 1", "Player 2"], "B": ["Player 3"]},
  "weather": null,
  "toss": "A won toss and elected to bat",
  "pitchReport": null,
  "headToHead": null,
  "prediction": null,
  "infoSources": {
    "toss": {"verified": true, "updatedAt": "2026-10-26T10:00:00Z"}
  }
}
```

Do not set `verified:true` without a trustworthy provider or scorer/official confirmation. The UI treats absent fields as unavailable, not as zero. Predictions must have a named model/method and confidence disclosure before production. Weather should have a location, forecast time, source, and validity period; pitch reports require a credited observer and observation time. Squads must be separated from confirmed Playing XI.

**Status:** frontend rendering contract and UI code committed; provider integration, validation, and runtime visual QA remain pending.
