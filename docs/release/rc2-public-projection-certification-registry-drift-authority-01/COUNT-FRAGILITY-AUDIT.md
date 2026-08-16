# Count Fragility Audit

`assertCount(54, ...)` détecte une variation, mais ne distingue ni ajout autorisé, ni retrait compensé par un ajout, ni substitution d'identité. Il est donc utile comme signal mais fragile comme certification normative autonome.

La certification robuste doit comparer le set canonique réel au set explicitement autorisé, puis vérifier consumer, mode et version pour chaque entrée. Un count peut rester comme assertion secondaire dérivée du catalogue.
