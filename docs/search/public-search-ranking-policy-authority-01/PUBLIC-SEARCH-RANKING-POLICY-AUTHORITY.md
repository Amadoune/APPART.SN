# Public Search Ranking Policy Authority 01

## Décision

SearchDiscovery possède la politique de ranking public. `SearchRank` est un score de priorité non négatif : plus sa valeur est grande, plus une annonce est prioritaire. La plage normative est celle du Value Object, de 0 à 10000 inclus.

La politique v1 attribue `0`, valeur neutre, à toute annonce Published éligible. Aucun boost, signal commercial, temporel, comportemental ou UI n'entre dans v1. La policy est pure et déterministe.

Les facettes v1 sont la liste canonique vide. Cette décision est un périmètre produit explicite : Search UX/API et filtrage restent fermés. Elle n'est pas déduite du besoin RC2.

## Version

Identité normative : `public-search-ranking-policy-v1`.

## Verdict

**GO PROPOSÉ — PUBLIC SEARCH RANKING POLICY AUTHORITY 01**
