# Phase 5.2C — Professional Profile Discovery

## Statut et recommandation

**GO CERTIFIÉ — FERMÉ.**

Le jalon est exclusivement documentaire. Aucun code, contrat PHP, port,
migration, provider, route, événement concret, Runtime, Outbox, SQL ou test
n’est introduit.

## État réel constaté

Le bounded context `Professionals` contient déjà :

- l’Aggregate `Professional` avec `ProfessionalId`, `RegistrationNumber`,
  `ProfessionalName` et version optimiste ;
- les entités `Establishment` et `RepresentativeMandate` ;
- les comportements de création, ajout/retrait d’établissement,
  attribution/révocation de mandat ;
- le port historique `ProfessionalRegistry`, sans implémentation de production ;
- les événements de domaine historiques correspondants ;
- F-05 Professional Status Lifecycle, propriétaire exclusif de `Active` /
  `Suspended`, de sa persistence, de son delivery et de son HTTP.

Le Discovery interdit donc tout second Aggregate représentant l’identité légale,
les établissements ou les mandats.

## Besoin métier restant

La capacité doit permettre :

- une présentation publique professionnelle contrôlée ;
- la gestion des établissements et représentants via l’Aggregate existant ;
- la preuve et la décision de vérification professionnelle ;
- un portefeuille public dérivé de références Listing publiées ;
- la composition d’une disponibilité publique sans recopier les états Account,
  Professional Status ou Listing Publication ;
- des délégations professionnelles distinctes des délégations d’Authoring F-19.

## Autorités retenues

| Autorité | Owner | Responsabilité |
|---|---|---|
| Professional Core | Aggregate historique `Professional` | identité légale, registration number, établissements, mandats |
| ProfessionalPublicProfile | nouvel Aggregate additif | présentation publique, description, contacts publiables, branding et visibilité |
| ProfessionalVerification | nouvel Aggregate additif | demandes, preuves référencées, décision, expiration et révocation de vérification |
| ProfessionalPublicPortfolio | projection propriétaire | références publiques de Listings éligibles, sans ownership ni ordre Authoring |

`Establishment` et `RepresentativeMandate` restent des entités du Professional
historique. Elles ne deviennent pas des Aggregate Roots.

## Invariants directeurs

- un `ProfessionalId` désigne une seule identité professionnelle ;
- un `RegistrationNumber` reste unique et immuable ;
- un établissement appartient à un seul Professional ;
- un mandat actif vise un établissement actif et un représentant identifié ;
- le représentant est référencé par identifiant IAM, sans copie de credentials
  ni de claims ;
- une suspension F-05 rend le profil indisponible sans muter ses données ;
- une fermeture Account F-17 invalide l’accès, sans supprimer le Professional ;
- seule une vérification valide peut rendre le badge public ;
- le portefeuille ne crée, ne publie et ne modifie aucun Listing ;
- aucune PII privée, preuve brute ou diagnostic interne n’est projeté
  publiquement.

## Réserve de frontière

F-05 ne fournit actuellement aucun contrat public fermé de lecture de son état.
Le `ProfessionalStatusWorkflowStore` est un port de persistence et
`ProfessionalStatusOrchestrator` est une frontière de commande, pas une vue de
disponibilité.

Est donc identifié :

`A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01`
→ IDENTIFIÉ
→ NON OUVERT
→ BLOQUANT AVANT TOUTE COMPOSITION RUNTIME OU HTTP DE DISPONIBILITÉ

Le Discovery peut être certifié GO ; aucun contournement de F-05 ne sera admis.

## Hors périmètre

- modification du lifecycle F-05 ;
- authentification, sessions, Profile IAM ou claims F-17/F-18 ;
- ownership et délégations de Listing F-19/F-20 ;
- publication Listing F-01 ;
- Media Ingestion F-21/F-22 ;
- paiement, abonnement, facturation et benefits ;
- modération et décision administrative ;
- stockage de documents bruts de vérification dans le domaine ;
- anonymisation ou erasure.
