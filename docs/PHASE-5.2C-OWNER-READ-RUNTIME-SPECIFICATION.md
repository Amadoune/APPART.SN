# Phase 5.2C — Owner Read Implementations Specification

## Statut proposé

**NO GO — source owner de résolution de mandat absente.**

## Professional Public Status

`ProfessionalPublicStatusReaderV1` est techniquement implémentable dans
l’enclave F-05 :

```text
ProfessionalId
→ ProfessionalStatusWorkflowStore interne F-05
→ Found Active      = Available
→ Found Suspended   = Unavailable
→ Missing           = Missing
→ Corrupted         = Corrupted
→ exception source  = DependencyUnavailable
```

Cette implémentation pourrait rester owner F-05, sans exposer le store au
consumer et sans modifier le lifecycle.

## Professional Mandate Resolution

`ProfessionalMandateResolverV1` n’est pas implémentable avec les sources
actuelles et les contraintes du sprint.

### Sources réellement disponibles

| Source | État | Compatibilité |
|---|---|---|
| `ProfessionalRegistry` | port historique sans implémentation production | interdit et non résolvable |
| Aggregate `Professional` | contient les mandats | lecture explicitement interdite |
| `RepresentativeMandate` / `RepresentativeId` | modèle owner interne | usage explicitement interdit |
| persistence de mandats | inexistante dans le dépôt | aucune source |
| reverse index AccountId → ProfessionalId | inexistant | aucune source |
| Events de mandat | existants | reconstruction explicitement interdite |
| SQL | aucun schéma mandat disponible | lecture directe interdite |

Les seules tables Professionals existantes concernent F-05 et Professional
Profile 5.2C. Elles ne stockent aucune association Account/mandat/Professional.

## Impossibilité déterministe

Sans source owner, une implémentation devrait nécessairement :

- assimiler AccountId et RepresentativeId ;
- lire l’Aggregate ou un registre non implémenté ;
- reconstruire les mandats depuis les événements ;
- créer une nouvelle persistence, projection ou migration ;
- retourner systématiquement `DependencyUnavailable`.

Les quatre premières options sont interdites. La dernière serait un fallback ou
Null Object, également interdit, et ne constituerait pas une implémentation
réelle.

## Conséquence

Implémenter uniquement le reader F-05 produirait une certification partielle et
laisserait HTTP non résolvable. Aucun code partiel n’est introduit pendant cette
représentation.

Une décision d’autorité doit préalablement autoriser la source owner
Professional Core minimale nécessaire au resolver, avec persistence additive
ou autre source canonique explicitement certifiée.
