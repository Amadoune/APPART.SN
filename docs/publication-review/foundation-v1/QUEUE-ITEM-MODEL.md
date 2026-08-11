# Queue Item Model

## Modèle minimal retenu

| Champ | Nécessité | Source |
|---|---|---|
| `queueItemId` | identité canonique de la candidature | dérivé mécaniquement de l'identité de l'événement source |
| `sourceEventId` | preuve de provenance et déduplication | événement Lifecycle |
| `listingId` | cible des commandes Lifecycle | payload événementiel |
| `submissionVersion` | distingue submit et resubmit, contrôle l'ordre | `publicationVersion` du payload |
| `submittedAt` | ordre temporel secondaire et audit | metadata `occurredAt` |
| `state` | `pending`, `claimed`, `completed` ou état terminal fermé | PublicationReview |
| `version` | optimistic locking | PublicationReview, initialisée à 1 |
| `claimedBy` | acteur du claim, nullable avant claim | commande de claim |
| `claimedAt` | instant du claim, nullable avant claim | commande de claim |

## Champs écartés de l'item

- `commandId` : identité d'une tentative de mutation, conservée dans le ledger de commandes, pas propriété de la candidature ;
- contenu, adresse, médias, prix ou autres données métier : inutiles à la file ;
- statut public ou données Projection/Search : non autoritatifs pour la revue ;
- décision IAM : vérifiée à l'entrée de commande, non copiée comme fait métier de l'item.

## Identités

`queueItemId` doit être une dérivation versionnée et déterministe de `sourceEventId`. Deux deliveries du même événement convergent donc sur le même item. Une resoumission porte un nouvel événement et une nouvelle `submissionVersion`, donc un nouvel item.

L'ordre paginé est fermé et stable :

`submittedAt ASC → listingId ASC → submissionVersion ASC → queueItemId ASC`.

Le curseur encode exclusivement cette clé ordonnée. La taille de page est bornée par le futur contrat.

## État terminal

Un item n'est pas supprimé. Il devient terminal après la transition Lifecycle correspondante ou après une clôture explicitement qualifiée. L'historique reste append-only ou révisionné ; aucune réouverture implicite d'un item terminal n'est permise.
