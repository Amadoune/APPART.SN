# Phase 4.8 — Governance Decision

## Autorité

**Amadoune Gueye**, Architecte logiciel et responsable de l'architecture du
programme APPART-REBUILD, exerce l'autorité de certification des décisions de
gouvernance de la Phase 4.8.

## Décisions

1. Amadoune Gueye exerce la fonction de Responsable Référentiels
   géographiques pour `Place Lifecycle` — **VALIDÉ**.
2. `Merged` est irréversiblement terminal et n'autorise aucune transition
   sortante — **VALIDÉ**.
3. un lieu `Disabled` peut être fusionné vers une cible valide, sous les
   invariants du Blueprint — **VALIDÉ**.
4. Catalogue, Search, Public Projection, SEO, Migration Legacy et les autres
   consommateurs aval consomment uniquement les faits de `Geography`; ils ne
   commandent aucune transition, ne reconstruisent pas le lifecycle et ne
   réécrivent aucune décision propriétaire — **VALIDÉ**.
5. la baseline officielle de la Phase 4.7 demeure inchangée et constitue
   exclusivement la baseline d'entrée de la Phase 4.8; aucune régression n'est
   autorisée — **VALIDÉ**.

## Baseline d'entrée gelée

- suite complète : **2 463 tests**, **47 544 assertions** ;
- PostgreSQL : **521 tests**, **2 201 assertions** ;
- Architecture : **500 tests**, **40 309 assertions** ;
- Runtime Health : **Healthy — 50 capacités**.

Ces chiffres ne représentent pas une nouvelle campagne exécutée pendant la
Discovery 4.8A.

## Verdict

```text
Phase 4.8A — Discovery / Blueprint
→ GO CERTIFIÉ
```

Le GO conditionnel est levé. Le seul sprint autorisé est :

```text
4.8A-R1 — Place Merge Context Contract
```

Aucun autre jalon de la Phase 4.8 ne peut être ouvert directement.

---

**Autorité de certification**

Amadoune Gueye

Architecte logiciel

Programme APPART-REBUILD
