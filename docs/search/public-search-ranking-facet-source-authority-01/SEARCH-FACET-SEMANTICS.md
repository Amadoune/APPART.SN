# Search Facet Semantics

`SearchFacetPolicy` gouverne la forme, la confidentialité, la cardinalité et la déduplication des facettes. Elle ne décide pas quelles facettes doivent être produites depuis Listing, Property, Media ou Geography.

Clés fermées existantes : `amenity`, `feature`, `category`, `city`, `district`, `has_image`, `property_type`. Les deux premières sont multivaluées ; les autres sont monovaluées.

Conclusion : sémantique structurelle certifiable, sémantique de matérialisation absente.
