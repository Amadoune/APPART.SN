# Phase 5.2C — Professional Profile Blueprint

## Modèle cible

```text
Account/IAM reference
        |
        v
Professional (historique, identité légale)
  ├─ Establishments
  └─ Representative Mandates
        |
        +--> ProfessionalPublicProfile
        +--> ProfessionalVerification
        +--> ProfessionalPublicPortfolio (projection)
```

## Professional historique

Il reste l’unique autorité sur :

- `ProfessionalId`, `RegistrationNumber`, `ProfessionalName` ;
- établissements actifs/retirés ;
- mandats accordés/révoqués ;
- version et concurrence de ces mutations.

La future persistence devra implémenter le port existant sans modifier
l’Aggregate ni les invariants gelés de F-05. Toute incompatibilité constatée
devra déclencher un amendement, jamais une mutation implicite.

## ProfessionalPublicProfile

Aggregate additif, distinct de UserProfile IAM et de l’identité légale :

- `ProfessionalId` comme référence immuable ;
- nom d’affichage professionnel, description et catégories publiques ;
- contacts explicitement publiables, distincts des claims IAM privés ;
- logo/media par référence F-06/F-21, jamais par stockage binaire ;
- politique `Draft`, `Visible`, `Hidden` propre à la présentation ;
- révisions append-only et optimistic locking.

La visibilité ne signifie ni statut actif ni vérification : la disponibilité
publique compose trois décisions séparées.

## ProfessionalVerification

Aggregate additif owner des dossiers de vérification :

- état proposé : `Unverified`, `Pending`, `Verified`, `Rejected`, `Expired`,
  `Revoked` ;
- références opaques de preuves, jamais contenu documentaire brut ;
- policy/version, reviewer authority et timestamps explicites ;
- décision idempotente, auditable et réversible selon politique ;
- aucun effet direct sur F-05.

## Establishments, représentants et délégations

- `Establishment` demeure une entité du Professional historique ;
- `RepresentativeMandate` demeure l’autorité du droit de représentation d’un
  établissement ;
- `RepresentativeId` référence un Account IAM disponible ;
- un mandat professionnel ne confère pas automatiquement une délégation
  d’Authoring F-19 ;
- toute délégation Listing exige le contrat public F-19 et sa propre preuve.

## Portefeuille public

`ProfessionalPublicPortfolio` est une projection, pas un Aggregate métier :

- consomme uniquement des références de Listings publiés et attribuables ;
- n’écrit jamais dans Listing, Authoring ou Public Projection ;
- retire fail-closed une référence non publiée, non attribuable ou devenue
  indisponible ;
- ne copie ni draft, ni ownership, ni données privées d’Authoring ;
- ordre éditorial éventuel appartenant à la projection Professional, jamais au
  Listing.

## Politique de disponibilité

```text
Account disponible F-17
AND Professional actif F-05
AND PublicProfile visible
AND Verification valide si badge/activité réglementée
= Professional public disponible
```

Une source absente, corrompue ou non certifiée produit `Unavailable`. Cette
composition est sans persistence et sans écriture.

## Ownership

| Élément | Aggregate Owner | Event Owner | Projection Owner | HTTP Owner |
|---|---|---|---|---|
| identité légale/établissements/mandats | Professional historique | Professionals.Core | aucun | Professional Profile adapter futur |
| présentation publique | ProfessionalPublicProfile | Professionals.Profile | Professionals public profile | Professional Profile adapter futur |
| vérification | ProfessionalVerification | Professionals.Verification | badge public minimal | Professional Verification adapter futur |
| portefeuille | aucun (projection) | aucun événement métier propre requis au Discovery | Professionals.Portfolio | Professional public adapter futur |

## Confidentialité

Les surfaces publiques excluent credentials, account claims privés, preuve
documentaire, registration evidence brute, diagnostics, IP, object keys et
identifiants de session. Les événements futurs devront être minimaux et ne
porter que des identifiants opaques, versions et dispositions fermées.
