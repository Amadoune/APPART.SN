# Source Assembly Evidence

## Éléments assemblables

Un snapshot F4 complet fournit mécaniquement PropertyId, PropertyReference, PropertyType, SurfaceArea, RoomCount, BathroomCount, ConstructionYear, GeographicPlaceId et AddressLine. F2 calcule AddressId depuis l’intention serveur ; F3 calcule BusinessYear depuis l’instant stable.

Les Value Objects et la structure pure `Address` sont constructibles sans créer ni persister un Aggregate Property.

## Arrêt fail-fast

La preuve end-to-end ne peut pas franchir la revalidation `GeographicPlaceCatalog::statusOf` avec un composant productif. Utiliser `FakeGeographicPlaceCatalog`, supposer `Usable` depuis la validation F4-A ou appeler directement SQL/Projection constituerait un fallback interdit.

Aucun test artificiel n’est ajouté pour masquer ce maillon. Aucun `PropertyRegistry::add` n’est exécuté.
