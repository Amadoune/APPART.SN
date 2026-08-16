# Pipeline Order Decision

Ordre normatif fermé :

1. identity/runtime ;
2. Composer restore ;
3. npm restore ;
4. frontend production build ;
5. Unit ;
6. Feature ;
7. Architecture ;
8. Foundation ;
9. PostgreSQL ;
10. PHPStan ;
11. Pint check ;
12. secret scan ;
13. Packaging A ;
14. Packaging B ;
15. archive/tree/manifest comparisons ;
16. clean-room B avec le même ordre.

Chaque étape reste fail-fast.
