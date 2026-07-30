# Lead Eligibility Revision Specification

## Propriétaire

ContactsLeads possède la cohérence du lot d'éligibilité. Le Value Object historique `EligibilityRevision` reste inchangé.

## Champs

- `coherenceId` : UUID explicite identifiant un lot de décisions Listing et Advertiser ;
- `version` : entier strictement positif et strictement croissant pour un `ListingId` ;
- `effectiveAt` : instant UTC explicite auquel les faits deviennent applicables.

## Génération

Aucune valeur n'est générée par l'infrastructure. Le futur producteur fournit les trois valeurs à partir de la décision propriétaire. Aucun `now()`, UUID aléatoire ou timestamp PostgreSQL implicite n'est autorisé.

## Propagation

La même instance logique de révision accompagne la contactabilité Listing et l'éligibilité Advertiser. Une différence de `coherenceId`, `version` ou `effectiveAt` rend le lot incohérent et interdit sa matérialisation.

Les décisions positives et négatives suivent exactement la même continuité. Une révision plus ancienne ne peut remplacer une révision courante.
