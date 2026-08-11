# HTTPS Certification Note

## Verdict

**NO GO PROPOSÉ**

La correction de transport est conforme : HTTPS fonctionne, le certificat est accepté, `APP_URL` est sécurisé, `/up` et le shell répondent, les assets ne produisent aucun Mixed Content et la console est propre.

Le GO global reste toutefois impossible car la mission exige également une session IAM réelle, l'acceptation effective de `__Host-appart_session` et sa persistance après reload. Aucun principal local avec credentials autorisés n'est disponible, et en créer un est hors du périmètre de cette qualification d'environnement.

## Condition minimale de levée

Une décision distincte doit fournir un compte local IAM autorisé déjà créé par les capacités certifiées, ou autoriser explicitement sa création via un chemin applicatif IAM certifié. La vérification navigateur pourra alors être rejouée sans aucun changement de transport ni affaiblissement du cookie.

**NO GO PROPOSÉ — APPART.TEST LOCAL HTTPS TRANSPORT QUALIFICATION 01**
