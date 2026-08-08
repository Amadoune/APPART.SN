# Risk Register

| Risque | Maîtrise | État |
|---|---|---|
| Message dupliqué | identité et contrainte unique | maîtrisé |
| Replay divergent | checksum et DivergentMessage | maîtrisé |
| Double claim | FOR UPDATE SKIP LOCKED | maîtrisé |
| Retry infini | borne à dix | maîtrisé |
| Rupture transaction externe | savepoint local | maîtrisé |
| Source non autorisée | garde Architecture Delivery-only | maîtrisé |

