# Format-specific pitch analysis contract

Pitch analysis is scoped to the exact **venue + cricket format** (T20, ODI, Test, T10, The Hundred, etc.). Never mix formats when computing historical averages. Prefer comparable match conditions (women/men, competition level, season, day/night, surface and innings length); disclose filters, time range, sample size and data provider. Avoid asserting strong predictions on tiny samples.

Suggested JSON field `pitchAnalysis`:
```json
{
  "format": "T20",
  "sampleSize": 24,
  "firstInningsAverage": 168,
  "suitability": {"batters":72,"pacers":61,"spinners":43},
  "insight": "Illustrative example only: batting-friendly with some early pace assistance.",
  "verified": false
}
```
The numbers above are illustrative **only** and must not be served as genuine venue statistics. Suitability metrics are independent 0–100 estimates, **not probabilities of winning** and need not sum to 100. Use red (low), yellow (medium), green (high). A model must be calibrated against known historical outcomes before assigning these scores; publish methodology, uncertainty and data timestamp. If historical venue-format samples or credible pitch observations are missing, omit the estimates; the UI shows unavailable. Separate factual pitch report from predictive inference.

UI support added to `web/index.html` INFO tab. Backend historical venue aggregation, model calibration, provider permissions and visual/runtime QA remain pending.
