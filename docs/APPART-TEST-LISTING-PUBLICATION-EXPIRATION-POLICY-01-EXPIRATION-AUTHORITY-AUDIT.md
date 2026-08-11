# Expiration Authority Audit

Jalon : `APPART.TEST LISTING PUBLICATION EXPIRATION POLICY 01`.

## Constat

`ExpirationDate` est possédée structurellement par Listing Lifecycle : elle est enregistrée par l'Aggregate, portée par `ListingPublished`, persistée dans le snapshot Listing et utilisée comme borne avant la transition automatique vers `Expired`.

Cette propriété du type ne constitue toutefois pas une règle de calcul. Le repository ne contient aucune autorité décidant de la durée applicable à une publication initiale.

| Source candidate | Owner | Règle observée | Disponible avant publication | Configurable | Déterministe | Statut |
|---|---|---|---:|---:|---:|---|
| `ExpirationDate` | Listing Lifecycle | date strictement postérieure à `occurredAt` | oui si fournie | non | validation seulement | PARTIAL |
| `PublishListing` | Listing Lifecycle Application | reçoit une date déjà construite | oui si appelant autoritatif | non | transport mécanique | PARTIAL |
| Aggregate `Listing` | Listing Lifecycle | valide et conserve la date | oui si fournie | non | oui | PARTIAL |
| `ListingTransitionPolicy` | Listing Lifecycle | aucune règle de durée | non | non | — | MISSING |
| Publication workflow | workflow technique | aucun champ/règle d'expiration | non | non | — | MISSING |
| Authoring | Property/Listing Authoring | aucune durée autoritative | non | non | — | MISSING |
| Moderation | Moderation | décide la publication, pas sa durée | non | non | — | MISSING |
| Configuration | Application | aucune clé qualifiée | non | non | — | MISSING |
| Tests/fixtures | tests | valeurs arbitraires `+100`, `+120`, `+200` | oui | non | oui localement | NON_AUTORITATIVE |
| Blueprint métier | documentation | durée standard « restant à arbitrer » | non | indéterminé | — | BLOCKED |

## Conclusion

L'owner naturel de la politique est Listing Lifecycle, mais son autorité de décision n'est pas encore matérialisée ni même paramétrée par une décision métier. Aucun calcul n'est recevable à ce jalon.
