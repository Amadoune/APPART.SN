# Circularity Audit

Both required conditions are true:

1. RC2 candidate contains `runtime.lock`, workflow and packaging script;
2. those files must change to identify RC2 rather than R5.

Therefore an edit would make `appart-sn-release-candidate-rc2` differ from the aligned source and would require a new commit and tag. This is the exact fail-fast condition in the mission.

Classification: **BUILD/CI SELF-IDENTITY CIRCULARITY**.
