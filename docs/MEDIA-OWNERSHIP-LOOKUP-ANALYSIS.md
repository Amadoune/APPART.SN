# Sprint 3.8A — analyse d’architecture

L’ownership officiel existe déjà dans le modèle et la persistance certifiés : `MediaCollection::propertyId()` est stocké dans `media.media_collections.property_id`. Aucun lien direct Listing → MediaCollection n’est nécessaire. Le chemin retenu est donc :

`Listing.propertyId → Property → media.media_collections.property_id → MediaCollectionId`.

La fondation 3.8A ne modifie ni le Domain Media ni son Repository. Elle ajoute un port applicatif spécialisé et un adaptateur PostgreSQL de lecture. Le port reçoit une identité Property explicite ; il ne dérive jamais cette identité depuis un nom, un préfixe ou un format Listing.

Le schéma existant n’impose pas l’unicité de `property_id`. Cette réalité est conservée : zéro ligne produit `Missing`, une ligne produit `Found`, et au moins deux lignes produisent `Ambiguous`. L’adaptateur lit au maximum deux identités, ce qui suffit à la décision sans scan applicatif global.

Un index non unique sur `property_id` est la seule évolution persistante. Une contrainte unique aurait modifié rétroactivement le modèle d’ownership ; elle n’est donc pas introduite dans ce sprint.
