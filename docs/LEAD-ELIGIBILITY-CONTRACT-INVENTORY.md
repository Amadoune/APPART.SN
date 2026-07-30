# Lead Eligibility Contract Inventory

## Contrats ContactsLeads existants

| Type | Namespace propriétaire | Responsabilité |
|---|---|---|
| `ListingCatalog` | `ContactsLeads\Application\Contract` | fournir `ListingContactEvidence` pour un `ListingId` |
| `AdvertiserCatalog` | `ContactsLeads\Application\Contract` | fournir `AdvertiserEligibilityEvidence` pour un couple annonceur/Listing |
| `ListingContactEvidence` | `ContactsLeads\Domain\ValueObject` | état de contactabilité et révision figée |
| `AdvertiserEligibilityEvidence` | `ContactsLeads\Domain\ValueObject` | état d'éligibilité et révision figée |
| `LeadEligibilityProof` | `ContactsLeads\Domain\ValueObject` | preuve cohérente acceptée par l'Aggregate, réduite à la révision certifiée |

Les interfaces `ListingCatalog` appartenant à SearchDiscovery, ContentSeo et ModerationReports sont des ports distincts par bounded context. Elles ne participent pas à l'éligibilité Lead et ne constituent pas des contrats concurrents.

## Modèle actuel

```text
ListingCatalog ─────► ListingContactEvidence ─┐
                                                ├─► LeadEligibilityProof::fromEvidence()
AdvertiserCatalog ─► AdvertiserEligibilityEvidence ┘
```

Les évidences sont immuables. `LeadEligibilityProof` vérifie contactabilité, éligibilité et égalité des révisions ; cette règle Domain préexistante n'est pas déplacée.
