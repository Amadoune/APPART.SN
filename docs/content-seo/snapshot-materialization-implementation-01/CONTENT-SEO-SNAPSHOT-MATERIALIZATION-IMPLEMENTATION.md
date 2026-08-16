# Content SEO Snapshot Materialization Implementation 01

Le flux productif matérialise un `ContentSeoSourceDecision` à partir du seul `ListingId`. Il assemble les faits autoritatifs Listing, Search et Property, applique la policy canonical certifiée, puis délègue au reader/writer ContentSeo existant. Le catch-up réutilise strictement le même matérialiseur.

Aucune migration, lecture Projection, écriture Search, clock runtime ou identité aléatoire n'a été ajoutée.
