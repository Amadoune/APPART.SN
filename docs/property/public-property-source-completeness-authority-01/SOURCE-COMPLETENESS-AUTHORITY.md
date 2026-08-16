# Public Property Source Completeness Authority 01

## Objet

Cette autorité qualifie les sources nécessaires à `RegisterProperty` sans modifier Authoring ni le Domain. Elle distingue la source du fait, la validation finale et les identités techniques.

## Décision générale

- Les faits physiques et l'adresse sont légitimement saisis dans Property Authoring, owner-scoped et versionnés avec son snapshot.
- `PropertyReference` est proposée comme saisie propriétaire Authoring ; RealEstateCatalog conserve format et unicité. Aucun générateur/réservateur existant n'a été trouvé.
- `GeographicPlaceId` doit être sélectionné depuis une source Geography autoritative ; les libellés libres ne sont pas convertibles.
- `BusinessYear` ne possède pas d'autorité applicative normative observée.
- `AddressId` ne possède pas non plus de règle d'émission documentée pour cette frontière.

En conséquence, la complétude cible est définie mais toutes ses sources ne sont pas aujourd'hui exécutables.

**Verdict : NO GO PROPOSÉ.**
