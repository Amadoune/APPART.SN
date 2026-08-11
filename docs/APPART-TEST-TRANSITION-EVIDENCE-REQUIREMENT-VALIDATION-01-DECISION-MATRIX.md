# Transition Evidence Requirement Validation 01 — Decision Matrix

| Dimension | Observation | Qualification |
|---|---|---|
| invariant métier | aucune policy ne lit la valeur | non démontré |
| invariant technique | constructeurs, mapper et persistence l'exigent | REQUIRED dans le modèle actuel |
| trace d'audit | conservée dans révision et événement | oui, descriptive |
| champ documentaire | texte libre sans catalogue | oui |
| vestige historique | présent dès la baseline sans justification comportementale | probable |
| abstraction générique | imposée uniformément à toute transition | trop générale pour les trois cas audités |

Décision par transition : `REDUNDANT` au regard de la décision métier, `REQUIRED` uniquement par la structure technique actuelle.
