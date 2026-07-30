# Lead Eligibility Revision Continuity Policy

- la première version d'un Listing est exactement 1 ;
- chaque nouvelle version vaut `courante + 1` ;
- `effectiveAt` est strictement postérieur à celui de la version courante ;
- les révisions Listing et Advertiser sont égales sur identifiant, version et instant ;
- une version inférieure est obsolète ;
- une version supérieure non continue est refusée ;
- aucune valeur n'est générée ou corrigée par l'infrastructure.

Le `coherenceId` identifie le lot d'une version. La contrainte `(listing_id, coherence_id)` empêche sa réutilisation ambiguë dans le même flux.
