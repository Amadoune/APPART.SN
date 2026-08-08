# Legacy Migration & Reconciliation — Boundary Audit

## Décision d'owner

Le Discovery retient `LegacyMigration` comme owner unique de la coordination temporaire de migration, des checkpoints, des preuves, de la quarantaine et des rapprochements.

`LegacyMigration` ne devient jamais owner des données métier migrées. Il observe, qualifie, transforme selon une règle approuvée et propose une écriture au nouvel owner. Chaque owner cible reste seul habilité à accepter, rejeter ou mettre en quarantaine une donnée.

## Frontière

La frontière couvre exclusivement : inventaire des sources Legacy, classification des données, correspondance vers les owners cibles, préparation des vagues, rapprochements, quarantaine, reprise, rollback et cutover.

Les sources Legacy sont des sources d'observation et de preuve, pas des autorités de conception. Les capacités gelées ne peuvent être consommées ultérieurement que par leurs contrats publiés ou des ports d'import explicitement autorisés par une nouvelle Foundation.

## Invariants

- aucune écriture directe dans les tables privées d'un owner cible ;
- aucune modification d'une migration gelée ;
- aucune donnée ambiguë migrée activement ;
- aucune valeur manquante inventée ;
- aucune fusion sensible sans validation indépendante ;
- aucune source Legacy requise par le runtime après cutover ;
- aucune ouverture implicite de Transport, Routing ou Consumer.

## État du jalon

Le présent jalon est documentaire. Il ne crée aucun code, contrat, Provider, Runtime, Persistence, SQL, migration ou test. Aucune Foundation 5.7 n'est ouverte.
