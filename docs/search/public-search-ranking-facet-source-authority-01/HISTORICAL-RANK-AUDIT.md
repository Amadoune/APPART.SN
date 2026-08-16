# Historical Rank Audit

| Valeur / usage | Emplacement | Qualification |
|---|---|---|
| 100 | `SearchDomainTestCase` | fixture Domain |
| 500 | `SearchDecisionFixture`, tests stores, commande First Listing | fixture / démonstration |
| 501 | test de divergence checksum | valeur de test |
| 600 | commande Local Public Fact Listing | démonstration |
| plage 0..10000 | `SearchRank` | invariant normatif uniquement |

`SearchProjectionPolicy` recopie le rang de `ListingProjectionSource`; `SearchDecisionMapper` le sérialise et le valide; le writer compare le checksum. Aucun de ces composants ne choisit la valeur.

Les autorités historiques déclarent :

- ranking owner SearchDiscovery ;
- ranking non transféré à Listing/SEO/Projection ;
- ranking anticipé hors périmètre ;
- ranking policy future en Phase 5.5A.

Aucune répétition de 500 dans les fixtures ne constitue une baseline.
