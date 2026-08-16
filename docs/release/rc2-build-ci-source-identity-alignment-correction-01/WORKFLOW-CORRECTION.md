# Workflow Correction

Le workflow conserve intégralement ses jobs, suites, restore, PostgreSQL, PHPStan, Pint, frontend, timeouts, packaging et artifacts.

Seules quatre occurrences scalaires d'identité sont alignées : trigger tag, `SOURCE_BASE_SHA`, `CANDIDATE_TAG` et `RELEASE_CANDIDATE_ID`.

La structure YAML est inchangée. La validation statique du diff confirme que seules ces valeurs ont été substituées ; les guards du type annoté, de résolution exacte et d'ascendance restent présents.
