# Public Projection Updater

## Verdict du Sprint 3.6B

**GO.** L'orchestration applicative est certifiée en isolation, sans infrastructure de production. La promotion reste volontairement impossible tant que les sources publiques Geography et Media ne fournissent pas chacune une révision stable.

## Responsabilité et pipeline

`PublicListingProjectionUpdater` charge un paquet cohérent via `PublicListingProjectionSource`, puis délègue à `SearchListingProjectionBuilder`, `ListingSeoDecisionPolicy`, `SeoListingProjectionBuilder` et `PublicListingReadModelBuilder`. Il construit le watermark, contrôle sa `PromotionReadiness`, crée le record technique et appelle exclusivement `PublicListingProjectionWriter::applyCurrent`.

L'Updater ne décide ni Search, ni canonical, ni robots, ni JSON-LD, ni visibilité, ni redirection. La policy ContentSeo existante reste seule responsable de ses règles ; aucune règle n'est recopiée.

## Sources et ownership

- Listing, Property et Media fournissent les Aggregates au seul builder Search existant et leurs versions au watermark. Ils ne sont jamais exposés au Store.
- Search fournit ses faits SEO et une version stable explicite.
- ContentSeo fournit contenu, canonical décidée, historique, traitements et version stable. Il reste propriétaire des décisions SEO.
- Public Geography fournit localité et breadcrumb ; Public Media fournit l'URL publique. Leurs modèles actuels n'exposent aucune révision : leurs versions restent nullable, sans compteur synthétique.
- Le Projection Store possède uniquement le record dérivé, son état, sa génération et son watermark.

`PublicListingProjectionSources` exige qu'une source publique et sa version stable soient présentes ensemble et refuse une association incohérente.

## PromotionReadiness

Une écriture n'est tentée que si le watermark est `Ready`. Une révision Geography absente, Media absente, ou les deux, produit `PromotionNotReady` avec la cause exacte et zéro appel au writer. Une source introuvable produit `SourceUnavailable`. Une chaîne officielle sans page publique produit `ProjectionUnavailable`. Aucune promotion partielle n'existe.

## Interprétation du writer

Chaque résultat 3.6A est traduit exhaustivement en issue de même nom : `Applied`, `AlreadyApplied`, `RejectedObsolete`, `DivergentWatermark`, `IncompleteWatermark`, `CanonicalCollision`, `CanonicalReplacementRequired`, `HistoricalReservationConflict` et `GenerationMismatch`. Aucune branche `default` n'existe. `CanonicalReplacementRequired` est signalé ; l'Updater ne choisit jamais une ancienne canonical.

## Invariants et limites

- mêmes entrées et même date de décision donnent le même record ;
- la date de décision est un fait ContentSeo, jamais une version causale ;
- le writer ne voit aucun Aggregate ;
- aucun SQL, PostgreSQL, Repository, Laravel, binding, dispatcher, queue, outbox ou runtime n'est introduit ;
- tombstone, remplacement atomique et génération candidate exigent des orchestrations et faits dédiés ;
- aucune source de production ni aucun binding n'existe encore.

## Préparation du Sprint 3.6C

Le prochain sprint doit décider la livraison durable post-commit, l'ordre, l'idempotency key, les retries et la réconciliation. Avant tout runtime promotable, Geography publique et Media publique doivent acquérir une stratégie de révision stable explicite. L'Updater est la cible passive de cette future livraison, pas un projector runtime.
