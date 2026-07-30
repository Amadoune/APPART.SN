# Phase 5.2B — Risk Register

| Risque | Gravité | Réponse Discovery |
|---|---:|---|
| contournement de F-06 pour rattacher un asset | critique | amendement de frontière bloquant |
| contenu actif, polyglot ou malveillant | critique | signature, décodage, scan, quarantaine fail-closed |
| decompression bomb ou épuisement mémoire | critique | limites streaming, pixels et temps CPU |
| objet stocké mais transaction échouée | haute | état temporaire, compensation et reconciliation |
| transaction validée mais objet absent | haute | preuve de présence/checksum avant Ready |
| double finalisation concurrente | haute | intentId, checksum et résultat fermé |
| dépassement de quota concurrent | haute | réservation atomique et ledger monotone |
| fuite par URL/object key | haute | clés opaques, URLs courtes et audience bornée |
| variante non reproductible | moyenne | recette et codec versionnés |
| suppression prématurée d’un asset référencé | haute | rétention et handoff explicites, aucune cascade |
| confusion scan technique/modération | moyenne | owners et états distincts |
| dépendance à un fournisseur objet | moyenne | port de stockage indépendant du SDK |

## Risques résiduels à fermer en Contracts Foundation

- tailles, dimensions, quotas et TTL exacts ;
- fournisseur de scan et comportement en indisponibilité ;
- recettes de variantes et politique de métadonnées EXIF ;
- contrat public de rattachement F-06 ;
- sémantique de purge après retrait, fermeture de compte ou litige.
