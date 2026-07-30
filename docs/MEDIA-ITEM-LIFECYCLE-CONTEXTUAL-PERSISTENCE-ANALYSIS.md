# Media Item Lifecycle Contextual Persistence Analysis

Le Sprint **4.6C-R2** matérialise le contexte V1 certifié sans modifier le journal 031.

## Chaîne d'écriture

1. ouverture ou réutilisation de la transaction PostgreSQL ;
2. verrou advisory déterministe par `mediaId` ;
3. lecture verrouillée du dernier état Lifecycle ;
4. contrôle de version, d'état et de rejeu ;
5. insertion de la transition dans le journal 031 ;
6. insertion du contexte complet dans la table 032 ;
7. commit uniquement lorsque les deux insertions réussissent.

Toute défaillance non contractuelle annule les deux écritures. Une transaction externe reste propriétaire de son commit ou rollback.

Le repository ne lit jamais `MediaCollection` et ne construit aucune décision de remplacement. Il matérialise exclusivement le contexte reçu.
