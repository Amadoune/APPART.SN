# Phase 5.0A — Dependency Matrix

## 1. Matrice des dépendances autorisées

Légende : `R` lecture par port/projection ; `E` consommation d'événement ;
`C` commande publique du domaine cible ; `—` aucune dépendance ; `X`
interdite. Toute cellule non explicitement autorisée vaut `X`.

| Consommateur \ Owner | IAM | PRO | GEO | REC | LST | MED | LEA | RSV | MOD | MON | PUB |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| IAM | — | X | X | X | X | X | X | X | X | X | X |
| PRO | R/E | — | R | X | R | X | X | X | X | X | R |
| GEO | X | X | — | X | X | X | X | X | X | X | X |
| REC | X | X | R/E | — | X | X | X | X | X | X | X |
| LST | R/E | R/E | R/E | R/E | — | R/E | X | X | X | X | X |
| MED | R | X | X | R | R | — | X | X | X | X | X |
| LEA | R/E | R/E | X | X | R/E | X | — | X | X | X | R |
| RSV | R/E | R | X | R/E | R/E | X | X | — | X | X | X |
| MOD | R/C | R/C | X | R/C | R/C | R/C | R | R | — | X | R |
| MON | R | R | X | X | R | X | X | X | X | — | R |
| SEA | E | E | E | E | E | E | X | X | X | E | R/E |
| SEO | X | R/E | R/E | R/E | R/E | R/E | X | X | X | X | R/E |
| FAV | R | X | X | X | R | X | X | X | X | X | R |
| NOT | R | E | E | E | E | E | E | E | E | E | E |
| ADM | R/C | R/C | R/C | R/C | R/C | R/C | R/C | R/C | R/C | R/C | R |
| MIG | C | C | C | C | C | C | C | C | C | C | X |
| PUB | E | E | E | E | E | E | X | X | X | E | — |

## 2. Interdictions globales

| Interdiction | Motif |
|---|---|
| accès SQL cross-domain | contourne owner, invariants et optimistic locking |
| foreign key imposant la disponibilité synchrone d'un autre Aggregate | couple les cycles de vie |
| transaction ACID multi-domaines | ownership ambigu et rollback non maîtrisé |
| écriture dans une projection par un use case métier | projection reconstruisible, non autorité |
| publication d'un événement par son consommateur | Event Owner unique |
| ajout direct d'un type dans l'Outbox gelée | nécessite catalogue, mapper et amendement certifiés |
| appel de controller à controller | HTTP est un adapter, pas un port métier |
| dépendance Domain vers Laravel/PDO | viole l'indépendance DDD |
| lecture de secrets ou PII dans un événement public | confidentialité et minimisation |
| MOD/ADM/MON modifiant directement LST/IAM | les owners ciblés doivent accepter la commande |
| MIG utilisé après cutover | la frontière Legacy est temporaire |

## 3. Séquencement imposé par les dépendances

| Capacité | Prérequis fermes | Peut avancer en parallèle avec |
|---|---|---|
| Account completion | amendement 4.9, politique sécurité/privacy | Content, Search |
| Listing authoring | Account completion minimal, Property authoring, Media ingress | Professional profile |
| Moderation | Listing authoring IDs/commands, Account admin authority, Audit | Search query |
| Lead ingress | Listing public/contactability, Professional/Account status | Favorites |
| Reservation intake | Listing/Property availability, Account identity | Lead ingress |
| Monetization | décision commerciale, Account/Professional, Audit, finance controls | aucun chemin critique MVP si reportée |
| Search experience | PUB certifiée et critères de visibilité | Content/SEO |
| Content/SEO | PUB/GEO certifiés, roles admin | Search |
| Migration | tous ports d'import et règles de rapprochement stabilisés | production hardening initial |
| Launch | migration rehearsal, security, observability, performance, UAT | — |

## 4. Amendements probables

| Cible gelée | Besoin futur | Décision |
|---|---|---|
| Account/AccountRegistry 4.9 | auth, fermeture, profil | amendement versionné obligatoire avant Phase 5.1 |
| lifecycle event catalogs | nouveaux consommateurs seulement | aucun amendement si V1 suffit ; abonnement additif |
| generic Outbox/runtime | nouveau producteur MOD/MON/NOT | extension versionnée et recertification de compatibilité |
| Public Projection | nouvelles colonnes/facettes | amendement seulement après contrat de lecture |
| Listing publication | commandes de modération | utiliser transitions existantes ; amendement si sémantique absente |
| Property/Media lifecycles | nouvelles actions authoring/ingestion | séparer si possible ; sinon amendement ciblé |
