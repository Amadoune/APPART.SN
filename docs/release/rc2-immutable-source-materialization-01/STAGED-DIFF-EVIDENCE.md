# Staged Diff Evidence

This document is part of the candidate and records the pre-commit verification policy. Exact staged counts, stat, whitespace result and expected tree identity are recorded by the post-materialization external evidence after the index is built.

Required result before commit: staged paths exactly equal all `Include` rows in `STAGING-MANIFEST.md`; the single `Exclude` row is absent; cached diff check passes.
