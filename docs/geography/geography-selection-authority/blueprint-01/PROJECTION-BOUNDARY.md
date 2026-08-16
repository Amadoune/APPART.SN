# Projection Boundary

`GeographySelectionReaderV1` appartient à Geography Application et sert l'acquisition d'une identité par un consumer amont.

`PublicGeographyDecisionReader` reste une source aval de Public Projection, lue par PlaceId connu pour assembler locality et breadcrumb publics. Il ne fournit ni catalogue, ni selectability, ni identité à Authoring.

Public Projection, Search et Public Listing ne sont jamais consultés par le nouveau reader. Aucun read model public n'est élevé au rang de source Geography Domain.
