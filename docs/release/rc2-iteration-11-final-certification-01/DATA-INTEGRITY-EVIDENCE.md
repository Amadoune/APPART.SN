# Data Integrity Evidence

Read-only PostgreSQL inspection of `appart_rebuild` confirms:

- 1 Generation, 1 Active;
- 1 RC2 projection in Active;
- 1 terminal Public Geography decision;
- 1 Public Media decision for the productive collection;
- 1 Search decision;
- 1 ContentSeo snapshot;
- 1 canonical Property and 1 Promotion command;
- 1 completed Queue item for the submission.

All identities align with the Listing and source watermark. No duplicate owner decision or orphan projection was observed.

Database isolation remains certified: application `appart_rebuild`, automated PostgreSQL tests `appart_test`. `PostgreSqlTestEnvironment` requires a test-only database name, requires a declared application database, and refuses equality before every reset.
