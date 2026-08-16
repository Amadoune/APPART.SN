# Final refresh materialization

Le refresh terminal reçoit `terminalPlaceId` et l'identité causale déterministe du mutation event. Il réutilise le même hierarchy reader, le même assembler V2 et le même writer que l'initial.

Mutation :

- Rename : recompute de chaque terminal affecté;
- Disable/Merge : décision durable Unavailable, sans substitution automatique;
- Enable : Available uniquement si la chaîne complète est valide;
- toute autre mutation publique certifiée suit la même réévaluation.

Le consumer pagine les décisions V2 dont revisionVector contient le Place muté, par `place_id ASC`. Chaque terminal devient une intention indépendante dérivée de `sourceEventId + terminalPlaceId + refresh-contract-v1`. Rejouer une page produit AlreadyApplied pour les terminaux déjà traités; aucun checkpoint global supplémentaire.

Enabled/Disabled/Merged réutilisent le transport lifecycle existant. PlaceRenamed doit être admis comme nouveau type/payload dans cette même outbox atomique Geography et son consumer existant : extension de contrat déjà décidée, pas nouvelle Foundation.
