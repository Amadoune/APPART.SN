# Phase 5.2C — Boundary & Dependency Matrix

## Dépendances autorisées

| Source 5.2C | Cible | Mode autorisé | Usage |
|---|---|---|---|
| Professional Core | F-17 IAM | contrat public de disponibilité/référence | représentant et accès |
| Public Profile | Professional Core | port public fermé | identité professionnelle existante |
| Verification | Professional Core | référence `ProfessionalId` | cible vérifiée |
| Availability | F-05 Professional Status | futur contrat public read-only versionné | actif/suspendu |
| Portfolio | F-01 Listing Publication | contrat/public facts certifiés | publication courante |
| Portfolio | F-19 Authoring | contrat public ownership/délégation certifié | attribution, jamais mutation |
| Public Profile | F-06/F-21 Media | référence publique d’un media sûr | logo/branding |
| Future HTTP | F-17 session | auto-scope et autorisation | acteur authentifié |

## Dépendances interdites

| Dépendance | Décision |
|---|---|
| lecture SQL directe des tables IAM, Status, Listing ou Authoring | interdite |
| appel au store F-05 comme API de disponibilité | interdit |
| mutation Account/Profile IAM depuis Professionals | interdite |
| mutation Listing/Authoring depuis Portfolio | interdite |
| copie des ownerships ou délégations F-19 | interdite |
| FK cross-domain ou cascade | interdite |
| transaction ACID cross-domain | interdite |
| dépendance Application vers Infrastructure/Laravel/SDK | interdite |
| publication de preuves ou PII privée | interdite |

## Lectures et écritures

| Autorité | Écrit | Lit |
|---|---|---|
| Professional Core | ses futurs stores propriétaires | références IAM publiques |
| Public Profile | profil public et révisions | Professional Core, media public |
| Verification | dossiers, décisions et historique | Professional Core, policy |
| Portfolio | projection Professional uniquement | facts Listing/ownership publics |
| Availability | rien | F-17, F-05, Profile, Verification |

## Frontières gelées

F-05, F-17/F-18, F-19/F-20 et F-21/F-22 sont consommés uniquement par leurs
contrats publics. F-01 et F-06 restent propriétaires des lifecycles Listing et
Media. Aucun owner 5.2C ne peut étendre ces capacités.

## Réserve

`A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01` doit définir une vue publique,
versionnée, minimale et fail-closed de F-05 avant Runtime/HTTP. Il est identifié
mais non ouvert par ce Discovery.
