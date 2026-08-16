# UI NotReady Residual Audit

## Closed questions

**A. Does the defect still exist in current code?** Yes. `DeterministicPublicationReviewExperience::approve()` returns `PublicationReviewExperienceStatus::NotReady`, while `PublicationReviewExperienceController::render()` handles only Forbidden, NotFound, Conflict and DependencyUnavailable as failures. `approve()` always requests view mode `confirmed`; the Blade renders “Publication confirmée”.

**B. Is it observable on the current RC2 Listing?** No. The productive RC2 source is Ready and the current public projection/read model exist.

**C. Can it mask a future real NotReady result?** Yes. The unchanged default reduction can render the confirmed view for a future NotReady projection.

**D. Is it blocking Iteration 11 certification?** No. It did not alter the authoritative stores, and current RC2 readiness/public runtime are independently proven.

## Classification

**NON-BLOCKING RESIDUAL DEFECT.** Owner: Publication Review HTTP/UI result reduction. It requires a separate explicitly opened correction gate; no correction is made here.
