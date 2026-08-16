# Hierarchy model

| Type | Parent admis |
|---|---|
| Country | aucun, racine |
| Region | Country |
| Department | Region |
| City | Region ou Department |
| District | Department ou City |
| Neighborhood | City ou District |

La chaîne suit exclusivement `parent_place_id` jusqu'à Country. Toute autre combinaison, cycle ou racine manquante est corrompue.
