# Published Handoff

`ListingPublished` transporte ListingId, revisionId, expiration, evidence, transaction kind, version et temps d’événement. Il ne transporte pas headline, description, canonical path ni les révisions Search/Property.

Un matérialiseur ContentSeo devrait donc relire les sources owner. Aucun consumer existant ne transforme actuellement `ListingPublished` en snapshot ContentSeo.

Le handoff Search récemment ajouté est indépendant. Le delivery `content_seo.reconstruction.requested` sait seulement déclencher Projection après qu’une source existe ; il ne produit pas cette source.
