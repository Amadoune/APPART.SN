# F1 / Workspace Compatibility

F1 est un reader général de découverte hiérarchique. Il peut continuer à exposer Country, Region, Department, City, District et Neighborhood : les niveaux non adressables sont nécessaires pour naviguer jusqu’aux niveaux locaux.

Le workspace doit à terme distinguer navigation et sélection finale :

- Country, Region et Department restent navigables mais ne doivent pas être validés comme choix final d’Address Property ;
- City, District et Neighborhood peuvent être sélectionnés comme choix final ;
- l’absence de niveau enfant n’empêche pas de finaliser une City ou un District.

Cette Authority ne modifie ni F1 ni le workspace. Même si une interface transmet un niveau non adressable, la Promotion relit le Place courant et `GeographicPlaceCatalog` refuse finalement la registration. Cette revalidation reste l’autorité terminale.
