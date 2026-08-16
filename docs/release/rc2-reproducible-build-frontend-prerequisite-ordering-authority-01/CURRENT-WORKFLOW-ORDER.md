# Current Workflow Order

Ordre RC2-R2 observé :

1. checkout et identité ;
2. setup PHP/Composer ;
3. setup Node ;
4. restore Composer/npm ;
5. Unit ;
6. Feature ;
7. Architecture ;
8. Foundation ;
9. PostgreSQL ;
10. PHPStan et Pint ;
11. frontend `npm run build` ;
12. packaging ;
13. upload evidence.

Le build frontend intervient trop tard pour Feature.
