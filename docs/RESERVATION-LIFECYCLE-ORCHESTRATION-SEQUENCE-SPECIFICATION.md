# Reservation Lifecycle Orchestration Sequence Specification

La séquence est obligatoire et non interchangeable :

1. lire le dernier snapshot par `ReservationId` ;
2. arrêter sur `Missing` ou `Corrupted` ;
3. comparer la version stockée à `expectedVersion` ;
4. demander la décision au workflow ;
5. arrêter sur `Denied` en propageant son diagnostic ;
6. transmettre exactement la transition autorisée au store ;
7. utiliser exclusivement `expectedVersion + 1` ;
8. classer le résultat d'écriture.

Une sortie anticipée n'effectue aucun append. Une exécution effectue au maximum une lecture, une décision et une écriture. Aucune horloge, donnée externe ou Aggregate ne participe au traitement.
