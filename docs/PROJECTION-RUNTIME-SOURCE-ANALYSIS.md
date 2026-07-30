# Sprint 3.7C — Analyse Projection Runtime Source

## Décision

`CertifiedPublicListingProjectionSource` compose exclusivement les ports certifiés. Il ne construit aucune décision Search, SEO, Geography ou Media. Son unique responsabilité est de relier les identités explicites, vérifier les résultats fermés et produire `PublicListingProjectionSources`.

Le contrat historique `PublicListingProjectionSource` retourne `null` lorsqu'une source obligatoire est indisponible. Pour préserver ce contrat tout en rendant les causes observables, l'implémentation expose également `inspect()`, dont le résultat typé distingue chaque absence, corruption, ambiguïté et divergence.

## Données obligatoires

Listing, Property, ownership Media, MediaCollection, décision Search, snapshot Content/SEO, génération Active et `decisionAt` doivent être disponibles et cohérents. Leur absence bloque l'assemblage sans fallback.

Public Geography et Public Media sont des dimensions de promotion. Leur absence produit volontairement un assemblage avec versions nulles : le watermark existant fournit alors la `PromotionReadiness` bloquée appropriée. Une décision présente mais corrompue bloque totalement l'assemblage.

## Adaptation

Les contenus Geography et Media sont déjà décidés. L'adaptateur convertit leurs types publics vers les types Content/SEO attendus, dans le même ordre et sans sélection : locality et breadcrumb sont repris tels quels ; l'URL de la couverture officiellement désignée est reprise telle quelle.

## Déterminisme

La génération provient du reader Active, les versions proviennent des décisions durables et `decisionAt` provient de son reader certifié. La source compare ce dernier au snapshot Content/SEO et refuse toute divergence. Elle ne lit aucune horloge.
