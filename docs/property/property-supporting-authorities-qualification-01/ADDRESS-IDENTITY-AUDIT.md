# Address Identity Audit

`AddressId` est un Value Object UUID non nullable dans `Address`. `RegisterProperty`, `ChangeAddress` et le constructeur `Address` reçoivent une identité déjà produite ; aucun factory ou port d'émission n'est observé.

Les usages identifiés dans P02, tests, fixtures et harnesses fournissent des UUID littéraux ou formatés. Ils démontrent seulement la validité du Value Object, pas une convention normative.

| Propriété requise | État observé |
|---|---|
| Owner de l'émission | Non défini |
| Moment d'émission | Non défini |
| Stabilité au replay/retry | Non définie |
| Collision | Validation UUID seulement ; aucune politique d'émission |
| Relation commandId/propertyId | Non définie |
| Client autorisé à fournir l'ID | Aucun contrat normatif |

L'identité est technique, mais cela ne rend ni UUID aléatoire ni dérivation arbitraire acceptables.
