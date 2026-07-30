# Media Item Lifecycle Event Contract Analysis

Le Sprint **4.6E** définit exclusivement les faits terminalisant le statut d'un Media Item.

Le catalogue contient deux événements :

- `media.item.lifecycle.removed` ;
- `media.item.lifecycle.archived`.

La création reste hors workflow et ne produit aucun événement Lifecycle. Le contrat ne transporte aucune décision de `MediaCollection`.

## Confidentialité

Le payload exclut URL, caption, ordre, checksum de contenu, source, identité de collection, statut principal et média de remplacement. Il contient uniquement l'identité technique du Media Item, la transition, la version causale et les métadonnées explicites nécessaires.
