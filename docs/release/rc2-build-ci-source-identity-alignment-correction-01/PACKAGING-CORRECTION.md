# Packaging Correction

Le script attend désormais le commit prédécesseur RC2 et le tag successor exact.

Un preflight fermé `APPART_ALIGNMENT_CHECK_ONLY=1` a été ajouté pour le stade pré-matérialisation. Il exige :

- `HEAD` égal au prédécesseur connu ;
- absence du tag futur réservé.

Il termine avant toute archive. Le contrôle final historique — tag annoté, `tag → HEAD`, ascendance, propreté et checksums — reste intact. Les corrections Composer et le manifeste ne sont pas modifiés.
