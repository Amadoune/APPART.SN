# Identity Access HTTP Runtime Activation — Decision Report

## Options

### A. Changement de binding uniquement

**REJETÉ.** Aucune implémentation concrète réelle n'est disponible comme cible. Les seules alternatives observées sont des fakes de tests.

### B. Implémentation complète

**REQUISE.** Un adapter applicatif doit implémenter le port et composer les autorités IAM existantes sans accès direct depuis le Controller. Pour débloquer le parcours produit, le premier périmètre cohérent doit au minimum couvrir Login, inspection de session, renouvellement et Logout, avec une politique explicite pour les autres opérations du catalogue.

### C. Autre chantier

**OUI, comme gouvernance de B.** La matérialisation doit être ouverte par un amendement IAM distinct. Elle ne relève ni du provisioning local, ni d'un changement d'environnement HTTPS, ni d'un simple sprint d'interface.

## Cause racine unique

La Foundation HTTP a certifié une frontière sans matérialiser son adapter applicatif réel ; le fallback de production est resté l'unique implémentation du port.

## Stratégie de reprise identifiée

1. ouvrir une décision d'autorité IAM Runtime Activation Implementation ;
2. qualifier les sources d'autorité de Login et Session ;
3. implémenter l'adapter réel derrière `IdentityAccessHttpRuntime` ;
4. prouver les réductions, transactions, idempotence et absence de fuite ;
5. remplacer le binding seulement après certification ;
6. provisionner ensuite un principal par une chaîne certifiée ;
7. rejouer la preuve navigateur HTTPS.

Aucune de ces étapes n'est ouverte par le présent audit.
