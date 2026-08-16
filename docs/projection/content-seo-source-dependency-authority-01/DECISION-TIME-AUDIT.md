# Decision Time Audit

`DecisionTimeReader` est lié à `ContentSeoSnapshotDecisionTimeReader`. Celui-ci relit le même snapshot et retourne exactement `snapshot.decisionAt`.

Le format persistant canonique est `Y-m-d\TH:i:s.uP`. `CertifiedPublicListingProjectionSource` exige l’égalité exacte entre ce temps et le `decisionAt` du snapshot déjà lu ; sinon `DecisionTimeDivergent`.

Aucun `now()` de secours n’existe ou n’est permis. Pour RC2, DecisionTime est Missing par conséquence de l’absence du snapshot, mais l’assemblage s’arrête auparavant à `ContentSeoMissing`.
