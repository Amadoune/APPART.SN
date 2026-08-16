# Selection Read Dependency

F1 ne doit pas étendre `PlaceRegistry` avec pagination/query. Un port read-only dédié Geography Application lira les mêmes tables owner-local : filtre type/parent/enabled/non merged et keyset `(normalization key official_name, id)`.

Le Repository Aggregate sert `find/add/save`. Le Selection Source sert une lecture optimisée et retourne des snapshots minimaux, ensuite réduits par `GeographySelectionReaderV1`.

Index cible F1 sur `(type, parent_place_id, enabled, merged_into_place_id, lower(official_name), id)` ou équivalent PostgreSQL démontré lors de l'implémentation. L'index appartient à la même migration de persistance seulement s'il est requis par le contrat de lecture déjà certifié.

Aucune lecture de `public_geography`, Projection ou Search. F1 pourra être rouverte uniquement après GO de l'implémentation F0.
