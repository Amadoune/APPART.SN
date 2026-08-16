# Existing Contract Matrix

| Besoin | Contrat existant | Verdict | Gap |
|---|---|---|---|
| découvrir les drafts owner | Authoring Portfolio | partiel | pas d’état Aggregate/Workflow |
| relire Property | ReadProperty / PropertyAuthoringStore | suffisant pour faits | type/hiérarchie Geography non exposés |
| relire Draft | ReadDraft / ListingDraftStore | suffisant | aucun |
| vérifier ownership | ListingOwnershipStore | suffisant | doit être composé |
| vérifier lifecycle | ListingRegistry + WorkflowStore | suffisant | doit être composé |
| restaurer Geography | PlaceRegistry + GeographicPlaceId | partiel | aucun Reader de hiérarchie dédié |
| restaurer preuve F4-A | aucun état persistant | insuffisant mais non requis en lecture | nouvelle preuve à l’édition |
| restaurer Media metadata | Media collection GET | suffisant | aucun |
| restaurer image Preview | aucun GET binaire owner-scoped | insuffisant | surface Media read privée |
| dériver step | complétudes existantes | partiel | policy Application à composer |
| snapshot atomique de reprise | aucun | insuffisant | Resume Reader V1 |

Conclusion : aucune nouvelle Foundation ou migration n’est nécessaire. Une composition Application et un petit gap HTTP Media sont requis.
