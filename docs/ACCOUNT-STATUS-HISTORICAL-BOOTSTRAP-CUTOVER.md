# Account Status — Historical Bootstrap and Cutover

## Séquence d'amorçage future

```text
qualifier l'existence historique
    → absent : AccountMissing
    → présent : lire l'entrée lifecycle
        → présente : utiliser état/version lifecycle
        → absente : LegacyUninitialized
            → dériver Active/Suspended depuis le booléen historique
            → fixer version lifecycle 0
            → append d'amorçage unique
            → poursuivre sur la source lifecycle
```

Cette séquence est une exigence pour la future Persistence Foundation, pas une
implémentation.

## Barrière de cutover

Avant toute exposition Runtime :

1. tous les comptes existants doivent être amorçables de façon déterministe;
2. les chemins `SuspendAccount` et `ReactivateAccount` ne doivent pas être
   exposés par le nouveau Runtime;
3. les consommateurs du statut doivent désigner la source lifecycle;
4. aucun composant ne doit relire le booléen historique après amorçage;
5. le rollback doit revenir entièrement au régime antérieur sans avoir écrit
   partiellement dans les deux sources.

## Rollback conceptuel

Tant que le Runtime 4.9 n'est pas activé, les entrées d'amorçage sont
préparatoires et ne changent pas `Account`. Un rollback de la future tranche
supprime uniquement les artefacts lifecycle selon sa migration réversible.

Après activation et première transition lifecycle, revenir au booléen
historique serait une perte d'autorité et est interdit sans amendement,
reconstruction certifiée et fenêtre de cutover dédiée.
