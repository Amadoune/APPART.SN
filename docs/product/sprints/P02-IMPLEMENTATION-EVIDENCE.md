# P02 — Implementation Evidence

## Composition réalisée

La commande locale `appart:local:first-listing` compose les capacités existantes sans SQL direct. Elle utilise des identifiants de développement fixes pour rendre les replays déterministes et idempotents.

| Étape | Frontière utilisée | Preuve |
|---|---|---|
| Property | `PropertyRegistry` et Aggregate Property | Property local marqué P02, relié au Listing |
| Média | `MediaCollectionRegistry` et ownership lookup | collection owner-scoped, image principale `p02-first-listing.svg` |
| Draft | `ListingRegistry` et Aggregate Listing | Listing initialisé en draft |
| Révisions | `ListingRevisionAllocatorV1` | intents fermés distincts pour les trois transitions |
| Workflow | `ListingPublicationOrchestrator` | Submit, BeginReview, ApproveAndPublish |
| Registry | `SubmitListing`, `SendToReview`, `PublishListing` | Aggregate relu en `published` |
| Expiration | `ListingPublicationExpirationPolicyV1` | `publishedAt + 90 days` |
| Sources publiques | writers certifiés Search, Content/SEO, Geography et Media | aucune écriture directe de projection |
| Première génération | génération candidate, rebuild, manifeste et activation | activation sans génération active préalable |
| Lecture publique | `PublicSearchResultsReaderV1` et `PublicListingQuery` | accueil et fiche issus de la projection PostgreSQL |

## Amendements minimaux

### Expiration

`NinetyDayListingPublicationExpirationPolicy` appartient à Listing Lifecycle et dérive uniquement l'expiration de l'instant autoritatif de publication.

### MediaCollection

Les adapters `RegistryListingPropertyCatalog` et `RegistryListingMediaCatalog` relient mécaniquement Listing Lifecycle aux registries Property et Media. Ils vérifient l'ownership Property → MediaCollection et la présence d'un média principal, sans SQL direct.

### Bootstrap de projection

`CandidatePublicListingProjectionSource` ajoute une inspection explicitement bornée à la génération candidate. La lecture publique normale continue d'exiger une génération active. Le rebuilder peut ainsi assembler la toute première candidate, puis seulement la valider et l'activer.

### Front Office

L'accueil interroge `PublicSearchResultsReaderV1`, remplace les cartes fictives par les items réellement projetés et lie l'annonce à son `canonicalPath` certifié.

## Donnée démontrée

- Listing : `b0200000-0000-4000-8000-000000000002`.
- Canonical path : `annonces/p02-premiere-annonce-dakar`.
- Statut Registry : `published`.
- Statut Workflow : `published`.
- Expiration observée : `2026-11-07T12:03:00.000000+00:00`.
- Média public : `/p02-first-listing.svg`.

## Réversibilité

La donnée porte des UUID réservés au sprint P02 et un marqueur de développement explicite. Elle n'est pas destinée à la production. La remise à zéro s'effectue par reconstruction de la base locale `appart_test` depuis les migrations certifiées ; aucun script de suppression ad hoc ni SQL manuel n'est introduit dans le produit.
