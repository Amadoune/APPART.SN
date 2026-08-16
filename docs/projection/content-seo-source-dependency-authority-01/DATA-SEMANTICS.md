# Data Semantics

| Donnée snapshot | Owner normatif | Source attendue | Consommée par Projection |
|---|---|---|---|
| headline, description | Listing Authoring, transportés sous contrôle ContentSeo | `ListingSeoSource` | oui, via `ListingSeoDecisionPolicy` |
| canonical path | ContentSeo | `ListingSeoSource` | oui |
| Listing state/dates | ListingLifecycle | `ListingSeoSource` | oui |
| Search state | SearchDiscovery | `SearchSeoSource` | oui |
| Property state/type/city | Property/Geography | `PropertySeoSource` | oui |
| revisions | owners respectifs | trois `SeoSourceRevision` | watermark/cohérence |
| canonical history | ContentSeo | snapshot | oui |
| decision time | ContentSeo | `decisionAt` | oui |

Indexabilité, robots, JSON-LD et page treatment sont décidés ensuite par la policy ContentSeo, jamais par Projection.
