# Phase 5.4 — Risk Register

## Statut

`DISCOVERY — MITIGATIONS DOCUMENTAIRES UNIQUEMENT`

| ID | Catégorie | Risque | Niveau | Mitigation documentaire avant code |
|---|---|---|---|---|
| R-5.4-01 | Fonctionnel | contact envoyé vers un annonceur non autorisé | Élevé | certifier contactabilité, mandat et résultat fail-closed |
| R-5.4-02 | Fonctionnel | double réservation d'une même fenêtre | Élevé | définir autorité de disponibilité, verrou et résultat de conflit |
| R-5.4-03 | Fonctionnel | favori visible par un autre Account | Élevé | auto-scope IAM obligatoire et Query privée fermée |
| R-5.4-04 | Architecture | accès direct aux lifecycles existants | Élevé | Boundary Audit et ports publics avant toute composition |
| R-5.4-05 | Architecture | Favorites absorbé par IAM | Élevé | décision d'ownership explicite avant Contracts |
| R-5.4-06 | Cohérence | Listing supprimé mais référence conservée | Moyen | politique Missing/Ineligible et nettoyage asynchrone certifié |
| R-5.4-07 | Cohérence | disponibilité observée devenue obsolète | Élevé | observedAt, version et contrôle atomique owner Reservation |
| R-5.4-08 | Transaction | tentative d'ACID cross-domain | Élevé | commits indépendants, intents durables et compensation |
| R-5.4-09 | Transaction | mutation réussie sans Outbox locale | Élevé | gate d'atomicité owner-local et rollback PostgreSQL |
| R-5.4-10 | Runtime | dépendance externe indisponible | Élevé | availability fail-closed, diagnostics internes fermés |
| R-5.4-11 | Runtime | Provider monolithique ou binding ambigu | Moyen | providers owner-scoped, singleton/lazy et tests de résolution |
| R-5.4-12 | Concurrence | deux créations ou ajouts appliqués | Élevé | idempotence durable, optimistic locking, preuve concurrente |
| R-5.4-13 | Replay | double contact ou double réservation | Élevé | identité/checksum canoniques, AlreadyApplied, quarantaine |
| R-5.4-14 | Idempotence | même intent avec payload divergent | Élevé | DivergentIntent fermé, aucune réinterprétation |
| R-5.4-15 | Sécurité | spam et amplification de delivery | Élevé | rate limiting non PII, anti-abus certifié, retry borné |
| R-5.4-16 | Sécurité | fuite de coordonnées ou contenu libre | Élevé | minimisation, chiffrement à qualifier, logs sans payload |
| R-5.4-17 | Sécurité | énumération de Listing, lead ou réservation | Élevé | réponses indistinguables et ressources auto-scopées |
| R-5.4-18 | Confidentialité | consentement insuffisant ou non prouvable | Élevé | owner, finalité, rétention et preuve minimale contractualisés |
| R-5.4-19 | Gouvernance | réutilisation implicite d'une capacité gelée | Élevé | amendement versionné avant toute modification |
| R-5.4-20 | Gouvernance | trois voies certifiées comme un bloc incomplet | Moyen | certification indépendante par voie puis freeze consolidé |
| R-5.4-21 | PostgreSQL | index insuffisant pour disponibilité/pagination | Moyen | plans de requête et index owner-local dans Persistence gate |
| R-5.4-22 | PostgreSQL | FK cross-domain introduite par commodité | Élevé | tests Architecture/migrations interdisant FK et cascade |
| R-5.4-23 | Delivery | canal externe non idempotent | Élevé | clé fournisseur stable, ledger local, reprise/quarantaine |
| R-5.4-24 | Gouvernance | Events candidats traités comme certifiés | Élevé | catalogue explicitement non normatif jusqu'à Event gate |

## Seuils

- **Élevé** : bloque la gate concernée tant qu'une décision normative et une
  preuve adaptée ne sont pas disponibles.
- **Moyen** : exige une mitigation contractualisée et un test dans la
  Foundation concernée.
- **Faible** : peut être accepté avec justification et observation.

## Risques résiduels acceptables au Discovery

Les inconnues de nommage, de schéma et de technologie sont acceptables parce
qu'aucune implémentation n'est ouverte. Les ambiguïtés d'ownership ou de
frontière publique ne sont pas acceptables pour ouvrir Contracts Foundation.

