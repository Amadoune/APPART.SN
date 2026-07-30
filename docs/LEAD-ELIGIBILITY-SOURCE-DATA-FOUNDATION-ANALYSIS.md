# Lead Eligibility Source Data Foundation Analysis

## Décision

Un journal unique `contacts_leads.lead_eligibility_decisions` matérialise un lot cohérent par `ListingId` et version. Ce choix rend atomiques la décision Listing, la relation normative, la décision Advertiser et leur révision commune.

Le lot applicatif transporte deux `EligibilityRevision` explicites afin qu'une divergence reste représentable et soit refusée par `IncoherentRevision`. Seul un lot dont les deux révisions sont égales et UTC atteint PostgreSQL.

Les décisions sont reçues sous leurs enums historiques. Le mapper et le store ne connaissent aucune matrice métier, aucun Aggregate et aucun historique Listing.
