# Phase 5.4 — Dependency Matrix

## Statut

`DISCOVERY — DOCUMENTAIRE — AUCUNE DÉPENDANCE AUTORISÉE PAR CE DOCUMENT`

## 1. Matrice fonctionnelle

| Producteur | Consumer | Flux candidat | Condition d'autorisation | Flux interdit |
|---|---|---|---|---|
| Listing/Public Projection | ContactsLeads | décision de contactabilité | Reader public V1 certifié | Aggregate, Repository, SQL Listing |
| Professional Core/Profile | ContactsLeads | destinataire autorisé | résolution publique minimale | mandat, établissement ou contact interne |
| IdentityAccess | ContactsLeads | session/availability | contrat public certifié | Aggregate Account, rôles internes |
| ContactsLeads | delivery owner-local | message de contact minimal | Event/Outbox certifiés | coordonnées dans Event public |
| Listing/Property | ReservationLifecycle | décision de disponibilité | Reader public V1 certifié | calendrier, store ou SQL externe |
| IdentityAccess | ReservationLifecycle | Account auto-scopé | session/availability certifiée | AccountId fourni librement par client |
| Reservation intake | Reservation lifecycle | handoff propriétaire | Command Gateway certifié | mutation directe du workflow |
| IdentityAccess | Favorites | Account auto-scopé | session publique certifiée | ownership de la collection par IAM |
| Listing/Public Projection | Favorites | éligibilité d'une référence | Reader public V1 certifié | snapshot ou état lifecycle |
| IdentityAccess | Favorites | fermeture de compte | Event/command public certifié | suppression SQL cross-domain |

## 2. Dépendances par couche

| Voie | Runtime | Event | HTTP | PostgreSQL |
|---|---|---|---|---|
| 5.4A Lead | composition ContactsLeads et frontières publiques seulement | catalogue propriétaire à certifier ; aucun Event 5.3 réutilisé | public ingress + lecture annonceur, après contrats | schéma ContactsLeads uniquement, sans FK cross-domain |
| 5.4B Reservation | composition ReservationLifecycle et readers publics | catalogue propriétaire à réconcilier avec l'existant | surfaces privées auto-scopées | schéma ReservationLifecycle uniquement |
| 5.4C Favorites | nouveau Runtime owner Favorites | catalogue minimal optionnel à justifier | surfaces privées uniquement | nouveau schéma owner Favorites possible |

## 3. Matrice des autorités

| Décision | Autorité unique | Consumers permis | Consumers interdits |
|---|---|---|---|
| contactabilité d'un Listing | Listing/Public Projection à qualifier | ContactsLeads | Favorites ou Reservation ne la réinterprètent pas |
| disponibilité professionnelle | Professional Status | ContactsLeads via frontière certifiée | lecture F-05 interne |
| destinataire mandaté | Professional Core | ContactsLeads via resolver certifié/adapté | lecture de RepresentativeMandate |
| éligibilité du lead | ContactsLeads | delivery owner-local | Listing/Professional ne la recalculent pas |
| disponibilité d'une fenêtre | Property/Listing à qualifier | ReservationLifecycle | calcul depuis une projection non autorisée |
| conflit de réservation | ReservationLifecycle | HTTP privé, Events propres | Property ne décide pas du lifecycle Reservation |
| appartenance à une collection | Favorites | Account auto-scopé | IdentityAccess et Listing |
| visibilité d'un Listing favori | Listing/Public Projection | Favorites en lecture | Favorites ne modifie pas Listing |

## 4. Flux explicitement interdits

- transaction distribuée entre deux owners ;
- FK ou cascade cross-domain ;
- lecture SQL, Repository, Store, Snapshot ou Aggregate d'un autre owner ;
- reconstruction d'une décision depuis des Events externes ;
- dépendance directe d'un contrat Application à Laravel, PDO ou un SDK ;
- passage de coordonnées personnelles dans un Event de routage ;
- réutilisation de ModerationReports, de son Outbox ou de ses destinations ;
- mutation de Listing/Property/Account par ContactsLeads, Reservation ou
  Favorites ;
- exposition HTTP avant certification des Commands, Queries et bindings.

## 5. Analyse des cycles

| Cycle potentiel | Risque | Rupture documentaire exigée |
|---|---|---|
| Listing → Lead → Listing | le lead deviendrait source de contactabilité | Listing décide avant ingress ; aucun retour synchrone |
| Professional → Lead → Professional | le delivery modifierait le mandat | resolver read-only ; résultat de delivery owner ContactsLeads |
| Property/Listing → Reservation → disponibilité | Reservation deviendrait calendrier Property | décision de disponibilité publique, puis résultat local |
| Account → Favorites → Account | IAM posséderait la collection | IAM fournit identité ; Favorites possède données et suppression |
| Event → projection → commande source | projection utilisée comme autorité | Query projectionnelle séparée de toute mutation |

Aucun cycle n'est autorisé. Toute nécessité de retour métier synchrone doit
être représentée par un Command Gateway public et une transaction indépendante.

## 6. Dépendances bloquantes avant Contracts

- ownership définitif de Favorites ;
- frontière de contactabilité Listing ;
- résolution du destinataire professionnel et règles de confidentialité ;
- autorité de consentement et d'anti-abus ;
- frontière de disponibilité Listing/Property ;
- frontière de création/handoff Reservation ;
- éligibilité Listing pour Favorites ;
- politique de suppression Favorites lors de la fermeture Account.

Tant qu'une dépendance est ambiguë, la voie concernée demeure fail-closed et
ne peut pas ouvrir sa Contracts Foundation.

