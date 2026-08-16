# Binary Delivery integration

Media Public Delivery fournit un résultat fermé : Ready(mediaId, assetVersion, relativeLocator, MIME, checksum), NotReady, Missing, Corrupted ou DependencyUnavailable.

La materialization décide la présence publique. Le GET relit séparément les owners et réévalue la servabilité sans consulter PublicMediaDecision ni Projection.

`/media/{mediaId}/revisions/{oldVersion}` répond 404 dès que cette version n'est plus la version ready courante.
