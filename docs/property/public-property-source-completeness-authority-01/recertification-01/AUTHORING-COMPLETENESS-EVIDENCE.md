# Authoring Completeness Evidence

Le snapshot F4 contient désormais les huit faits cibles. Les formes primitives sont validées par les Value Objects existants sans recopier `PropertyTypePolicy`.

- snapshot enrichi : `CompleteForPromotion` par présence des sources ;
- snapshot pré-099 : nouveaux champs null et `IncompleteForPromotion` ;
- source requise absente : `IncompleteForPromotion`.

Les colonnes 099 sont nullable, sans backfill. City et neighborhood restent des libellés legacy non autoritatifs. Aucun AddressIntent historique n’est inventé.

Cette qualification n’affirme pas que la Promotion est exécutable : le contrôle Geography Domain reste requis après la complétude Authoring.
