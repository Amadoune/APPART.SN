# Outbox Blueprint

## 1. Objet

L’Outbox garantit qu’un événement produit par une mutation validée devient distribuable sans être perdu entre la persistance de l’Aggregate et le Dispatcher.

## 2. Propriété et frontière

Chaque module écrit ses événements dans une Outbox logique locale, au sein de la même plateforme PostgreSQL et de la même transaction que son Aggregate. L’Outbox est une responsabilité d’infrastructure ; elle ne modifie ni les événements ni les Aggregates.

## 3. Enveloppe normative

Chaque entrée contient conceptuellement : identifiant d’événement durable, module propriétaire, type d’événement, version de contrat, identité d’Aggregate, version résultante, index dans la mutation lorsqu’applicable, date métier, date d’enregistrement, corrélation, causalité, charge utile minimale et état de distribution.

L’identifiant durable est déterministe à partir du module, de l’Aggregate, de sa version et de l’index. Une même mutation ne peut créer deux entrées distinctes pour le même fait.

## 4. Écriture

Les événements sont capturés après exécution réussie du Domaine et avant commit. L’état de l’Aggregate, ses réservations et l’Outbox sont validés ensemble. Aucun événement n’est écrit pour une mutation refusée ou une transaction annulée.

La sérialisation applique une liste blanche explicite par type d’événement. Elle refuse les objets inconnus, secrets, hash, tokens, coordonnées personnelles non autorisées et détails de fournisseur.

## 5. Distribution

Un relay lit les entrées disponibles dans un ordre stable, les revendique pour une durée bornée, les remet au Dispatcher puis marque le résultat. La livraison est au moins une fois : tout consommateur doit être idempotent.

L’ordre est garanti pour un même Aggregate par `aggregateVersion` puis `eventIndex`. Aucun ordre global entre Aggregates n’est promis.

## 6. Échecs et reprises

Un échec incrémente le nombre de tentatives, conserve la dernière catégorie d’erreur et planifie une reprise avec temporisation bornée. Après le seuil décidé, l’entrée passe en quarantaine ; elle n’est ni supprimée ni déclarée réussie.

La reprise manuelle est auditée. Elle réutilise le même identifiant d’événement. Une entrée en cours dont le bail expire redevient distribuable.

## 7. Rétention

Les entrées distribuées sont conservées assez longtemps pour audit, diagnostic et reconstruction des projections selon la politique d’exploitation. La purge est progressive, mesurée et interdite tant qu’un consommateur déclaré est en retard au-delà de son point de reprise.

L’Outbox n’est pas l’historique métier officiel ni un substitut à l’état des Aggregates.

## 8. Sécurité et observabilité

Accès en moindre privilège, chiffrement des sauvegardes, absence de secrets, métriques de retard, volume, tentatives, quarantaines et âge maximal. Toute dérive de Search/SEO au-delà de son budget déclenche une alerte.

## 9. Critères d’acceptation

- atomicité avec la mutation ;
- identifiant et ordre déterministes ;
- livraison au moins une fois assumée ;
- consommateurs idempotents ;
- reprise et quarantaine auditables ;
- aucune donnée sensible non autorisée ;
- aucune publication avant commit ;
- reconstruction des projections possible dans la fenêtre de rétention.
