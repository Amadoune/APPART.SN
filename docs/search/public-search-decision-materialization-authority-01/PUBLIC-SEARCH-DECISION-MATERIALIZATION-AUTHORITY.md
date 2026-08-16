# Public Search Decision Materialization Authority 01

## Décision

SearchDiscovery est l'owner unique de `ProjectionState`, `SearchRank`, `SearchFacet`, `SourceRevisionSet`, de l'identité et de la version `SearchDecision`. Ni PublicationReview ni Public Projection ne peuvent construire ou écrire cette décision.

Le pipeline ne peut toutefois pas être entièrement qualifié. `SearchProjectionPolicy` ne calcule pas le rang : il recopie `ListingProjectionSource::rank`. Aucun contrat productif Listing, événement Published, policy SearchDiscovery ou source persistante auditée ne fournit ce rang. Les valeurs 100, 500 et 600 observées appartiennent uniquement aux tests et commandes locales.

Le catalogue `SearchFacetPolicy` gouverne les clés admises et leur ordre, mais aucun assembler productif ne décide quelles facettes Listing/Property/Media émettre. L'identité et la version de décision ne possèdent pas non plus d'autorité d'attribution productive.

## Fail-fast

La mission interdit d'inventer un rang. Le chantier s'arrête donc avant toute sélection de valeur, création de contrat ou choix de pipeline.

## Autorités acquises

- `SearchVisibilityPolicy` décide `Visible/Hidden/Removed` depuis trois états sources ;
- `SearchFacetPolicy` valide, déduplique et ordonne un ensemble déjà fourni ;
- `SearchDecisionWriter` persiste monotoniquement une décision déjà construite ;
- l'événement certifié `listing.publication.published` peut constituer un trigger, mais ne porte ni rank ni facettes.

## Autorité préalable requise

Ouvrir une **Public Search Ranking and Facet Source Authority 01** pour qualifier la provenance normative du rang, le catalogue effectivement matérialisé et les adapters owner-scoped des trois sources. Tant que cette autorité n'est pas GO, `Implementation 01` reste fermée.

## Verdict

**NO GO PROPOSÉ — PUBLIC SEARCH DECISION MATERIALIZATION AUTHORITY 01**

## Completion 01 — statut opératif

Le texte ci-dessus est conservé comme certification historique. Sa cause racine est fermée par `PUBLIC SEARCH RANKING POLICY AUTHORITY 01` : score de priorité, direction décroissante, baseline `0`, facettes `[]`, policyId `public-search-ranking-policy-v1`.

La Completion qualifie également : révision Property par le ledger de promotion autoritatif, identité UUIDv5, version Search owner-locale, handoff Published et catch-up commun.

**GO PROPOSÉ — REOPENING / COMPLETION 01.** Voir `AUTHORITY-COMPLETION-01.md` et les documents `FINAL-*`.
