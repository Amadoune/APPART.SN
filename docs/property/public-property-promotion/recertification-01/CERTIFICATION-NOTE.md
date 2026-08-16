# Note de certification F7

## Verdict

**NO GO PROPOSÉ**

## Cause racine unique

Après une Promotion réussie et durable, l’écriture Aggregate effectuée par `SubmitListing` et la transition du Workflow sont dans la même closure Listing. Un résultat Workflow non réussi est retourné sans exception. La transaction committe donc potentiellement l’Aggregate alors que le Workflow n’a pas convergé.

Le scénario obligatoire F7 « échec Listing après Promotion → retry Promotion AlreadyApplied → reprise Listing » n’est pas déterministe et ne peut pas être certifié.

Aucune correction, nouvelle capacité, migration ou ouverture RC2 n’est réalisée.

APPART.SN PROPERTY FOUNDATION
F7 — PUBLIC PROPERTY PROMOTION RECERTIFICATION 01
