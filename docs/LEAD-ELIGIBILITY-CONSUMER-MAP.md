# Lead Eligibility Consumer Map

| Consommateur | Dépendance | Usage |
|---|---|---|
| `CreateLead` | `ListingCatalog` | appelle `contactabilityOf()` |
| `CreateLead` | `AdvertiserCatalog` | appelle `eligibilityFor()` |
| `CreateLead` | `LeadEligibilityProof` | combine et valide les deux évidences |
| Aggregate `Lead` | `LeadEligibilityProof` | conserve la preuve certifiée à la création et à la reconstitution |
| `FakeListingCatalog` de tests | `ListingCatalog` | harness contractuel existant |
| `FakeAdvertiserCatalog` de tests | `AdvertiserCatalog` | harness contractuel existant |
| `ContactsLeadsTestCase` | `LeadEligibilityProof` et évidences | fabrique une preuve valide pour les tests Domain |

Aucun adaptateur de production et aucun binding Runtime n'existent encore pour ces deux ports. Aucun autre consommateur ContactsLeads n'appelle leurs méthodes.
