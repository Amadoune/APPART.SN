# Authority Evidence

Trois autorités successor indépendantes sont réunies :

1. APP_URL : insertion canonique unique dans `phpunit.xml`.
2. Architecture : sept blobs test-only introduits par R8 et conservés par R9/R10.
3. Pint : trois blobs `ordered_imports` introduits par R9 et conservés par R10.

Le défaut frontend était un `QUALIFICATION_ORDER_DEFECT`; le build Vite réel a été exécuté avant la reprise Feature. Les défauts Architecture et Pint sont des défauts d'ascendance successor, non des régressions produit.
