# Phase 4.8F — Place Lifecycle Event Contract Certification

## Livrables

- catalogue fermé de trois événements;
- payload V1 minimal;
- identité SHA-256 déterministe;
- matrice exhaustive transitions ↔ événements;
- politique de confidentialité;
- tests unitaires contractuels et tests d'architecture.

## Garanties

- chaque transition certifiée produit exactement un fait;
- toute transition non certifiée est refusée;
- les deux transitions vers `Merged` restent distinguables par leur état
  précédent;
- une action différente produit une identité différente;
- la cible participe à l'identité et au payload uniquement pour une fusion;
- aucune donnée de gouvernance ou preuve observée n'est exposée;
- aucun Transport, Routing, Outbox, Runtime ou HTTP n'est introduit;
- les fondations 4.8A-R1 à 4.8E restent inchangées.

## Validation ciblée

```text
14 / 14 tests
71 assertions
```

## Validation finale

```text
Architecture : 517 / 517 tests, 41 843 assertions
Suite complète : 2 539 / 2 539 tests, 49 335 assertions
Pint : PASS
Analyse statique ciblée : 0 erreur
```

Aucune campagne PostgreSQL distincte n'est revendiquée : 4.8F ne modifie ni
persistance, ni migration, ni requête.

## Verdict

**GO CERTIFIÉ**.

Le sprint 4.8F est fermé. Le seul sprint autorisé est
**4.8G — Place Lifecycle Event Transport**.
