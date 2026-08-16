# Certification Note

## Autorité arrêtée

RealEstateCatalog Domain possède la policy d’adressabilité Property.

La matrice fermée est :

- Country, Region, Department → `NotAddressable` ;
- City, District, Neighborhood → `Addressable`.

La décision est indépendante de `PropertyType`, accepte City comme granularité finale, conserve les niveaux larges pour la navigation F1 et applique le même résultat à RegisterProperty et ChangeAddress.

Le mapping Catalog est désormais entièrement qualifié dans l’ordre existence → merged → enabled → addressability. Aucun choix nécessaire à son implémentation ne reste implicite.

## Gouvernance

- F5-A1 Addressability Authority : décision complète.
- F5-A Blueprint : peut être rouvert uniquement pour completion avec cette policy.
- F5-A Implementation, F6/F7 et RC2 Iteration 11 : non ouvertes.
- Aucun PHP, Provider, binding, workspace ou migration modifié.

**GO PROPOSÉ**

**APPART.SN REALESTATE CATALOG / GEOGRAPHY FOUNDATION — F5-A1 GEOGRAPHIC PLACE ADDRESSABILITY AUTHORITY 01**
