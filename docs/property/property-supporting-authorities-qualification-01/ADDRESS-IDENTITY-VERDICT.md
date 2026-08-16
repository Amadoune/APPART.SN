# Address Identity Verdict

**MISSING**

Aucune autorité certifiée n'émet aujourd'hui `AddressId` pour une nouvelle Address issue de Property Authoring. `AddressId::fromString` valide une valeur ; il ne la crée pas. Les constantes P02 et UUID de tests ne sont pas normatifs.

La première capacité absente est un émetteur d'identité appartenant à RealEstateCatalog, appelé au moment défini par la future promotion, et garantissant une même identité pour le replay de la même intention, une identité distincte pour une nouvelle adresse, ainsi qu'un traitement fermé des collisions.
