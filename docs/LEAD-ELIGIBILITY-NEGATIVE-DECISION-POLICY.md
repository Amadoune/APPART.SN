# Lead Eligibility Negative Decision Policy

## Principe

Toute décision négative est un fait explicite, versionné et attribué à un propriétaire. L'absence d'une ligne, une exception de lecture ou une indisponibilité ne constituent jamais une décision métier.

| Décision | Propriétaire | Signification |
|---|---|---|
| Listing `Missing` | ListingLifecycle | non-existence explicitement certifiée à la révision |
| Listing `NotPublished` | ListingLifecycle | existence, mais état temporairement non contactable |
| Listing `Closed` | ListingLifecycle | existence, mais cycle public fermé |
| Advertiser `Missing` | ContactsLeads | non-existence explicitement certifiée |
| Advertiser `Suspended` | ContactsLeads | réception des Leads explicitement suspendue |
| Advertiser `NotListingRecipient` | ContactsLeads | existence et activité, mais relation normative différente |

Chaque décision négative porte une `EligibilityRevision` complète. Les futurs readers distingueront donc `source absente/corrompue` d'une décision métier `Missing`.
