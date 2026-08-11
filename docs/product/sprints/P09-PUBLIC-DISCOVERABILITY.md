# P09 — Public Discoverability

## Périmètre livré

- Home : titre, description, canonical, robots, Open Graph et Twitter Card.
- Recherche : titre et description dynamiques selon transaction, ville et type ; canonical conservant le curseur de pagination.
- Fiche publique : canonical et directive robots issus de la projection certifiée ; Open Graph, Twitter Card et JSON-LD limités aux faits publics projetés.
- Sitemap : Home, Recherche et fiches `indexable` résolues par les contrats publics existants.
- Robots : zones publiques autorisées ; API, authoring, espaces propriétaire/professionnel et outils locaux exclus.

La réalisation n'ajoute aucune lecture d'Aggregate, aucun SQL UI, aucune règle Search et aucune donnée fictive.

## Décision produit

La capacité est implémentée, mais le parcours local n'est pas certifiable de bout en bout : la recherche réelle `Acheter / Dakar / Appartement` ne retourne actuellement aucune fiche publique. Le sitemap réel ne contient donc que Home et Recherche. Le sprint reste NO GO jusqu'à disponibilité d'au moins une projection publique indexable réelle.
