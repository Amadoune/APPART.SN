# Phase 4.7B — Persistence Mapping Matrix

| Entrée | entry_kind | source_checksum | mirror_checksum |
|---|---|---|---|
| checkpoint V1 | enrollment | obligatoire | interdit |
| mutation Record V1 | transition | interdit | obligatoire |
| mutation Approve V1 | transition | interdit | obligatoire |
| mutation Reject V1 | transition | interdit | obligatoire |

Le mapper calcule `entry_checksum` sur identité, version, état précédent, état courant et action. Il restaure uniquement un `AdministrativeActionLifecycleStoredState`.

La mutation du miroir est mécanique :

- Record ajoute l'entrée d'audit historique ;
- Approve ajoute approbation, décision et audit ;
- Reject ajoute décision et audit ;
- les trois mettent à jour statut, instant et version du root historique.
