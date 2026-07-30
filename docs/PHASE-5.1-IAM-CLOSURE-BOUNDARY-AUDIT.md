# A-5.1-IAM-CLOSURE-01 — Closure Boundary Audit

## 1. Suspended n'est pas Closed

| Dimension | Suspended | Closed |
|---|---|---|
| origine | décision administrative/status | volonté utilisateur ou procédure de clôture |
| autorité | Account Status Lifecycle | Account Closure |
| réversibilité | Reactivated par autorité | Reopened selon cooling-off/policy |
| rôles | révoqués par comportement Account existant | pas de mutation Account ; accès neutralisé |
| sessions | non modélisées dans Status | invalidation obligatoire |
| rétention | aucune décision d'effacement | classification et échéance obligatoires |
| événements | status.suspended/reactivated V1 | catalogue Closure distinct |
| HTTP | admin suspend/reactivate | self-service/admin closure séparé |

Assimiler fermeture à suspension perdrait la finalité, la preuve de demande,
la politique de rétention, les sessions et les effets privacy. C'est interdit.

## 2. Closed, Deleted, Anonymized

### Closed

État métier durable. Le compte n'est plus utilisable. Les données restent
disponibles aux seules finalités de rétention, contentieux, fraude, audit et
réouverture autorisée.

### Deleted

Absence physique. Elle n'est possible qu'après extinction de toutes les
références/obligations ou après stratégie de tombstone. Elle ne peut pas être
déclenchée dans la transaction de fermeture.

### Anonymized

PII rendue irréversiblement non attribuable. Un pseudonyme ou un chiffrement
réversible ne suffit pas. L'AccountId peut rester comme référence technique
seulement si la réidentification n'est plus raisonnablement possible selon la
politique validée.

## 3. Réouverture

Réouverture autorisable uniquement si :

- état Closed, pas ErasurePending/Completed ;
- délai de rétention/cooling-off valide ;
- identité et contrôle de possession réétablis ;
- aucune interdiction administrative ;
- Account Status n'est pas contourné ;
- sessions recréées, jamais restaurées ;
- audit et événements distincts.

## 4. Politique de rétention

| Classe | Traitement à la fermeture |
|---|---|
| credential/session | sessions détruites immédiatement ; credential conservé inaccessible jusqu'à décision erasure |
| Profile/contacts | masqués, accès restreint, échéance de rétention |
| roles/consents | preuve historique conservée selon obligation |
| Listings/Leads/Reservations | owner conserve sa donnée et applique sa propre rétention |
| Audit | preuve append-only minimisée, jamais cascade delete |
| sécurité/fraude | legal hold possible et tracé |

Les durées exactes relèvent de Privacy/Legal et doivent être versionnées avant
implémentation.

## 5. Impacts cross-domain

| Domaine | Référence conservée | Effet Closure |
|---|---|---|
| Listing | actor/advertiser stable ID | nouvelles mutations interdites ; publication policy séparée décide retrait |
| Lead | advertiser/visitor evidence | delivery future bloquée ou redirigée selon owner |
| Reservation | actor/customer stable ID | nouvelles demandes interdites ; obligations existantes conservées |
| Favorites | collection privée | accès bloqué ; purge selon rétention Favorites |
| Audit | ActorId | conservation de la preuve, affichage minimisé |
| Professionals | relation Account/Professional | accès équipe bloqué, Professional owner décide continuité |
| Notifications | destination Profile | suppressions transactionnelles, messages légaux seulement |

Closure ne peut écrire directement dans aucun de ces domaines. Ils consomment
un événement/reader Closure puis appliquent leur propre commande.

## 6. Snapshot et migrations

- Snapshot V1 reste round-trip identique.
- 041 continue à porter seulement Active/Suspended.
- 042 conserve Historical Account.
- 043 continue à porter seulement l'owner Outbox Account Status certifié.
- le Closure store futur utilise une migration additive distincte ;
- aucune FK cascade cross-domain ;
- aucune suppression physique lors de close.

## 7. Runtime, HTTP et Outbox

| Surface gelée | Conservation |
|---|---|
| Runtime Health 58 | aucun requirement Closure implicite |
| Provider Status | aucun binding modifié |
| routes suspend/reactivate | inchangées |
| Event V1/serializer | inchangés |
| Outbox 043 | aucun type Closure ajouté |
| public projection | aucun statut Closure déduit sans contrat |

Le futur Closure Runtime/HTTP/Outbox est une tranche propriétaire séparée.
