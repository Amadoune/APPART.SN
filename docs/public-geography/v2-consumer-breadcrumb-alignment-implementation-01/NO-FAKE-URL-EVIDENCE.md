# No fake URL evidence

Le modèle V2 ne possède aucun champ URL ou slug. Le mapper rejette explicitement un item V2 contenant `url` ou `slug`. Les adapters ne construisent ni `#`, ni `/`, ni `javascript:`, ni `/geography/{id}`.

Les tests Unit, Feature et Architecture couvrent l’absence d’URL synthétique et de dépendance Search dans les contrats V2.
