# Legacy Migration & Reconciliation — Risk Register

| ID | Risque principal | Impact | Maîtrise proposée |
|---|---|---|---|
| R1 | Source Legacy prise pour autorité | Réintroduction des incohérences | acceptation obligatoire par le nouvel owner |
| R2 | Volumétrie inconnue | dimensionnement et couverture non démontrés | inventaire empreinté et profilage avant Foundation |
| R3 | Fusion incorrecte de personnes/professionnels | perte de propriété ou exposition | critères multiples et quatre yeux |
| R4 | Annonce attribuée ou publiée à tort | atteinte métier et publique | propriété prouvée, état incertain en quarantaine |
| R5 | Média mal rattaché ou sans droit | atteinte aux droits | checksum, preuve d'usage et validation owner |
| R6 | Géographie fusionnée à tort | recherche/SEO incohérents | référentiel approuvé et aliases conservés |
| R7 | PII sans finalité | risque légal et sécurité | minimisation, durée, accès restreint, suppression/archivage |
| R8 | Secret, session ou accès Legacy repris | compromission | exclusion structurelle et recréation contrôlée des accès |
| R9 | Perte d'URL prioritaire | perte SEO | registre URL exhaustif et décision par URL |
| R10 | Paiement ou droit commercial approximé | préjudice financier | preuve exacte et gate Finance/juridique dédiée |
| R11 | Écart de volume inexpliqué | perte silencieuse | équation volumétrique obligatoire et zéro écart |
| R12 | Identifiant réutilisé/collision | corruption référentielle | registre immutable de correspondances |
| R13 | Chronologie mal interprétée | mauvais état ou effet | timezone documentée et UTC canonique |
| R14 | Règle instable entre répétitions | cutover non reproductible | versionnage des règles et deux répétitions stables |
| R15 | Rollback écrasant des mutations nouvelles | perte post-cutover | capture du delta, isolation et rejeu validé |
| R16 | Dépendance Legacy persistante | runtime non autonome | suppression du chemin Legacy après cutover certifié |
| R17 | Modification d'une capacité gelée | rupture de certification | contrats/ports publiés uniquement et amendement explicite |

Les risques de volumétrie, stratégie de mots de passe, référentiel géographique, disponibilité des annonces, conservation légale et pouvoir de rollback restent ouverts et bloquants avant toute Foundation technique.
