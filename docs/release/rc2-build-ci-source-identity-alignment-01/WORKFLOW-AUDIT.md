# Workflow Audit

The embedded workflow is pinned to R5 in its tag trigger, `CANDIDATE_TAG`, `RELEASE_CANDIDATE_ID` and historical `SOURCE_BASE_SHA`.

Its jobs, service, restore procedure, Unit/Feature/Architecture/Foundation/PostgreSQL gates, PHPStan, Pint, frontend build, artifact creation and upload semantics remain structurally intact.

Only identity fields need future change. The workflow is part of the immutable RC2 tree, so that change cannot be applied without a newly authorized materialization.
