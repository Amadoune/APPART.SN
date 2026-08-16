# RC2 Handoff

RC2 Iteration 11 reste suspendue. Le Listing Published existant est préservé.

Ordre des futurs jalons :

1. `Public Search Ranking and Facet Source Authority 01` GO ;
2. réouverture et fermeture GO de cette Materialization Authority ;
3. `Public Search Decision Materialization Implementation 01` GO ;
4. catch-up du Listing RC2 par le pipeline normal ;
5. `SearchDecisionReader = Found` ;
6. replay de la même intention Projection ;
7. Projection matérialisée puis replay idempotent.

Le parcours Owner ne doit pas être recréé. Search UX/API reste fermé.

## Completion 01

Les jalons 1 et 2 sont désormais fermés documentairement. Le Listing `979cd5aa-ced1-48a1-8adf-8b29c843a0c2` est rattrapable par le matérialiseur normal avec rang `0`, facettes `[]` et ses révisions owners existantes.

SearchDecision candidate : decisionId `20d5ab5a-45ac-5a5e-9f15-d1357e9105a9`, version initiale `1`.

Après Implementation 01, réutiliser l'intention Projection existante `7cb365c9-2b79-47fd-8f05-2f2e188e042e` : aucune entrée ledger n'a été créée par les réponses NotReady, l'intention demeure légitimement rejouable. Ne pas créer de nouvelle intention par défaut.
