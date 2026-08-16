# Determinism and Pagination

- Ordre canonique : `PlaceName::normalizationKey()` ascendant, puis `placeId` ascendant.
- Pagination : keyset, jamais offset.
- Cursor opaque versionné contenant le fingerprint `type + parentPlaceId`, puis la dernière clé normalisée et le dernier PlaceId ; intégrité protégée par encodage/checksum applicatif.
- Un cursor d'une autre requête ou invalide est rejeté par l'adapter.
- Les doublons sont impossibles par PlaceId ; une corruption source est `Corrupted`, pas dédupliquée silencieusement.
- Les Places merged ne sont pas remplacées dans les résultats ; seule une cible elle-même sélectionnable apparaît.

À état Geography identique, pages et représentations sont équivalentes. En cas de mutation concurrente entre pages, la lecture suivante observe le nouvel état selon la clé de continuation ; aucun snapshot global n'est revendiqué car Geography n'en possède pas. La revalidation finale couvre la dérive après sélection.
