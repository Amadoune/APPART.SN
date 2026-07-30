# Phase 4.8 — Place Lifecycle Risk Matrix

| Risque | Impact | Prévention | Verdict si non résolu |
|---|---|---|---|
| `Merged` n'est pas reconnu terminal par le métier | cycle réversible ambigu | décision métier écrite avant R1 | NO GO 4.8A-R1 |
| fusion d'une source désactivée non arbitrée | matrice incomplète | confirmer explicitement la transition | NO GO 4.8A |
| cible modifiée entre lecture et commit | fusion vers une cible invalide | contexte versionné et stratégie atomique certifiée | NO GO Persistence |
| chaîne de fusion autorisée | résolution récursive et instable | cible active et non fusionnée obligatoire | NO GO Workflow |
| fusion entre types ou pays | corruption sémantique du référentiel | invariants fermés dans le contexte | NO GO Workflow |
| réécriture des références aval | violation d'ownership | faits seulement; chaque consommateur décide localement | NO GO Routing |
| projection publique utilisée comme autorité | décision sur donnée dérivée | source propriétaire Geography uniquement | NO GO Orchestration |
| désactivation assimilée à une suppression | perte d'historique | conservation d'identité et distinction des états | NO GO Persistence |
| renommage absorbé dans le lifecycle | périmètre composite | renommage et aliases restent exclus | NO GO 4.8A |
| détails géographiques excessifs dans l'événement | fuite et couplage | payload minimal et matrice de confidentialité | NO GO Event |
| owner Outbox Geography supposé | collision de schéma | audit owner avant toute migration | NO GO Outbox |
| journal et Outbox séparés | fait perdu ou fantôme | transaction unique | NO GO Atomicity |
| traitement en masse via HTTP | atomicité et diagnostics ambigus | endpoint unitaire seulement après certification | NO GO HTTP |
| contrat gelé incompatible | régression inter-capacité | amendement versionné préalable | STOP 4.8 |
| Owner métier non nommé | absence d'autorité de décision | nomination écrite avant R1 | NO GO 4.8A-R1 |
