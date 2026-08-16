# Authority Model

| Responsabilité | Owner |
|---|---|
| Émission déterministe AddressId | RealEstateCatalog Application |
| Création/rotation de l'intention Address | Property Authoring serveur |
| Faits AddressLine et choix Geography | Propriétaire via Authoring |
| Validation Address/AddressId | RealEstateCatalog Domain |
| Persistance | PropertyRegistry |

L'Issuer possède uniquement la transformation d'identités stables en AddressId. Il ne lit ni n'interprète AddressLine, Place, owner IAM ou règles Property.
