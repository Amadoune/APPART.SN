# Source reader evidence

Le materializer résout ListingRegistry → PropertyRegistry → Address → terminal Place. `PublicGeographyHierarchyReader` remonte PlaceRegistry parent par parent, détecte cycle, absence et racine non-country, puis inverse explicitement la chaîne en root→leaf. Aucun SQL Application.
