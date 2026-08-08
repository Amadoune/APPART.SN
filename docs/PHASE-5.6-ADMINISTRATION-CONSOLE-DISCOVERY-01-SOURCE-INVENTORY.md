# Administration Console — Source Inventory

Sources candidates autorisées pour les futures Foundations :

- principal et permissions via une frontière IAM versionnée ;
- cas, décisions et files via les lectures owner-scoped de Moderation ;
- états Notifications via ses Readers publics ou administratifs certifiés ;
- états Editorial Content et Operational SEO via les Readers ContentSeo certifiés ;
- audit opérationnel via les contrats append/read d'AdministrationAudit.

Sources interdites : Aggregates, PostgreSQL, migrations, mappers, caches,
projections internes, Runtime Health, Outboxes et tables de domaines externes.
