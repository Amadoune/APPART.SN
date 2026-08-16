# Root Cause

`DeterministicPublicationReviewExperience::approve()` correctly reduced Projection `NotReady` to `PublicationReviewExperienceStatus::NotReady`.

The defect was downstream: `PublicationReviewExperienceController::approve()` always passed `confirmed` to the renderer. `render()` did not reject `NotReady`, and the Blade fallback rendered the success panel. A correct closed Application result was therefore mislabeled by the HTTP/UI adapter.

Owner: Publication Review HTTP/UI result reduction.
