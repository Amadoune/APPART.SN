# RC1-A Migration Manifest

## Ordre candidat additif

| Ordre | Migration | Owner | Rollback | SHA-256 up | SHA-256 down |
|---:|---|---|---|---|---|
| 092 | `listing_transition_reason_optional` | Listing Lifecycle | présent | `0ab08108dbdbfc71d6d2ce8442758bb83221484a0ce43ebbe467cd38d99794a0` | `6a9409146682a4e14cd15ad2ff8db2ec50e96ab116967c31922fbd44053a5db9` |
| 093 | `authoring_public_fact_handoff` | Listing Lifecycle | présent | `8d4e5ecfb17cfd09224495bbafe46de4b7c73d47b9c621890545dd257c4b8a51` | `a6bece8312e917d29270677bfec81a9824a944f3862a1af47f63589b569bf3b9` |
| 094 | `property_authoring_public_surface` | Real Estate Catalog | présent | `467b197a1af75828502c45e1f570d0ec3fe6bebfe2ccc777a846f2bd6005fef8` | `7e3e58864d4164d321586f26d7d96a4b9f7cb7c7b3ac6e0aa6c74a25334caa62` |
| 095 | `iam_session_policy_authority` | Identity Access | présent | `3edb9ede159d64a062a94e2ef022e4ae85e003ef9df8d4a5aeafe732383c371c` | `c74339566a9813419305cf20901a0ef1166f88b51598675988582e243525708c` |
| 096 | `publication_review_queue` | Publication Review | présent | `245b5df44bb5b53fff8df28660d69b5b7aad72bdd1d69636f2483152b2bd3c1b` | `a6fe105b2c57fd587e3919fbdf17f88be492d52472f7e5ae081f37b467cbe6be` |
| 097 | `listing_publication_command_gateway` | Listing Lifecycle | présent | `a907529bb12f34b4750e51c9f799333774791530132c9df4b21ef4ae92bedbc7` | `cacc79b7496749d8867c1828f31afbee46d86f397547567a3fba3e5b689fb720` |

## Dépendances

1. 092 assouplit uniquement `TransitionReason` sur les trois transitions qualifiées.
2. 093 ajoute le handoff des faits publics requis par la publication.
3. 094 complète la surface Property Authoring consommée par le parcours owner.
4. 095 matérialise l'état durable nécessaire aux politiques de session IAM.
5. 096 crée Queue, Claim et Command Ledger Publication Review.
6. 097 ajoute le ledger de la Gateway de publication Listing Lifecycle.

L'ordre numérique 092→097 est obligatoire. Le rollback candidat s'effectue en ordre strictement inverse 097→092, après arrêt des consumers concernés et sauvegarde des données durables.

## Compatibilité

- migrations historiques modifiées : aucune dans ce lot ;
- backfill implicite : aucun ;
- évolution : additive ou assouplissement explicitement qualifié ;
- rollbacks 092–097 : tous présents ;
- migrations SQL totales observées : 79 up / 71 down.

Huit migrations historiques initiales ne possèdent pas de `.down.sql` : 001 AdministrationAudit, 002 Listing, 003 Property, 004 Media, 007 Media ownership, 008 Search decisions, 009 Content SEO et 012 Property Listings resolution. Cette dette préexistait à RC1-A ; elle n'empêche pas la matérialisation de la source mais devra être intégrée à la stratégie de rollback production globale.
