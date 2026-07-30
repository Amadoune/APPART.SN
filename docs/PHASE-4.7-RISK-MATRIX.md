# Phase 4.7 — Risk Matrix

| Risque | Impact | Prévention | Verdict si non résolu |
|---|---|---|---|
| `Record` possède deux états cibles | seconde matrice implicite | contexte décisionnel fermé avant Workflow | NO GO 4.7A |
| motif absent ou contenu libre exposé | décision incorrecte / fuite | preuve de présence seulement ; texte hors workflow et événements | NO GO 4.7A / 4.7E |
| auto-approbation ou auto-rejet | violation four-eyes | auteurs et acteurs explicites, règle d'indépendance certifiée | NO GO 4.7A |
| concurrence avec le registre historique | double source de vérité | stratégie de coexistence et journal additif certifiés | NO GO 4.7B |
| rejeu classé depuis l'état courant | reconstruction métier | inspection exacte du dernier append | NO GO 4.7D |
| événements d'audit trop riches | exposition de données sensibles | payload minimal et matrice de confidentialité | NO GO 4.7E |
| port de routage sans résultat fermé | acquittement implicite | résultat fermé dès 4.7F | NO GO 4.7F |
| Consumer avant politique | retry/quarantaine implicites | matrice certifiée avant Consumer | NO GO 4.7H |
| owner Outbox supposé | collision de schéma | audit/résolution owner avant compatibilité | NO GO 4.7H |
| journal et Outbox séparés | événement fantôme ou perdu | transaction PostgreSQL unique | NO GO 4.7I |
| HTTP contourne l'intégrateur | perte d'atomicité | délégation unique après certification 4.7I | NO GO 4.7J |
