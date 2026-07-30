# Unit of Work

## 1. Objet

L’Unit of Work coordonne une transaction applicative unique : écritures d’Aggregates, réservations techniques et événements Outbox. Elle ne coordonne aucune transaction distribuée et ne porte aucune règle métier.

## 2. Frontière officielle

Une exécution de cas d’usage ouvre au plus une Unit of Work d’écriture. Elle commence avant la première lecture destinée à être modifiée et se termine par validation ou annulation. Les lectures purement consultatives n’exigent pas d’Unit of Work.

Une Unit of Work appartient à une seule requête, commande ou tâche. Elle n’est ni globale, ni réutilisable, ni imbriquée. Une tentative d’imbrication est refusée.

## 3. Participants

Peuvent participer :

- les Repository du ou des Aggregates explicitement coordonnés par le cas d’usage ;
- les réservations d’unicité requises par leurs contrats ;
- l’Outbox locale ;
- les marqueurs techniques d’idempotence appartenant à la même décision.

Sont exclus : appels réseau, envoi de message, média externe, e-mail, index de recherche externe et tout effet non transactionnel.

## 4. Séquence normative

1. Ouvrir l’Unit of Work avec une identité de corrélation.
2. Charger des Aggregates détachés et mémoriser leurs versions attendues.
3. Exécuter le cas d’usage et les règles du Domaine.
4. Enregistrer les écritures et réservations attendues.
5. Collecter les événements produits, sans les publier.
6. Valider versions, doublons d’événements et cohérence technique.
7. Écrire états, réservations et Outbox dans la même transaction.
8. Valider durablement.
9. Après succès seulement, autoriser la distribution asynchrone et libérer les événements sur les instances appelantes.

## 5. Commit et rollback

Le commit est tout ou rien. Une erreur entraîne l’annulation de toutes les écritures de l’Unit of Work. Aucun gestionnaire d’événement externe n’est invoqué avant le commit.

Une erreur de commit n’est jamais masquée. L’état des objets en mémoire n’est pas une preuve de succès ; seule une nouvelle lecture durable l’est. L’Unit of Work terminée ne peut être réutilisée.

## 6. Plusieurs Aggregates

Plusieurs Aggregates ne partagent une transaction que lorsqu’un cas d’usage validé exige une cohérence immédiate. La proximité technique n’est pas une justification. Les conséquences dérivées, notamment SearchDiscovery et ContentSeo, utilisent l’Outbox et la cohérence différée.

L’ordre d’écriture est déterministe : réservations nécessaires, états d’Aggregates, événements Outbox, marqueurs d’idempotence. Cet ordre ne change pas les règles du Domaine.

## 7. Idempotence et concurrence

L’Unit of Work transporte la version attendue de chaque Aggregate. Un seul conflit annule l’ensemble. Elle ne fusionne pas deux états concurrents et ne relance pas automatiquement une mutation.

Un identifiant d’exécution protège seulement contre la répétition technique d’une même intention lorsqu’un contrat l’autorise ; il ne transforme pas deux intentions différentes en doublon.

## 8. Observabilité

Chaque exécution expose : corrélation, module, cas d’usage, durée, résultat, nombre d’écritures, nombre d’événements et catégorie d’échec. Aucun état métier complet, secret ou donnée personnelle n’est journalisé par défaut.

## 9. Critères d’acceptation

- transaction unique, non imbriquée et à durée bornée ;
- rollback complet ;
- aucune publication avant commit ;
- Outbox atomique avec les états ;
- concurrence optimiste obligatoire ;
- absence d’appel externe dans la transaction ;
- tests déterministes des succès, conflits et erreurs de commit.
