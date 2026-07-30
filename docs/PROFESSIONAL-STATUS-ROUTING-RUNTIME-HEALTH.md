# Professional Status Routing Runtime Health

Runtime Health est explicitement étendu avec :

- `ProfessionalStatusInboxStore` ;
- `ProfessionalStatusEventRouter`.

L'inspection vérifie uniquement binding, compatibilité et constructibilité. Elle n'appelle ni `store()`, ni `route()`, ni `consumptionFor()`. Le total attendu est de 40 capacités.
