# Preuves Geography

F6 appelle le `GeographyBackedGeographicPlaceCatalog` productif depuis `RegisterProperty`. La source terminale est le `PostgreSqlPlaceRepository` F0.

Preuves déjà certifiées et rejouées durant F6 :

- City active et non fusionnée : Promotion `Applied` ;
- Place disabled : `DomainRejected`, aucune Property ;
- type non adressable : `DomainRejected`, aucune Property ;
- catalogue et policy F5-A inchangés.

Les branches NotFound/Merged et corruption restent définies fail-closed par le catalogue et la réduction F6. Leur campagne F7 détaillée n’a pas été poursuivie après la première divergence obligatoire de la section 18.
