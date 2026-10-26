# Scorer and viewer browser pilot

- `web/scorer-demo.html` is an offline practice scorer. Runs, wides, no-balls, byes, leg-byes, and a bowled wicket create normalized events. Undo removes the last practice event. Copy JSON for manual review. It does **not** submit to the server and does not persist data.
- `web/viewer-demo.html` is a read-only polling UI. It polls a **same-origin public** JSON endpoint every five seconds. Polling stops when the page is hidden. It never requests or stores the shared scorer bearer token.
- `/api/v1/public-local-view` is an illustrative route only and has **not** been implemented. The existing `/api/v1/local-view` is private and requires bearer authentication; do not expose its token in browser code.
- The existing CLI projection and private API can be exercised separately in an isolated staging environment.

## Next integration gates
1. Authenticated scorer sessions with match-scoped roles and revision permissions.
2. Separate public, read-only projected cache endpoint with bounded payload and freshness metadata.
3. Provider and local scorer source distinction, true current-over reconstruction, verified match state.
4. End-to-end PHP/browser tests and deployment approval.

Do not call this a production real-time service.
