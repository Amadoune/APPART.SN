# Geography Rehydration

L’autorité persistée est `PropertyAuthoringState.geographicPlaceId`. `AddressIntentId` prouve l’identité de l’adresse produite, mais le tuple F4-A `{type,parent,cursor,limit}` n’est pas persisté.

Conséquences :

- le Resume Reader relit le Place par son ID dans le `PlaceRegistry` ;
- il reconstruit la hiérarchie visible en remontant les parents autoritatifs ;
- il expose ID, type, parent et labels courants ;
- il ne fabrique jamais un ancien cursor/limit ;
- il ne rejoue pas `GeographySelectionReplayValidatorV1` pour une lecture inchangée.

Si l’utilisateur modifie Geography, l’UI repart de F4-A et obtient une nouvelle preuve live avant mutation. Le contexte historique de pagination n’est pas nécessaire au futur Submit, qui utilise les faits déjà persistés.

Le catalogue `GeographicPlaceCatalog` actuel ne fournit que le statut. La composition de reprise doit utiliser la lecture Geography owner du Place, sans modifier F1/F4/F4-A.
