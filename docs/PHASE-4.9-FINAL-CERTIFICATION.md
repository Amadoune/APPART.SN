# Phase 4.9 — Account Status Lifecycle Final Certification

## Nature du jalon

La Final Certification consolide exclusivement les fondations certifiées de
la capacité Account Status Lifecycle. Elle n'introduit aucune implémentation,
aucun contrat, aucune migration et aucun comportement nouveau.

Le périmètre audité couvre :

```text
4.9A Discovery / Blueprint
→ 4.9A-R1 Decision Boundary
→ 4.9B Workflow Foundation
→ 4.9C Persistence Foundation et gates associés
→ 4.9P Historical Account Persistence Foundation
→ recertification ciblée 4.9C
→ 4.9D Runtime Composition
→ 4.9E Runtime Orchestration
→ 4.9F Event Contract
→ 4.9G Event Transport
→ 4.9H Event Routing
→ 4.9I Delivery Consumption
→ 4.9J Outbox Compatibility et amendements
→ 4.9K Atomic Event Integration
→ 4.9L HTTP Runtime
```

## Matrice de consolidation

| Frontière | Garantie consolidée | Verdict |
|---|---|---|
| Gouvernance | `IdentityAccess` est l'owner exclusif du statut `Active ↔ Suspended` | SATISFAIT |
| Workflow | fonction pure, états et actions fermés, refus déterministes | SATISFAIT |
| Effets aval | rôles, sessions, Credentials, Verification et Consent restent hors du lifecycle | SATISFAIT |
| Persistance lifecycle | journal 041 versionné, concurrence optimiste et résultats fermés | SATISFAIT |
| Compte historique | piste 4.9P GO FINAL; Snapshot V1 et Repository gelés | SATISFAIT |
| Coexistence | existence/version historique séparées du statut/version lifecycle | SATISFAIT |
| Runtime Composition | singletons paresseux, alias uniques, bootstrap sans effet | SATISFAIT |
| Orchestration | Inspection, Workflow et Persistance séquencés sans redécision | SATISFAIT |
| Event Contract | faits Account Status versionnés, fermés et facts-only | SATISFAIT |
| Event Transport | enveloppe déterministe, restauration fermée | SATISFAIT |
| Event Routing | destination certifiée sans reconstruction du fait | SATISFAIT |
| Delivery Consumption | consommation aval sans réécriture du statut | SATISFAIT |
| Outbox | owner `IdentityAccess`, compatibilité générique et destination routée préservées | SATISFAIT |
| Atomicité | lifecycle et Outbox partagent une transaction unique | SATISFAIT |
| HTTP | deux routes fermées; autorisation explicite; dépendance applicative unique | SATISFAIT |
| Runtime Health | `Healthy — 58 capacités` | SATISFAIT |
| Non-régression | campagnes Unit, PostgreSQL, Architecture, PHPStan et Pint vertes | SATISFAIT |

## Migrations stabilisées

| Migration | Owner | Responsabilité | Statut |
|---|---|---|---|
| `041_account_status_lifecycle_workflow` | IdentityAccess / Account Status | journal lifecycle | STABILISÉE |
| `042_historical_account_persistence` | IdentityAccess / Historical Account | agrégat Account historique | STABILISÉE |
| `043_identity_access_outbox_owner` | IdentityAccess / Outbox | livraison durable des faits | STABILISÉE |

Les migrations sont additives, distinctes et sans transfert de responsabilité.
La Final Certification ne les modifie pas.

## Baseline consolidée

```text
Feature / sécurité / Architecture ciblés : 16 / 16, 87 assertions
PostgreSQL Account Status + Outbox          : 52 / 52, 391 assertions
Architecture complète                      : 592 / 592, 44 523 assertions
Suite complète                             : 2 733 / 2 733, 52 475 assertions
Runtime Health                             : Healthy — 58 capacités
PHPStan                                    : 0 erreur
Pint                                       : PASS
git diff --check                           : PASS
```

Cette baseline est la campagne certifiée à l'issue de 4.9L. Le présent jalon
étant exclusivement documentaire, il ne revendique pas une nouvelle exécution.

## Inventaire des amendements et décisions négatives

Les décisions `NO GO` de 4.9C-R2, 4.9C-R3 et 4.9J-R2 restent des éléments
historiques fermés. Elles ont déclenché respectivement la piste 4.9P et
l'amendement 4.9J-R3; elles ne constituent plus un gate ouvert.

Les amendements certifiés ne sont pas des dérogations implicites. Ils font
partie de la chaîne normative et demeurent gelables avec leurs contrats.

## Périmètre de gel proposé

Le GO FINAL rendra gelés :

- les décisions 4.9A et la frontière 4.9A-R1 ;
- le Workflow et ses contrats ;
- les persistances 041 et 042, Snapshot V1 et leurs mappings ;
- la composition et l'orchestration Runtime ;
- les contrats Event, Transport, Routing et Delivery ;
- l'owner Outbox, la compatibilité générique et la migration 043 ;
- l'intégration atomique ;
- la frontière HTTP, ses deux routes et sa matrice de résultats ;
- la baseline `2 733 / 2 733` et le catalogue Healthy de 58 capacités.

Toute évolution après gel exigera un amendement versionné préalable, une
analyse d'impact, une campagne de non-régression et une nouvelle certification.

## Verdict soumis à l'autorité

```text
Phase 4.9 — Account Status Lifecycle
→ AUDIT FINAL COMPLET
→ GO FINAL PROPOSÉ

Implémentation nouvelle
→ AUCUNE

Gel de la capacité
→ EN ATTENTE DU PRONONCÉ GO FINAL
```

Le dossier ne prononce pas lui-même le gel. La Phase 4.9 deviendra
officiellement fermée et gelée uniquement après décision expresse de
l'autorité de certification.
