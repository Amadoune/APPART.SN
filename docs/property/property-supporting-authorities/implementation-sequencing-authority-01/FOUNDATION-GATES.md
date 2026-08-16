# Foundation Gates

| Gate | Condition d'ouverture | Sortie obligatoire | Interdiction principale |
|---|---|---|---|
| F1 Geography Selection Implementation | Présente autorité GO | Reader, DTO, statuts, keyset, binding et tests GO | Modifier Authoring |
| F2 Address Identity Implementation | F1 fermé GO | Issuer UUIDv5 pur, primitives, tests GO | Persister l'Issuer ou accepter AddressId client |
| F3 Business Year Implementation | F2 fermé GO | Autorité UTC pure, compatibilité Register/Update, tests GO | Clock courante |
| F4 Authoring Source Completeness | F3 fermé GO ; donc F1/F2 GO | Schéma owner-scoped complet, snapshots historiques incomplets mais lisibles | Créer Aggregate Property |
| F5 Source Completeness Recertification | F4 fermé GO | Toutes sources réellement exécutables, verdict GO | Continuer si une source manque |
| F6 Promotion Implementation | F5 GO fermé | Handoff, ledger, transaction locale, replay et résultats fermés | Lire Projection ou inventer un fait |
| F7 Promotion Recertification | F6 fermé GO | Aggregate/Registry/replay réels et verdict GO | Ouvrir RC2 sur preuve partielle |
| F8 RC2 Iteration 11 | F7 GO fermé | Rejeu fail-fast jusqu'à la divergence suivante | Anticiper Search/Public Listing |

Chaque chantier exécute uniquement ses validations impactées : Unit, Feature/Composition, Architecture, PostgreSQL si persistance, PHPStan, Pint et `git diff --check`.
