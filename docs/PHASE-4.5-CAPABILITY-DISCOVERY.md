# Phase 4.5 — Next Business Capability Discovery

## Capacité retenue

La prochaine capacité verticale est **Professional Status Lifecycle**, propriété du bounded context `Professionals`.

Les preuves existantes sont suffisantes :

- Aggregate Root `Professional`, versionné et reconstituable ;
- statut actif/suspendu déjà explicite dans le comportement de l'Aggregate ;
- commandes `RegisterProfessional`, `SuspendProfessional` et `ReactivateProfessional` ;
- faits `ProfessionalRegistered`, `ProfessionalSuspended` et `ProfessionalReactivated` ;
- port propriétaire `ProfessionalRegistry` avec création, lecture et contrôle optimiste de version ;
- identités `ProfessionalId` et `RegistrationNumber` fermées ;
- aucune dépendance métier externe pour suspendre ou réactiver.

## Frontière retenue

La capacité gère uniquement l'entrée d'un professionnel actif, sa suspension et sa réactivation. Elle ne gère pas le cycle des établissements ni celui des mandats de représentation.

`EstablishmentAdded`, `EstablishmentRemoved`, `MandateGranted` et `MandateRevoked` appartiennent à deux futures sous-capacités. Leur inclusion produirait une machine composite dépendant d'identités, de collections et de règles de cardinalité qui ne sont pas des états du professionnel.

## Décision sur l'entrée

L'enregistrement est une création explicite hors transition du workflow de statut. Le workflow 4.5A expose `initialState() → Active`, puis décide uniquement `Suspend` et `Reactivate`. Aucun état artificiel `Unregistered` n'est introduit. Le fait historique `ProfessionalRegistered` reste gelé et ne devient pas implicitement un événement du workflow de statut.

## Valeur métier

Le statut professionnel est une source normative future pour l'éligibilité des annonceurs, les mandats et les capacités commerciales. La verticaliser fournit une décision propriétaire sans coupler `ContactsLeads` ou Listing Lifecycle à l'Aggregate `Professional`.
