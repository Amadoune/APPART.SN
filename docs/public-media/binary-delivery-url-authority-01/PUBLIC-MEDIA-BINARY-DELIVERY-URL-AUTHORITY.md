# Public Media Binary Delivery & URL Authority 01

## Décision normative

L'owner final est **Media Public Delivery**, boundary applicative de Media. Il expose un locator public stable dérivé de `MediaId` et de la révision binaire, puis sert le binaire privé uniquement après décision owner-side d'éligibilité.

Le locator V1 est un path relatif : `/media/{mediaId}/revisions/{assetVersion}`. L'URL absolue est dérivée à la lecture depuis une origine publique HTTPS validée par environnement. Ni hostname ni storageKey ne sont persistés.

La route répond sans session. Elle relit Media/asset/attachment/Listing et ne sert que : item actif, asset ready et intègre, attachment appliqué, Listing Published et relation d'ownership cohérente. Tout autre cas répond 404, sans révéler l'existence privée.

## Verdict

GO proposé. La prochaine étape autorisée est exclusivement `PUBLIC MEDIA DECISION MATERIALIZATION AUTHORITY 01 — REOPENING / COMPLETION 01`.
