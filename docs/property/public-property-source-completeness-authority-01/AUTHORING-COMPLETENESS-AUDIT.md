# Authoring Completeness Audit

| Besoin Domain | État Authoring actuel | Qualification |
|---|---|---|
| propertyId | présent | Suffisant |
| ownerAccountId | présent | Suffisant pour scope, non transmis à l'Aggregate actuel |
| version / intent | présents | Suffisants pour concurrence et replay Authoring |
| propertyType | présent nullable | Source légitime, incomplet tant que null |
| city / neighborhood | présents nullable | Insuffisants pour GeographicPlaceId et Address |
| reference | absent | Champ Authoring cible proposé |
| surface | absent | Champ Authoring cible légitime |
| rooms | absent | Champ Authoring cible légitime |
| bathrooms | absent | Champ Authoring cible légitime |
| constructionYear | absent | Champ optionnel cible légitime |
| geographicPlaceId | absent | Champ cible, mais uniquement par sélection Geography autoritative |
| addressLine | absent | Champ owner-authored cible légitime |
| addressId | absent | Identité technique ; autorité d'émission non qualifiée |
| businessYear | absent | Ne doit pas être saisi par le propriétaire ; autorité applicative non qualifiée |

Les champs physiques, référence et AddressLine peuvent légitimement rejoindre le snapshot owner-scoped. Un `GeographicPlaceId` ne peut y entrer que comme identité sélectionnée et validée, jamais dérivée des libellés.
