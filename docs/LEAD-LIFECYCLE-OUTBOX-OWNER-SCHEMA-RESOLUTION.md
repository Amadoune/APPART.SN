# Lead Lifecycle Outbox Owner Schema Resolution

| Module | Schéma |
|---|---|
| `ContactsLeads` | `contacts_leads` |

La résolution est bijective : `for(ContactsLeads) = contacts_leads` et `moduleFor(contacts_leads) = ContactsLeads`.

La liste globale contient désormais sept owners : ListingLifecycle, RealEstateCatalog, Media, SearchDiscovery, ContentSeo, ReservationLifecycle et ContactsLeads. Toute valeur non reconnue reste explicitement refusée.
