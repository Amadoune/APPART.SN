# Decision Time Authority

L’instant normatif candidat est le `publishedAt` autoritatif déjà conservé par `PublishedPublicFacts`, identique au temps de la révision Published. Il est stable, rejouable et antérieur à tout catch-up.

Le matérialiseur devra copier cet instant dans `ContentSeoSourceDecision::decisionAt`. `ContentSeoSnapshotDecisionTimeReader` relira exactement cette valeur. Aucun `now()` n’est permis.

Cette règle reste applicable après fermeture de l’autorité canonical.
