# Public Projection — séquence Runtime finale

1. Laravel résout `PostgreSqlAggregateOutboxTransaction` et le Repository producteur participant.
2. La mutation Aggregate et l'append Outbox utilisent la PDO `pgsql` commune.
3. Le commit rend simultanément visibles Aggregate et message.
4. Laravel résout `PublicProjectionDeliveryWorker`.
5. Le Reader sélectionne le message et le ClaimManager pose la lease.
6. Le registre choisit le Consumer par type et version.
7. Le Consumer obtient une résolution mono-cible ou multi-cibles.
8. Le Lookup et les sources Runtime relisent les décisions durables.
9. L'Updater construit la projection depuis les décisions certifiées.
10. Le Store écrit la projection Current dans la génération Active.
11. Le Worker acknowledge la livraison.
12. `PublicListingQuery` relit le ReadModel durable.
13. Le contrôleur HTTP expose la canonical publique.
14. Runtime Health demeure `Healthy`.

En redelivery, les étapes 5 à 9 sont rejouées et le Store converge vers `AlreadyApplied`, sans double effet.
