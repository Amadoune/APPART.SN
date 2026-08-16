# Workflow Order Correction

Ordre corrigé :

1. restore Composer/npm ;
2. frontend production build ;
3. Unit, Feature, Architecture et Foundation ;
4. PostgreSQL ;
5. PHPStan et Pint ;
6. packaging.

La commande reste exactement `npm run build`. Elle apparaît une seule fois. Tous les autres gates, timeouts, restore et artifacts sont préservés.
