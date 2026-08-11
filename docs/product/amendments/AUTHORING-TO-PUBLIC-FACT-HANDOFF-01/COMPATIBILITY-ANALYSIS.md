# Compatibility Analysis

## Listings historiques

Les snapshots et événements existants restent lisibles sans réécriture. Ils sont qualifiés `publicFacts=unqualified` tant qu'aucun handoff autoritatif n'existe. Ils restent visibles dans les lectures générales si leur projection est autrement valide, mais sont exclus des requêtes filtrées par transaction. Aucun défaut ne doit être converti en `sale` ou `rent` par défaut.

## P02

P02 demeure inchangé, publié et accessible par canonical path. Sa commande n'a créé aucun `ListingDraftState` porteur de transaction ; il reste donc `unqualified` pour le filtre transaction. Le rendre éligible à Acheter/Louer nécessitera une décision distincte autorisant un nouveau cycle owner-scoped ou une reprise locale explicite. Le présent audit ne la réalise pas.

## Projections existantes

Le format futur doit être backward-compatible : transaction nullable avec provenance/qualification interne, jamais une valeur par défaut. Un rebuild conserve les enregistrements historiques non qualifiés et propage mécaniquement les faits scellés pour les nouveaux Listings.

## Snapshots

Aucune migration historique n'est modifiée. Une implémentation future devra être additive : nouveau champ nullable versionné ou journal compagnon owner-scoped, avec checksum canonique. Les snapshots anciens continuent d'être reconstitués.

## Événements

Les événements historiques restent immuables. Le contrat peut produire un nouvel événement versionné ou enrichir uniquement une nouvelle version d'événement ; il ne modifie pas les payloads déjà certifiés.

## Conclusion de compatibilité

La compatibilité de lecture et de rebuild est démontrable en mode fail-closed. Elle ne permet pas d'attribuer rétroactivement une transaction aux Listings historiques. P03 restera bloqué pour la donnée P02 tant qu'une autorité distincte n'aura pas qualifié cette reprise.
