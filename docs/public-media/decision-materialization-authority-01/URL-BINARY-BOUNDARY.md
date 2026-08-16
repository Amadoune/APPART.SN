# URL / binary boundary

## Completion 01

Le blocage historique est fermé. V2 persiste uniquement `/media/{mediaId}/revisions/{assetVersion}`. Media Public Delivery dérive l'URL absolue, relit les owners et stream le binaire privé. L'ancien locator et tout média non servable donnent 404; storageKey reste interne.

Le binaire est écrit avec visibilité `private`. `MediaBinaryObject` expose ownerId, assetId, storageKey, checksum et bytes. Aucun endpoint public, URL resolver, asset key public ou politique de durée/stabilité n'a été trouvé.

Le contrat Public Media exige pourtant une URL valide par item. Construire une URL depuis storageKey, le hostname ou une route supposée serait une nouvelle décision de sécurité et de delivery.

Blocage unique : **Public Media Binary Delivery & URL Authority 01**.
