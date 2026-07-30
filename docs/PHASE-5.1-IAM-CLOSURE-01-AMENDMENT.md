# A-5.1-IAM-CLOSURE-01 — Account Closure Frozen Boundary Amendment

## 1. Statut et objet

```text
A-5.1-IAM-01
→ NO GO CERTIFIÉ
→ FERMÉ

A-5.1-IAM-CLOSURE-01
→ OUVERT
```

L'amendement définit une fermeture métier réelle, distincte d'une suspension,
sans muter les capacités 4.9 gelées.

## 2. Décision d'architecture proposée

La fermeture est portée par une autorité additive **Account Closure**,
référencée par `AccountId`.

```text
Open → ClosureRequested → Closed
Closed → Reopened          (si politique et délai l'autorisent)
Closed → ErasurePending    (processus privacy séparé)
```

`Deleted` et `Anonymized` ne sont pas des états de compte interchangeables :

- **Closed** : état métier, accès interdit, données/références conservées selon
  rétention ;
- **Deleted** : résultat physique éventuel après extinction des obligations,
  jamais une transition HTTP immédiate ;
- **Anonymized** : transformation irréversible des PII après qualification
  juridique, distincte de Closed ;
- **Suspended** : sanction/mesure administrative réversible, sans volonté de
  fermeture et avec historique Account intact.

## 3. Priorité des décisions

Pour toute authentification ou action propriétaire future :

1. `Anonymized/ErasureCompleted` : identité non authentifiable ;
2. `Closed/ClosureRequested` selon policy : accès refusé ;
3. Account Status `Suspended` : accès refusé administrativement ;
4. Account Status `Active` : accès possible sous autres policies.

Une réouverture de Closure ne réactive jamais un Account suspendu. Les deux
autorités restent orthogonales.

## 4. Persistence et références

Le futur Closure store est additif et conserve :

- AccountId, state, version ;
- requested/closed/reopened timestamps ;
- actor, reason, cooling-off deadline ;
- rétention classifiée et legal hold ;
- session invalidation checkpoint ;
- erasure eligibility, sans exécuter l'effacement.

Snapshot V1 et migration 042 restent inchangés. Les références Listing, Lead,
Reservation, Favorites et Audit conservent le stable `AccountId`; aucune
cascade de suppression cross-domain n'est autorisée.

## 5. Sessions

La transition vers ClosureRequested/Closed doit :

- interdire la création de nouvelles sessions ;
- invalider toutes les sessions et recovery challenges ;
- révoquer refresh tokens/remember-me ;
- rester idempotente et observable ;
- ne pas dépendre d'une mutation `Account::suspend()`.

## 6. Événements et delivery

Un futur catalogue Closure distinct peut contenir des intentions telles que
requested, closed, reopened et erasure_eligible. Ces noms ne sont pas créés ni
certifiés ici.

Ils ne modifient jamais `AccountStatusEventType` V1, 043 ou son consumer. Un
Outbox Closure séparé est requis, sauf amendement futur explicite de
l'infrastructure générique.

## 7. Anonymisation et effacement

La fermeture peut être certifiée sans prétendre anonymiser Snapshot V1.
L'effacement/anonymisation irréversible exige :

```text
A-5.1-IAM-ERASURE-01
→ amendement Privacy/Erasure distinct
→ requis avant toute mutation/destruction de Historical Account
```

Cet amendement devra arbitrer rétention légale, audit, unicités, crypto-erasure,
PII cross-domain et preuve d'effacement. Il ne bloque pas l'état métier Closed,
mais bloque toute promesse d'effacement effectif.

## 8. Verdict proposé

```text
A-5.1-IAM-CLOSURE-01
→ GO PROPOSÉ
```
