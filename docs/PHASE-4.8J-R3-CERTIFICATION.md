# Phase 4.8J-R3 — Generic Delivery Output Type Compatibility Certification

## Périmètre audité

- retour checksum objet contre retour scalaire;
- résultats Place contre résultats génériques;
- ownership des calculs, décisions et traductions;
- conservation des fondations 4.8G et 4.8I;
- compatibilité des neuf owners historiques.

## Décisions

1. Le calcul typé SHA-256 devient l'opération explicitement nommée
   `transportChecksum()`.
2. `checksum(): string` expose exactement la propriété `value`, sans nouveau
   calcul.
3. Le Consumer existant traduit, après décision 4.8I :
   `Acknowledged → Consumed`, `Retry → RetryableFailure`,
   `Quarantined → PermanentFailure`.
4. Aucun port générique et aucun owner historique n'est modifié.
5. Aucun adapter ni composant spécialisé supplémentaire n'est autorisé.

## Contrôles de certification

- solution minimale et versionnée : conforme;
- valeurs et octets 4.8G : conservés;
- identités 4.8G : conservées;
- politique Ack / Retry / Quarantine : conservée;
- matrice totale et bijective sur les sorties Place : conforme;
- propriétaire unique de traduction : conforme;
- compatibilité des neuf owners : conforme;
- absence d'implémentation Outbox : conforme.

## Validations

```text
Tests documentaires ciblés : 6 / 6, 28 assertions
Architecture complète : 530 / 530, 42 207 assertions
Suite complète : 2 579 / 2 579, 49 770 assertions
```

Aucune campagne PostgreSQL n'est revendiquée : R3 ne crée ni migration, ni
table, ni requête, ni stratégie de persistance.

## Verdict

```text
4.8J-R3 → GO CERTIFIÉ et fermé
4.8J → AUTORISÉ
```

La décision de l'autorité de certification autorise exclusivement la reprise
de 4.8J selon les amendements R1, R2 et R3.
