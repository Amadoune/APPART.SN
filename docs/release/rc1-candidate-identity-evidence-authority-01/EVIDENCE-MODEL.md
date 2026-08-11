# Evidence Model

## Répartition des preuves

### Dans le commit candidat

- sources et migrations qualifiées ;
- lockfiles ;
- tests et documentation historiques ;
- Baseline Manifest pré-matérialisation ;
- Candidate Source et règles de sélection ;
- Migration Manifest ;
- présente décision d'autorité.

Le commit ne contient ni son SHA, ni le SHA de son futur tag.

### Dans le tag annoté

- nom autoritatif de la RC ;
- cible commit native ;
- tagger et date ;
- message d'attestation RC1.

### Dans le rapport d'exécution externe

- SHA de l'objet tag ;
- SHA du commit résolu ;
- SHA du tree ;
- parent R5 ;
- date d'exécution ;
- résultat de `git diff --cached --check` avant commit ;
- résultat de résolution tag→commit ;
- résultat final de `git status` et `git diff --check`.

## Statut du rapport

Le rapport post-matérialisation est une preuve externe. Il ne fait pas partie de la baseline et ne doit pas provoquer un second commit sur la source candidate.

Sa conservation relève de l'evidence custody. Tant qu'aucun repository officiel n'est résolu, la preuve peut être produite localement, mais sa publication durable reste une gate distincte de CI/Release Authority.

## Reproductibilité

Un vérificateur indépendant n'a pas besoin d'un SHA auto-déclaré dans la source. Il obtient le tag annoté, résout sa cible, calcule le tree et compare les valeurs au rapport externe. Toute divergence de référence, commit, tree ou parent produit un échec fermé.
