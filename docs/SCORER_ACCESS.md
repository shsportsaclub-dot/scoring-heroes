# Match-scoped scorer access (foundation)

`ScorerAccess::issue` signs a short-lived match/user/role token with HMAC-SHA256; `verify` rejects tampering, expiry and mismatched match; `authorizeEvent` requires admin role for revisions >1.

Test: `php api/tests/scorer_access.php`. Tested locally with PHP in this development turn.

**Not yet wired into the POST endpoint.** Existing `api/public/local.php` still uses a shared bearer secret. This module is not production authorization until issuance is restricted to authenticated staff, token revocation and rotation are implemented, event revision history is checked server-side, and a match-scoped journal partition is used. Do not place signing secrets in browser JavaScript or commit them. Production also needs CSRF review (if cookies used), rate limiting, audit, role assignment, and replay controls.
