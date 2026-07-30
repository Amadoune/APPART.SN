# Runtime Health Specification

Chaque exigence associe une capacité, un contrat attendu et sa criticité. Chaque enregistrement
associe la capacité à une implémentation, son état d'enregistrement, sa configuration et ses
dépendances manquantes.

Diagnostics fermés :

- `ComponentAbsent` : aucune déclaration pour la capacité ;
- `ImplementationNotRegistered` : déclaration sans implémentation utilisable ;
- `InvalidConfiguration` : configuration ou contrat attendu invalide ;
- `IncompatibleContract` : l'objet ne satisfait pas le contrat ;
- `DependencyMissing` : une dépendance déclarée manque.

Les diagnostics sont triés par capacité, code puis dépendance. À entrées identiques, le résultat est
strictement identique. Aucun timestamp, booléen global opaque ou fallback n'est utilisé.
