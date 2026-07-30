# Active Generation Reader

## Contrat

`ActiveGenerationReader::read()` renvoie `ActiveGenerationReadResult` :

- `Found` avec un `PublicProjectionGeneration` Active ;
- `Missing` sans génération ;
- `Corrupted` sans génération exploitable.

Le modèle réutilise les identités et états certifiés du Projection Store sans les modifier.

## Usage

Le futur Runtime Source peut obtenir une identité de génération uniquement lorsque le résultat est `Found`. `Missing` et `Corrupted` restent des blocages explicites ; ils ne déclenchent ni création, ni bascule, ni réparation.

## Limites

Ce composant ne lit aucune projection, ne gère aucune génération et ne fournit aucun binding Laravel. Il ne remplace ni le Generation Manager ni le Validator.
