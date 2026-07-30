# Media Item Lifecycle Runtime Orchestration Analysis

Le Sprint **4.6D** coordonne exclusivement le workflow 4.6A et les ports contextuels 4.6C-R2.

La commande contient :

- `MediaItemLifecycleId` ;
- l'action demandée ;
- le contexte V1 complet déjà produit à partir d'une décision `MediaCollection`.

L'orchestrateur ne reçoit ni repository de collection, ni booléen `isPrimary`, ni API de sélection d'un remplaçant. Il ne peut donc pas reconstruire une décision de collection.

## Chemins

Le chemin nominal appelle le workflow uniquement lorsque la version courante égale `expectedVersion`. Le chemin de rejeu est activé uniquement lorsque la version courante égale `expectedVersion + 1`.

La politique de rejeu compare l'action demandée et le checksum du contexte au snapshot exact. Elle ne reçoit aucun état à partir duquel reconstruire une transition.
