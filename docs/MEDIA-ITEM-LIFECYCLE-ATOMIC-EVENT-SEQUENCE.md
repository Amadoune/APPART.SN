# Media Item Lifecycle Atomic Event Sequence

1. Ouvrir la transaction PostgreSQL générique.
2. Exécuter l'orchestrateur 4.6D.
3. En cas de résultat autre que `Applied` ou `AlreadyApplied`, retourner sans événement.
4. Inspecter exactement le dernier append contextuel.
5. Résoudre le type événementiel depuis la transition inspectée.
6. Dériver `eventId` avec le contrat 4.6E.
7. Construire le payload Delivery opaque 4.6F.
8. Construire le message déterministe via le catalogue générique.
9. Écrire dans l'Outbox `media`.
10. Valider ensemble journal, contexte et Outbox.

Tout échec lève une défaillance interne, provoque le rollback complet et retourne `PersistenceCorrupted`.
