# UI Defect Closure

The certified root cause was the HTTP/UI adapter selecting `confirmed` for a closed `NotReady` result.

The controller now selects `not-ready`; the Blade presents “L’annonce n’est pas encore prête” and excludes all confirmation copy. Applied and AlreadyApplied retain success, DependencyUnavailable retains its prior mapping, and HTTP 200 remains the established NotReady contract.

Residual defect `NotReady -> Publication confirmée`: **CLOSED**. No residual risk of the same reduction remains open.
