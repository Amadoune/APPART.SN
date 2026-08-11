# Certification Note

## Verdict proposé

**GO PROPOSÉ — APPART.SN RELEASE CANDIDATE RC1 — RC1 CANDIDATE IDENTITY EVIDENCE AUTHORITY 01**

## Décision normative

L'autorité d'identité RC est la chaîne native Git : tag annoté, commit et tree. Le rapport post-matérialisation atteste leurs SHA après création et demeure externe à la baseline.

L'option A est impossible en raison de l'auto-référence cryptographique. L'option B est valide techniquement mais introduit inutilement un second commit et une ambiguïté entre la source candidate et son rapport. L'option C est compatible avec Git, simple, immutable et reproductible.

RC1-A2 est désormais délié de l'exigence contradictoire :

- il crée un commit candidat unique ;
- il crée le tag annoté sur ce commit exact ;
- il ne modifie pas ensuite la baseline ;
- il produit les SHA et contrôles dans une preuve d'exécution externe.

Aucun code, document RC1 existant, commit, tag ou staging n'a été modifié par cette décision.
