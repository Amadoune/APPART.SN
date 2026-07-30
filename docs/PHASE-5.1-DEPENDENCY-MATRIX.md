# Phase 5.1A — Dependency Matrix

## 1. Matrice

| Consommateur | Account | AccountRegistry | Status | Profile/Claims | Sessions | Recovery | Closure |
|---|---|---|---|---|---|---|---|
| Authentication | R status/verifications | R par id | R | R identity | C create | — | R |
| Sessions | référence ID | — | R availability | — | owner | — | R/invalidate |
| Recovery | C ChangePassword via use case | R par id | R | R identity | C invalidate | owner | R |
| Profile | seed lecture | R par id | R authorization | owner | R fresh auth | — | R |
| Claims | seed identité | aucune extension | — | owner | — | — | R policy |
| Closure | référence ID | R existence | R sans mutation | R retention contacts | C invalidate all | C revoke | owner |

Légende : R lecture par port ; C commande publique vers l'owner.

## 2. Dépendances externes

| Domaine | Usage | Interdiction |
|---|---|---|
| AdministrationAudit | actions sensibles et preuves | aucune écriture dans IAM |
| Notifications | messages sécurité/contact change | aucun secret dans event |
| Listing | conservation AccountId à Closure | aucune cascade |
| Leads | conservation/rétention owner | aucune suppression IAM |
| Reservations | conservation/obligations | aucune suppression IAM |
| Favorites | invalidation/purge future | pas de table join directe |
| Professionals | Account availability | ne lit pas SQL IAM |

Les capacités Notifications/Favorites ne sont pas encore construites. 5.1
définit des contracts consumers minimaux ou diffère leur activation ; il ne les
implémente pas.

## 3. Ordre des dépendances

```text
Contracts communs identity/time/idempotence
→ Persistence Profile/Claims + Session/Auth + Recovery + Closure
→ seed/cutover Profile Claims
→ Runtime propriétaire
→ Availability composition Status + Closure
→ Event/Delivery propriétaires
→ Atomic integration
→ HTTP
```

## 4. Interdictions

- SQL direct vers 041–043 ;
- `findByEmail` ajouté à AccountRegistry ;
- session ou closure encodée comme Account Status ;
- Profile event dans Account Status Outbox ;
- transaction ACID cross-domain ;
- dépendance Domain vers Laravel/PDO ;
- route 5.1 dans `AccountStatusHttpController` ;
- nouveau requirement ajouté aux 58 sans amendement.
