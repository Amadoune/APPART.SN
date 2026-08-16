# PHPUnit Environment Correction

`phpunit.xml` conserve ses valeurs génériques non secrètes. Le bootstrap DB est volontairement fourni par variables de processus afin que le preflight puisse le valider avant PHPUnit et qu'aucun fallback local ne soit masqué.
