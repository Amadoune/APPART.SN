# Geography Rehydration Evidence

`PropertyAuthoringState.geographicPlaceId` reste l'autorité. Le Reader relit le Place puis remonte sa hiérarchie autoritative pour exposer ID, type, parent et labels.

Il ne déduit jamais l'identité depuis les champs legacy `city` ou `neighborhood`. Il ne fabrique ni cursor ni limit F4-A : ces valeurs restent absentes au bootstrap. Une modification Geography doit donc obtenir une nouvelle preuve live par F4-A ; une simple lecture Resume ne rejoue pas le validateur.

Les cycles, Places désactivés, fusionnés ou hiérarchies incohérentes sont refusés fail-closed.
