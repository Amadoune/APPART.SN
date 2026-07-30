# Repository Testing Strategy

## 1. Objet

Cette stratégie définit les preuves exigées de chaque futur Repository PostgreSQL. Les doubles de test du Domaine ne prouvent pas la conformité d’un adaptateur réel.

## 2. Pyramide de tests

1. **Tests de contrat partagés** : mêmes scénarios exécutés contre le fake de référence et l’adaptateur PostgreSQL.
2. **Tests de mapping** : aller-retour complet de chaque Aggregate et de chacun de ses états significatifs.
3. **Tests d’intégration transactionnelle** : contraintes, concurrence, rollback, réservations et Outbox sur PostgreSQL réel de la version approuvée.
4. **Tests de concurrence** : deux connexions et ordonnancements contrôlés, sans simulation séquentielle trompeuse.
5. **Tests d’exploitation** : restauration, reprise Outbox, quarantaine et comportement après interruption.

## 3. Contrat minimal commun

Chaque Registry doit démontrer :

- absence sur identité inconnue ;
- ajout puis lecture détachée ;
- conflit d’identité et de chaque clé unique ;
- sauvegarde avec version correcte ;
- conflit avec version périmée ;
- aucune mutation visible après échec ;
- événements vides après rechargement ;
- événements de l’instance appelante non rejoués ;
- historique et états terminaux préservés ;
- deux lectures produisant des instances indépendantes.

## 4. Réservations et idempotence

Pour chaque opération atomique spécialisée : succès, doublon, conflit concurrent, échec après réservation simulé, rollback complet et nouvelle tentative. Lead vérifie la fenêtre glissante. Payment distingue rejeu de même intention et conflit d’intention. Les identités enfants à réservation durable ne changent jamais silencieusement de propriétaire.

## 5. Mapping

Chaque Aggregate est testé dans son état minimal, chaque état terminal, avec collections vides et remplies, historiques, aliases ou preuves, valeurs limites et version non triviale. Après reconstruction : propriétés observables identiques, version identique, aucun événement et prochaine mutation valide produisant la version suivante.

## 6. Concurrence réelle

Les scénarios utilisent au moins deux unités d’exécution indépendantes : mises à jour concurrentes du même Aggregate, réservations simultanées, ajout de même identité, même référence métier et livraison Outbox concurrente. Un seul gagnant est observé lorsque l’unicité l’exige ; tous les perdants reçoivent l’erreur contractuelle attendue.

## 7. Unit of Work et Outbox

Vérifier : état plus événement validés ensemble, aucun événement après rollback, aucun état sans événement requis, doublon d’événement impossible, ordre version/index, reprise après interruption, bail expiré et consommateur idempotent.

## 8. Environnement

Les tests d’intégration utilisent PostgreSQL 18.x réel, isolé et jetable. Aucun moteur substitut n’est accepté pour prouver les transactions, contraintes ou la concurrence. Chaque test contrôle ses données, ne dépend pas de l’ordre de la suite et laisse l’environnement propre.

## 9. Qualité et CI

Les tests de contrat et mapping s’exécutent à chaque changement d’adaptateur. Les scénarios de concurrence et restauration s’exécutent dans un pipeline dédié mais bloquant avant fusion ou livraison selon leur durée. Toute instabilité est un défaut ; aucune relance automatique ne transforme un test intermittent en succès.

## 10. Matrice d’acceptation

Un Repository n’est recevable que si : contrat partagé, mapping, concurrence, rollback, réservations, Outbox, sécurité des journaux et performance minimale sont tous au vert. Une dérogation exige un ADR, un propriétaire et une échéance ; elle ne peut concerner l’intégrité ou le rollback.

## 11. Critères d’acceptation

- PostgreSQL réel ;
- tests de contrat réutilisables ;
- reconstruction et détachement prouvés ;
- concurrence réellement simultanée ;
- rollback et Outbox atomiques ;
- idempotence propre à chaque contrat ;
- aucun secret ni donnée de production ;
- résultats déterministes et bloquants.
