# Final fan-out model

Chaque intention terminale est identifiée déterministement par `sourceEventId + terminalPlaceId + refresh-contract-v1`. Pas de random.

Chaque write est une transaction locale. Crash : replay source depuis page 1; AlreadyApplied absorbe les succès; writer monotone arbitre stale/divergent.
