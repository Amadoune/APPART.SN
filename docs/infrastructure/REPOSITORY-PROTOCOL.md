# Repository Protocol

## 1. Statut et objet

Ce document est normatif pour la Phase 2. Il définit le comportement de tout adaptateur de persistance satisfaisant un Registry public de la DDD Foundation. Le vocabulaire `Registry` reste celui des contrats du cœur ; `Repository` désigne uniquement leur réalisation périphérique future.

## 2. Principes non négociables

- Un Repository est propre à un Aggregate Root et à son module.
- Il dépend du contrat public à satisfaire ; le Domaine ne dépend jamais de lui.
- Il persiste et reconstruit un Aggregate complet, jamais un graphe partiellement chargé.
- Il ne contient aucune règle métier, transition, politique, valeur par défaut métier ou décision d’autorisation.
- Il ne retourne qu’un Aggregate détaché, reconstruit officiellement et sans événement déjà publié.
- Il ne partage pas de modèle persistant entre modules.
- Il ne fournit aucune opération générique de type « sauvegarder n’importe quel objet ».

## 3. Protocole de lecture

Une lecture par identité produit exactement l’un des résultats suivants : Aggregate détaché ou absence explicite. La lecture utilise le point de reconstruction officiel de l’Aggregate, restaure son identité, sa version, son état complet et son historique requis, puis vérifie que `releaseEvents()` est vide.

Une donnée persistée invalide provoque une erreur d’intégrité technique. Elle ne doit jamais être corrigée silencieusement, normalisée différemment du Domaine ou transformée en absence.

## 4. Protocole d’ajout

`add()` est atomique. Il réserve dans la même transaction toutes les identités et clés d’unicité déclarées par le contrat : identité d’Aggregate, référence métier, signature, identifiant enfant ou autre réservation durable. Un conflit est traduit vers l’erreur explicite prévue par le port. Aucun état partiel ne devient visible.

L’Aggregate fourni reste détaché. Le Repository n’efface pas ses événements et ne le remplace pas par une instance liée à la persistance.

## 5. Protocole de sauvegarde conditionnelle

`save(aggregate, expectedVersion)` réussit uniquement si la version durable courante est exactement `expectedVersion`. La version persistée après succès est la version de l’Aggregate. Une absence, une version différente ou une écriture concurrente produit le conflit concurrent défini par le contrat.

Une sauvegarde ne déclenche pas de nouvelle règle métier et n’incrémente jamais elle-même la version. Une version régressive, inchangée alors qu’un changement est attendu, ou incohérente est refusée comme erreur d’intégrité.

## 6. Réservations atomiques

Les opérations combinant sauvegarde et réservation (`saveWith…Reservation`) forment une seule unité atomique. La réservation est durable et respecte sa politique de réutilisation. En cas d’échec, ni la réservation ni l’état de l’Aggregate ni les événements Outbox ne deviennent visibles.

## 7. Événements

Le Repository lit les événements non libérés sans décider de leur contenu. Leur écriture Outbox appartient à l’Unit of Work. Après validation durable, l’appelant peut les libérer. Un échec conserve l’Aggregate appelant dans son état détaché, mais aucune mutation n’est visible par une nouvelle lecture.

La clé d’un événement durable est composée de l’identité d’Aggregate, de `aggregateVersion` et, lorsqu’il existe, de `eventIndex`.

## 8. Erreurs et sécurité

- Les erreurs de concurrence ne sont jamais converties en succès.
- Aucun retry automatique ne rejoue une commande métier ; seule l’orchestration peut recommencer avec une nouvelle lecture.
- Les détails internes de persistance ne traversent pas le port.
- Secrets et données personnelles non nécessaires ne figurent ni dans les erreurs ni dans les journaux.
- Les lectures inter-modules directes sont interdites.

## 9. Critères d’acceptation

- reconstruction complète et sans événement résiduel ;
- ajout et réservations atomiques ;
- sauvegarde conditionnelle sur version attendue ;
- Aggregate détaché avant et après l’appel ;
- traduction déterministe de chaque conflit ;
- aucune logique métier ou dépendance Laravel ;
- compatibilité avec Unit of Work, Outbox et stratégie de tests.
