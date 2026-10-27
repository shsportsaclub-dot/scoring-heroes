# Corrected over timeline (foundation)

`OverTimeline::project(events, innings)` groups deliveries by legal ball count, not by the last six deliveries. Wide and no-ball events remain in their actual over; a completed over has six legal deliveries, potentially more total events. Highest event revision wins.

Test with `php api/tests/over_timeline.php`.

Limits: Assumes one delivery event per ball and monotonic sequence; doesn't yet handle penalty-only events, forfeits, multiple dismissals, umpire correction of over length, or all unusual cricket law cases. UI integration with LiveMatchView is the next gate. The test has not been executed by this GitHub write.
