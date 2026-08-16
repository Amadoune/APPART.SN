# Dependency Graph

## Graphe logique

- F1 fournit le reader Geography requis par F4.
- F2 fournit l'Issuer et les primitives `addressIntentId` requises par F4.
- F3 fournit BusinessYearAuthority requis pour que F5 constate toutes les sources exécutables.
- F4 matérialise les faits et identités dans Property Authoring.
- F5 recertifie l'ensemble F1–F4 avant toute promotion.
- F6 dépend du GO F5 et compose F1/F2/F3, Authoring et RegisterProperty.
- F7 vérifie l'exécution réelle de F6.
- F8 dépend exclusivement du GO F7.

## Absence de dépendance future

| Étape | Entrées déjà disponibles au moment d'ouverture |
|---|---|
| F1 | Geography Domain, lifecycle et Blueprint certifié |
| F2 | AddressId, propertyId et Blueprint certifié ; ses tests peuvent fournir un addressIntentId typé |
| F3 | occurredAt stable, BusinessYear et Blueprint certifié |
| F4 | F1 et F2 GO ; F3 également fermé par l'ordre total |
| F5 | F1–F4 GO |
| F6 | F5 GO et toutes les autorités exécutables |
| F7 | F6 GO |
| F8 | F7 GO |

F2 ne dépend pas de la persistance F4 : il définit une fonction pure. F4 est responsable de matérialiser l'intention serveur que F2 consomme.
