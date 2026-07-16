# ADR-1004 — Authentification et gestion des secrets

## Statut de la décision

- **Projet :** APPART.SN REBUILD 2026
- **Sprint :** 7 — Document 4
- **Date de décision :** 16 juillet 2026
- **Statut :** proposé pour validation
- **Portée :** identités applicatives, authentification, autorisation, sessions, récupération, secrets et comptes techniques
- **Décisions héritées :** PHP 8.5.x, Laravel 13.x, PostgreSQL 18.x et structure physique ADR-1003
- **Hors périmètre :** package, fournisseur d’identité, produit de secrets, configuration, interface détaillée et réalisation

## Décision directrice

APPART.SN utilisera une identité applicative propriétaire du domaine **Identité et accès**, une authentification web par session au lancement, une autorisation par action et ressource, et des niveaux d’assurance adaptés au risque.

Les comptes internes et les actions professionnelles sensibles exigeront plusieurs facteurs. Une option résistante au phishing sera obligatoire pour les comptes internes et les rôles les plus puissants. Les secrets techniques seront fournis par une capacité dédiée, séparés par environnement, rotatifs et refusés dans le dépôt.

---

# 1. Objet

Cet ADR définit les décisions fondatrices relatives à :

- l’identité des acteurs ;
- l’authentification et les sessions ;
- l’autorisation ;
- la récupération d’accès ;
- la séparation des environnements ;
- le cycle de vie des secrets ;
- les comptes techniques et identités applicatives ;
- l’audit et la réaction aux incidents d’identité.

Il ne sélectionne aucun package, fournisseur ou format de configuration et ne crée aucun mécanisme exécutable.

---

# 2. Contexte

PERMISSIONS-MATRIX définit neuf acteurs officiels : Visiteur, Particulier, Professionnel, Modérateur, Commercial, Responsable SEO / Contenu, Finance, Super Administrateur et Système.

DOMAIN-MAPPING attribue à Identité et accès les Comptes, profils personnels, rôles, habilitations, consentements et Mandats. AGGREGATE-BOUNDARIES distingue Compte, Mandat de représentation et Professionnel. Administration et audit gouverne le principe des quatre yeux sans devenir propriétaire des comptes.

Les décisions techniques doivent donc empêcher :

- qu’un rôle remplace les invariants d’un domaine ;
- qu’un professionnel soit confondu avec le compte d’un représentant ;
- qu’un employé utilise une identité client pour agir en interne ;
- qu’une récupération soit plus faible que l’accès récupéré ;
- qu’un secret partagé rende les actions inattribuables ;
- qu’un Super Administrateur contourne le métier ;
- qu’un environnement donne accès aux secrets d’un autre.

---

# 3. Référentiels et principes

## 3.1 Référentiels

Les décisions s’appuient notamment sur :

- [NIST SP 800-63B-4 — Authentication and Authenticator Management](https://pages.nist.gov/800-63-4/sp800-63b.html) ;
- [NIST — Authenticator Requirements](https://pages.nist.gov/800-63-4/sp800-63b/authenticators/) ;
- [OWASP — Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html) ;
- [OWASP — Multifactor Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Multifactor_Authentication_Cheat_Sheet.html) ;
- [OWASP — Session Management Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html) ;
- [OWASP — Forgot Password Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html) ;
- [OWASP — Secrets Management Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Secrets_Management_Cheat_Sheet.html) ;
- [OWASP ASVS](https://owasp.org/www-project-application-security-verification-standard/).

## 3.2 Principes non négociables

1. Refus par défaut.
2. Moindre privilège.
3. Identité humaine nominative pour toute action humaine.
4. Aucun compte partagé.
5. Plusieurs facteurs pour les risques élevés.
6. Résistance au phishing pour les comptes internes puissants.
7. Autorisation contextuelle en plus du rôle.
8. Sessions courtes et révocables selon le risque.
9. Récupération aussi contrôlée que l’accès normal.
10. Secret absent du code, du dépôt, des journaux et des données de test.
11. Identité et secret distincts par environnement.
12. Traçabilité des actions sensibles sans journaliser les authentificateurs.
13. Laravel soutient le mécanisme ; Identité et accès reste propriétaire des règles.
14. Aucun package ne définit la politique.

---

# 4. Modèle officiel des identités

## 4.1 Compte humain

Un Compte représente une personne pouvant agir. Il possède une identité interne stable, un statut, des authentificateurs liés, des coordonnées vérifiées, des rôles bornés et un historique d’actions sensibles.

Le Compte n’est ni un Professionnel, ni une session, ni une simple adresse électronique, ni un numéro de téléphone.

## 4.2 Professionnel

Le Professionnel représente une organisation. Une personne agit pour elle au moyen d’un Mandat actif. La suspension du Professionnel et la suspension du Compte sont deux décisions différentes.

## 4.3 Identité interne

Tout membre de Modération, Commercial, SEO / Contenu, Finance ou Super Administration utilise un compte interne nominatif distinct de son éventuel compte public. Les rôles internes ne sont jamais ajoutés à un compte client ordinaire.

## 4.4 Système

Système n’est pas un utilisateur humain. Chaque charge de travail ou automatisation possède une identité technique dédiée, une finalité, une portée, un environnement et un propriétaire.

## 4.5 Identifiants de connexion

Une adresse électronique ou un numéro de téléphone vérifié peut servir d’identifiant de connexion selon le parcours approuvé. Ces coordonnées sont modifiables et ne deviennent jamais l’identité interne stable du Compte.

## 4.6 Unicité et rapprochement

Une même personne ne doit pas créer plusieurs comptes pour contourner suspension ou modération. La détection d’un doublon produit une revue ; elle ne fusionne jamais automatiquement deux identités.

---

# 5. Niveaux d’assurance par acteur

## 5.1 Niveaux internes à APPART.SN

| Niveau | Description | Usage |
|---|---|---|
| A0 | aucune authentification | Visiteur et contenus publics |
| A1 | un authentificateur vérifié | opérations particulières ordinaires à faible risque |
| A2 | deux facteurs distincts | actions sensibles de Particulier ou Professionnel et tous les comptes internes |
| A3 | authentification renforcée résistante au phishing et appareil maîtrisé selon le rôle | Finance sensible, Super Administrateur, récupération interne et actions critiques définies |

Ces niveaux sont une politique APPART.SN inspirée des principes NIST ; ils ne constituent pas une déclaration de conformité automatique à un niveau NIST complet.

## 5.2 Acteurs

| Acteur | Niveau minimal de session | Renforcement obligatoire |
|---|---|---|
| Visiteur | A0 | aucun |
| Particulier | A1 | A2 pour changement d’identité, sécurité, transfert, suppression sensible ou activité à risque |
| Professionnel | A2 pour gestion active du profil et des annonces | nouvelle liaison de représentant, changement d’identité légale, action sensible ou risque élevé |
| Modérateur | A2 | A3 pour actions exceptionnelles ou recours critiques définis |
| Commercial | A2 | renforcement pour remises, exports ou actions sensibles autorisées |
| Responsable SEO / Contenu | A2 | A3 pour redirections patrimoniales massives ou changements critiques |
| Finance | A2 | A3 pour remboursement, rapprochement exceptionnel, export ou seuil financier |
| Super Administrateur | A3 | à chaque action critique et accès d’urgence |
| Système | identité technique courte ou certificat équivalent | réauthentification automatique selon durée et portée |

## 5.3 Facteurs distincts

Deux mots de passe, deux codes de connaissance ou deux variantes du même facteur ne constituent pas plusieurs facteurs. La combinaison doit reposer sur des catégories distinctes.

---

# 6. Authentification primaire

## 6.1 Décision de lancement

Le lancement utilisera une authentification web par session pour les humains. Aucune authentification par jeton d’API n’est décidée par cet ADR.

## 6.2 Authentificateurs admis conceptuellement

- mot de passe ou phrase secrète ;
- clé cryptographique liée au domaine de vérification, notamment passkey/WebAuthn ;
- code à usage unique produit par une application d’authentification, comme solution de second facteur transitoire ou de repli ;
- codes de récupération à usage unique ;
- authentificateur matériel pour les rôles A3 lorsque requis.

## 6.3 Canaux restreints

SMS et courrier électronique ne sont pas considérés résistants au phishing. Ils peuvent servir à une notification ou à une récupération proportionnée pour un compte public, mais pas comme facteur principal suffisant pour un compte interne A3.

## 6.4 Fédération

La connexion par un fournisseur externe reste différée. Elle n’est admise que si l’identité locale, la révocation, la récupération, la portabilité et l’absence de verrou fournisseur sont démontrées par un futur ADR.

---

# 7. Politique des mots de passe

## 7.1 Règles de choix

- minimum de 15 caractères pour tout mot de passe humain APPART.SN ;
- maximum accepté d’au moins 64 caractères ;
- espaces et caractères Unicode acceptés selon une normalisation documentée ;
- aucune règle artificielle imposant majuscule, chiffre ou symbole ;
- aucun changement périodique obligatoire ;
- changement obligatoire en cas de preuve ou suspicion de compromission ;
- comparaison avec une liste de mots de passe courants, attendus et compromis ;
- gestionnaires de mots de passe, collage et génération autorisés ;
- aucun indice de mot de passe ;
- aucune question secrète ou connaissance biographique.

Ces décisions suivent les exigences actuelles de [NIST SP 800-63B-4](https://pages.nist.gov/800-63-4/sp800-63b/authenticators/).

## 7.2 Stockage

- mot de passe jamais conservé de façon réversible ;
- dérivation lente, salée et résistante aux attaques hors ligne ;
- algorithme et facteur de coût versionnés ;
- coût revu régulièrement et augmenté sans imposer une réinitialisation globale ;
- re-dérivation lors d’une authentification réussie lorsque la politique évolue ;
- secret supplémentaire éventuel séparé de la base et géré comme secret technique ;
- aucune valeur dans un journal, une erreur, un événement ou une sauvegarde non protégée.

L’algorithme précis et ses paramètres seront décidés avant J0 selon PHP 8.5, Laravel 13, les recommandations actuelles et la capacité de performance.

## 7.3 Protection en ligne

- limitation progressive des tentatives ;
- détection par compte, origine, appareil et comportement, sans permettre un déni de service simple ;
- réponses ne révélant pas l’existence d’un compte ;
- alertes sur attaques distribuées et credential stuffing ;
- mécanisme de récupération après blocage qui ne réduit pas l’assurance.

---

# 8. Politique MFA et résistance au phishing

## 8.1 Obligations

- MFA obligatoire pour Professionnels lors des actions actives définies, et pour tous les comptes internes ;
- authentificateur résistant au phishing obligatoire pour Super Administrateur et proposé puis imposé aux autres rôles internes selon la roadmap ;
- au moins une option résistante au phishing disponible pour tout compte A2 ;
- authentificateurs multiples pouvant être liés afin d’éviter un point de perte unique ;
- liaison, remplacement et retrait d’un facteur soumis à réauthentification et notification.

## 8.2 Choix privilégié

Les authentificateurs cryptographiques liés au nom du vérificateur, comme WebAuthn/passkeys, sont la cible privilégiée. NIST les reconnaît comme résistants au phishing grâce à la liaison au vérificateur.

Référence : [NIST — Phishing Resistance](https://pages.nist.gov/800-63-4/sp800-63b/authenticators/#phishing-resistance).

## 8.3 TOTP

Les codes temporaires issus d’une application d’authentification sont admis comme second facteur de transition ou de repli. Ils résistent au rejeu mais pas au phishing en temps réel ; ils ne satisfont donc pas seuls A3.

## 8.4 Push, SMS et email

- aucune validation push sans affichage clair du contexte et protection contre la fatigue ;
- SMS non admis comme facteur A3 ;
- email non admis comme second facteur indépendant lorsque l’accès au même email dépend déjà de la session ou du mot de passe compromis ;
- tout canal restreint possède une roadmap de remplacement.

---

# 9. Politique de sessions

## 9.1 Séparation

- sessions publiques, utilisateur et administration physiquement et logiquement distinctes ;
- aucun cookie de session client accepté sur l’interface administrative ;
- sessions internes séparées des comptes publics ;
- sessions distinctes par environnement et domaine de déploiement.

## 9.2 Cycle de vie

- identifiant renouvelé après authentification, élévation, récupération ou changement sensible ;
- expiration d’inactivité et durée absolue selon le niveau d’assurance ;
- réauthentification pour une action sensible, indépendamment d’une session encore active ;
- révocation à la déconnexion, suspension, changement d’authentificateur, compromission ou fin de Mandat selon le risque ;
- visibilité pour l’utilisateur de ses sessions actives et capacité de révocation globale ;
- aucune session permanente d’administration.

## 9.3 Durées initiales à valider

| Contexte | Inactivité maximale proposée | Durée absolue proposée | Réauthentification |
|---|---:|---:|---|
| Particulier ordinaire | 30 minutes pour actions sensibles, confort prolongé possible pour lecture | 30 jours avec renouvellement contrôlé | changement d’identité, sécurité et action critique |
| Professionnel | 30 minutes | 12 heures pour session active ; confiance d’appareil séparée et bornée | publication, Mandat, sécurité et opération sensible selon risque |
| Interne A2 | 15 minutes | 8 heures | action sensible et reprise après anomalie |
| A3 / accès d’urgence | 5 minutes | 1 heure | chaque action critique |

Ces valeurs sont des décisions proposées à tester avec les parcours ; leur réduction est autorisée par risque, leur augmentation exige une revue sécurité.

## 9.4 Protection

- secret de session imprévisible et sans sens métier ;
- transport uniquement sur canal protégé ;
- protection contre fixation, vol, rejeu et requêtes forgées ;
- aucun secret de session dans URL, journal ou stockage accessible au script client ;
- invalidation côté serveur possible ;
- corrélation d’audit distincte du secret de session.

---

# 10. Politique d’autorisation

## 10.1 Modèle

L’autorisation combine :

- rôle officiel ;
- action demandée ;
- ressource ciblée ;
- ownership ;
- Mandat actif ;
- état métier ;
- portée géographique ou organisationnelle lorsqu’elle existe ;
- conflit d’intérêts ;
- niveau d’assurance et fraîcheur de réauthentification ;
- approbation à quatre yeux lorsque requise.

Il s’agit d’un contrôle de rôle complété par des attributs et invariants contextuels. Un rôle seul n’autorise jamais une action spécialisée.

## 10.2 Emplacement de l’autorité

- Identité et accès établit l’acteur, ses rôles et Mandats ;
- le domaine propriétaire vérifie ses invariants locaux ;
- Administration et audit vérifie l’approbation à quatre yeux ;
- Laravel applique la décision mais ne la définit pas ;
- les interfaces peuvent masquer une action, mais cette absence visuelle n’est pas une protection.

## 10.3 Règles fortes

- Commercial ne publie jamais et ne contourne pas Modération ;
- Modérateur ne modifie jamais une offre commerciale ;
- SEO ne modifie jamais une Annonce ;
- Finance ne publie jamais ;
- Super Administrateur ne contourne pas les règles métier ;
- Système agit seulement dans une portée prédéfinie et auditable ;
- refus par défaut si une information requise manque ou est périmée.

## 10.4 Cache

Une décision d’autorisation critique n’est jamais fondée uniquement sur une valeur en cache. Rôle, Mandat, suspension, état et approbation sont revalidés selon leur criticité.

---

# 11. Séparation des environnements

## 11.1 Environnements minimaux

- développement local ;
- intégration ou validation automatisée ;
- recette contrôlée ;
- production ;
- environnement isolé de reprise ou sécurité lorsque nécessaire.

Le nombre définitif et les responsabilités seront précisés avant J0.

## 11.2 Isolation obligatoire

- identités applicatives distinctes ;
- secrets distincts ;
- clés de chiffrement distinctes ;
- bases et stockages distincts ;
- domaines et sessions distincts ;
- fournisseurs externes séparés ou comptes séparés ;
- permissions et journaux distincts ;
- aucune donnée de production copiée par défaut hors production.

## 11.3 Promotion

Le code peut progresser entre environnements après validation. Les secrets et données ne sont jamais promus avec lui. Une valeur de production n’est pas utilisée pour faciliter un test.

## 11.4 Développement

Les développeurs utilisent des identités et secrets synthétiques à portée locale. Ils n’accèdent pas aux secrets de production. Les données de test sont artificielles et ne contiennent ni compte, ni contact, ni média réel sans processus exceptionnel approuvé.

---

# 12. Architecture de gestion des secrets

## 12.1 Décision

Une capacité dédiée de gestion des secrets sera utilisée pour chaque environnement partagé. Elle centralisera stockage protégé, contrôle d’accès, audit, version, rotation et révocation.

Le produit recevra les secrets au moment nécessaire par une identité applicative autorisée. Il ne les conservera ni dans le dépôt ni dans un fichier `.env` versionné.

## 12.2 Catégories de secrets

- identifiants PostgreSQL ;
- clés de chiffrement applicatif ;
- clés de signature ;
- identifiants de stockage média ;
- secrets de paiement ;
- secrets de notification ;
- certificats et clés privées ;
- secrets de fournisseurs d’identité ;
- codes et identifiants d’accès d’urgence ;
- éventuel secret supplémentaire de dérivation des mots de passe.

## 12.3 Métadonnées obligatoires

Chaque secret possède :

- nom non sensible ;
- finalité ;
- propriétaire ;
- consommateurs autorisés ;
- environnement ;
- date de création ;
- date de dernière rotation ;
- prochaine échéance ;
- procédure de révocation ;
- dépendances connues ;
- classification et impact de compromission.

## 12.4 Interdictions

- secret en clair dans code, dépôt, documentation, commentaire, journal ou erreur ;
- secret réel dans une donnée de test ;
- secret partagé entre environnements ;
- secret partagé entre services sans nécessité ;
- secret humain utilisé par une charge de travail ;
- clé de chiffrement identique à une clé de session ou de signature ;
- export non audité depuis la capacité dédiée ;
- secret sans propriétaire ou expiration revue.

---

# 13. Politique de rotation des secrets

## 13.1 Principe

Privilégier des identités de charge de travail et secrets dynamiques de courte durée. Lorsqu’un secret statique est inévitable, sa durée est bornée et sa rotation automatisable.

## 13.2 Classes initiales

| Classe | Exemple conceptuel | Durée maximale cible | Décision de rotation |
|---|---|---:|---|
| Éphémère | jeton de charge de travail | minutes à 24 heures | renouvellement automatique |
| Critique statique | accès base, paiement ou fournisseur sensible | 90 jours | rotation planifiée et après incident |
| Certificat | identité de service | selon validité, rotation avant les deux tiers de vie | renouvellement automatisé |
| Clé de signature active | signature d’un artefact ou jeton interne futur | 180 jours maximum proposé | chevauchement vérifiable des versions |
| Clé de chiffrement durable | protection de données | revue annuelle, rotation selon risque | versionnement et re-chiffrement maîtrisé |
| Accès d’urgence | compte break-glass | après chaque usage et contrôle périodique | cérémonie à plusieurs personnes |

Les durées finales seront alignées sur le fournisseur choisi, les obligations et la capacité d’automatisation. Une durée plus longue exige une acceptation de risque explicite.

## 13.3 Déclencheurs immédiats

- suspicion ou preuve de fuite ;
- départ ou changement de rôle d’une personne ayant eu accès ;
- perte d’un appareil ou authentificateur ;
- dépendance compromise ;
- exposition dans un journal, dépôt ou canal non autorisé ;
- changement de fournisseur ou d’environnement ;
- usage d’urgence ;
- impossibilité d’attribuer l’utilisation.

## 13.4 Rotation sans interruption

Lorsque nécessaire, une période de chevauchement permet ancien et nouveau secrets, avec durée minimale, observabilité et révocation finale obligatoire. La rotation est testée avant production et ne doit pas désactiver les invariants métier.

## 13.5 Clés de chiffrement

Les données chiffrées portent une référence de version de clé. La rotation d’une clé ne rend pas les données illisibles et ne suppose pas un déchiffrement global instantané. Clé active, anciennes clés de lecture et clés retirées ont des statuts distincts.

---

# 14. Récupération d’accès

## 14.1 Principe

La récupération est une liaison de nouvel authentificateur après perte de contrôle. Elle est plus lente et plus contrôlée qu’une authentification ordinaire.

## 14.2 Méthodes admissibles

- authentificateur secondaire déjà lié ;
- codes de récupération à usage unique ;
- contact de récupération préalablement vérifié lorsqu’il est autorisé ;
- nouvelle vérification d’identité proportionnée ;
- intervention humaine renforcée pour les comptes internes, avec quatre yeux et délai de sécurité.

## 14.3 Règles

- réponse uniforme ne révélant pas l’existence du compte ;
- jeton ou code aléatoire, à usage unique et de courte durée ;
- limitation des demandes et détection d’abus ;
- aucun changement de compte avant preuve suffisante ;
- notification sur tous les canaux vérifiés indépendants ;
- révocation des sessions et authentificateurs compromis selon le scénario ;
- délai de sécurité pour une modification à fort impact ;
- historique d’audit sans secret ;
- aucune question personnelle ou information publique comme preuve.

NIST exige qu’une récupération déclenche une notification et reconnaît notamment codes de récupération, contacts et nouvelle preuve d’identité. Référence : [NIST — Account Recovery](https://pages.nist.gov/800-63-4/sp800-63b.html#account-recovery).

## 14.4 Comptes internes

La récupération d’un compte interne ne repose jamais uniquement sur email ou SMS. Elle exige une vérification par l’organisation, une autre preuve forte, une seconde approbation et une notification sécurité. Le Super Administrateur ne récupère pas seul son propre accès.

## 14.5 Codes de récupération

- générés avec une entropie suffisante ;
- présentés une seule fois ;
- conservés par l’utilisateur hors ligne de préférence ;
- stockés sous une forme résistante à la divulgation ;
- chacun utilisable une seule fois ;
- remplacés ensemble après usage ou régénération ;
- jamais transmis dans les journaux ou au support.

---

# 15. Comptes techniques

## 15.1 Identité par charge de travail

Chaque application, tâche planifiée, processus de Queue, outil de reprise ou intégration possède une identité distincte. Une même identité n’est pas partagée par plusieurs environnements ou finalités incompatibles.

## 15.2 Propriétés obligatoires

- propriétaire humain ou équipe ;
- finalité documentée ;
- environnement ;
- permissions minimales ;
- durée et rotation ;
- source d’émission ;
- journalisation ;
- procédure de suspension ;
- date de revue et de fin de vie.

## 15.3 Interdictions

- connexion interactive humaine avec un compte technique ;
- mot de passe permanent lorsqu’une identité courte est possible ;
- rôle Super Administrateur pour un processus ;
- accès direct à plusieurs modules sans port approuvé ;
- compte technique sans propriétaire ;
- compte Legacy maintenu après clôture ;
- secret technique transmis à un développeur par commodité.

## 15.4 Migration Legacy

Les identités techniques de Migration Legacy sont temporaires, isolées, limitées aux sources et cibles approuvées et révoquées avec la clôture du module. Le produit courant ne réutilise aucune de ces identités.

---

# 16. Identités applicatives et communication interne

## 16.1 Monolithe initial

Le monolithe modulaire s’exécute initialement sous un nombre minimal d’identités techniques. Cette simplicité opérationnelle ne donne pas à chaque module le droit de lire les données internes d’un autre.

Les frontières sont protégées par les contrats, l’organisation physique, les autorisations et les contrôles architecturaux, même si certains mécanismes partagent un processus.

## 16.2 Capacités externes

PostgreSQL, stockage média, notification, paiement, recherche, observabilité et sauvegarde possèdent des identités distinctes selon leur finalité et leur niveau d’accès.

## 16.3 Communication future

Si un module est extrait un jour, il reçoit sa propre identité de charge de travail et une authentification mutuelle adaptée. Aucun secret global du monolithe n’est copié dans le nouveau service.

## 16.4 Identité du déploiement

Le mécanisme de déploiement possède une identité séparée de l’identité d’exécution. Il peut fournir ou référencer des secrets sans pouvoir les lire au-delà de sa mission lorsque la capacité choisie le permet.

---

# 17. Cycle de vie des comptes et authentificateurs

## 17.1 Compte

États conceptuels minimaux : invité sans compte, en attente de vérification, actif, temporairement verrouillé, suspendu, fermé. Les états définitifs devront rester alignés avec Identité et accès et ne pas être confondus avec les états d’Annonce ou Professionnel.

## 17.2 Authentificateur

Un authentificateur est proposé, vérifié, lié, actif, éventuellement suspendu, remplacé, révoqué ou expiré. Le retrait du dernier facteur fort exige récupération ou fermeture contrôlée.

## 17.3 Départ et changement de rôle

- rôle retiré immédiatement à la date d’effet ;
- sessions internes révoquées ;
- authentificateurs organisationnels retirés ;
- secrets et accès techniques revus ;
- Mandats réévalués ;
- actions récentes sensibles examinées selon le risque ;
- données d’audit conservées selon la politique.

## 17.4 Suspension

La suspension bloque les nouvelles authentifications et révoque les sessions selon le risque. Elle ne supprime ni l’historique, ni les preuves, ni les obligations financières. Ses effets sur Professionnels et Annonces passent par leurs domaines propriétaires.

---

# 18. Actions sensibles et step-up

## 18.1 Déclencheurs

Une réauthentification renforcée est exigée notamment pour :

- changement d’email, téléphone, mot de passe ou facteur ;
- récupération et régénération de codes ;
- ajout ou révocation d’un Mandat ;
- changement d’identité légale d’un Professionnel ;
- attribution de rôle interne ;
- publication, suspension, restauration ou action exceptionnelle selon le rôle ;
- décision de modération critique ou recours ;
- export de données personnelles ;
- changement d’URL patrimoniale massif ;
- remboursement ou rapprochement exceptionnel ;
- modification d’un secret ou accès technique ;
- usage Super Administrateur ou accès d’urgence.

## 18.2 Contexte

Le step-up vérifie la fraîcheur de l’authentification, le facteur requis, l’appareil, le risque de session et l’approbation éventuelle. Une session ancienne ou inhabituelle ne suffit pas même si elle n’est pas expirée.

## 18.3 Quatre yeux

L’authentification renforcée de l’auteur ne remplace jamais l’approbation par un second acteur. Les deux personnes possèdent des sessions nominatives et des facteurs indépendants.

---

# 19. Comptes d’urgence

## 19.1 Finalité

Un nombre minimal de comptes d’urgence permet la récupération opérationnelle lorsque les mécanismes normaux d’identité sont indisponibles.

## 19.2 Règles

- jamais utilisé pour l’administration quotidienne ;
- authentificateur résistant au phishing et matériel lorsque possible ;
- secret ou facteur conservé séparément selon une procédure à plusieurs personnes ;
- usage exigeant motif, durée, approbation ou revue immédiate selon l’urgence ;
- alerte en temps réel à la sécurité ;
- session très courte ;
- actions limitées à la restauration du service normal ;
- rotation après chaque usage ;
- test périodique sans révéler le secret ;
- aucune exemption aux invariants métier.

## 19.3 Super Administrateur

Le rôle Super Administrateur ordinaire n’est pas le compte d’urgence. Il reste nominatif, soumis à A3, au step-up et à l’audit. L’urgence est une procédure exceptionnelle distincte.

---

# 20. Journalisation et détection

## 20.1 Événements obligatoires

- succès et échecs d’authentification selon une granularité respectueuse de la vie privée ;
- inscription et vérification ;
- liaison, remplacement et révocation d’un facteur ;
- création et usage d’un code de récupération ;
- demande et réussite de récupération ;
- création, renouvellement et révocation de session ;
- changement de rôle ou Mandat ;
- refus d’autorisation sensible ;
- usage d’un compte technique ;
- accès, rotation, révocation ou échec d’un secret ;
- usage d’urgence ;
- anomalie de volume, origine, appareil ou comportement.

## 20.2 Contenu

Identité concernée, acteur, type d’action, date, résultat, environnement, corrélation, niveau d’assurance et motif normalisé lorsque nécessaire.

## 20.3 Contenu interdit

Mot de passe, facteur secret, code temporaire, code de récupération, clé privée, jeton de session, secret technique complet, réponse de récupération ou donnée personnelle non nécessaire.

## 20.4 Alertes prioritaires

- attaque distribuée ;
- récupération suivie d’une action sensible ;
- nouveau facteur puis retrait des anciens ;
- attribution de rôle puissant ;
- usage simultané géographiquement incohérent ;
- accès d’urgence ;
- lecture massive de secrets ;
- secret utilisé depuis une identité ou un environnement inattendu ;
- échecs répétés sur comptes internes.

---

# 21. Réponse aux incidents d’identité et secrets

## 21.1 Identité compromise

1. suspendre l’authentificateur ou le Compte selon le risque ;
2. révoquer les sessions ;
3. protéger les ressources et Mandats liés ;
4. notifier par un canal indépendant ;
5. rechercher les actions réalisées ;
6. rétablir l’identité avec un processus renforcé ;
7. lier de nouveaux facteurs ;
8. documenter les décisions et impacts métier.

## 21.2 Secret technique compromis

1. révoquer ou isoler ;
2. activer un secret sain ;
3. identifier consommateurs et données exposées ;
4. rechercher les usages anormaux ;
5. remplacer les secrets dérivés ou voisins si nécessaire ;
6. vérifier paiements, médias, notifications et données ;
7. notifier les responsables ;
8. corriger la cause et tester la rotation.

## 21.3 Priorité

La sécurité et l’intégrité priment sur la continuité d’une fonctionnalité non critique. Une annonce peut être temporairement non exposée ; un paiement ou une permission ne doit jamais être accepté sur une preuve compromise.

---

# 22. Décisions retenues

1. Identité et accès reste propriétaire des Comptes, rôles, authentificateurs et Mandats.
2. Professionnel reste une organisation distincte du Compte représentant.
3. Les comptes internes sont nominatifs et séparés des comptes publics.
4. L’authentification humaine initiale est web et fondée sur des sessions.
5. Le mot de passe minimal compte 15 caractères, sans composition artificielle ni rotation périodique.
6. MFA est obligatoire pour les professionnels actifs sensibles et tous les comptes internes.
7. Une option résistante au phishing est obligatoire pour A3 et disponible pour A2.
8. Passkeys/WebAuthn constituent la cible privilégiée sans sélection de package.
9. SMS, email et TOTP ne sont pas considérés résistants au phishing.
10. L’autorisation combine rôle, action, ressource, ownership, Mandat, état, conflit et niveau d’assurance.
11. Les interfaces et Laravel n’établissent pas seuls l’autorisation métier.
12. Les environnements possèdent identités, secrets, clés, sessions et données distincts.
13. Une capacité dédiée gère les secrets ; aucun secret n’est conservé dans le dépôt.
14. Les identités courtes et secrets dynamiques sont préférés.
15. La rotation est bornée par classe et immédiate après compromission.
16. La récupération est une nouvelle liaison d’authentificateur avec notification et audit.
17. Chaque charge de travail possède une identité technique dédiée.
18. Les comptes d’urgence sont distincts, rares, surveillés et sans contournement métier.

---

# 23. Conséquences et risques

## 23.1 Conséquences positives

- séparation claire entre personne, organisation, rôle et charge de travail ;
- réduction du risque de phishing pour les rôles puissants ;
- récupération moins exploitable par ingénierie sociale ;
- attribution fiable des actions sensibles ;
- rotation des secrets sans dépendance au dépôt ;
- environnements réellement isolés ;
- politique indépendante d’un package ou fournisseur ;
- maintien du Domaine hors de Laravel.

## 23.2 Coûts

- expérience MFA et récupération à concevoir soigneusement ;
- support utilisateur et professionnel ;
- capacité de secrets à sélectionner et exploiter ;
- gestion du cycle des facteurs et appareils ;
- procédures internes plus exigeantes ;
- observabilité et alertes à construire ;
- tests de révocation, rotation et reprise obligatoires.

## 23.3 Risques majeurs

| Risque | Impact | Réduction |
|---|---|---|
| MFA trop complexe pour les utilisateurs | abandon ou contournement | parcours progressif, facteurs multiples et support |
| SMS conservé trop longtemps | phishing et prise de contrôle | usage restreint et roadmap de retrait |
| récupération plus faible que l’accès | compromission totale | facteurs multiples, délais, notification et revue |
| comptes internes mêlés aux comptes publics | escalade et attribution ambiguë | identités séparées |
| Super Administrateur permanent et surpuissant | contournement du métier | A3, step-up, quatre yeux et audit |
| secrets statiques nombreux | fuite et rotation difficile | identités courtes et automatisation |
| rotation cassant le service | indisponibilité | chevauchement borné et tests |
| journaux contenant des secrets | compromission secondaire | filtrage, tests et accès limités |
| autorisation uniquement dans l’interface | accès direct indu | vérification dans le cas d’usage et le domaine |
| cache d’autorisation périmé | action après révocation | revalidation des décisions critiques |
| fournisseur d’identité imposant son modèle | perte d’ownership | contrat local et ADR de fédération |
| package dictant la politique | couplage au framework | politique indépendante et admission séparée |

---

# 24. Critères d’acceptation

L’ADR est accepté si :

- les neuf acteurs officiels ont un niveau d’assurance défini ;
- Compte, Professionnel, Mandat et Système sont distingués ;
- comptes internes et publics sont séparés ;
- authentification et autorisation sont explicitement différentes ;
- le mot de passe suit les principes NIST actuels ;
- MFA et résistance au phishing sont proportionnés aux rôles ;
- aucune méthode restreinte n’est présentée comme résistante au phishing ;
- les sessions ont séparation, renouvellement, expiration, step-up et révocation ;
- l’autorisation protège toutes les interdictions de PERMISSIONS-MATRIX ;
- le Super Administrateur ne contourne aucune règle ;
- les environnements n’échangent ni secrets, ni sessions, ni données par défaut ;
- une capacité dédiée de secrets est imposée sans fournisseur choisi ;
- chaque secret possède propriétaire, finalité, rotation et révocation ;
- la récupération exige preuve, notification et audit ;
- les comptes techniques sont non humains, dédiés et bornés ;
- Migration Legacy utilise des identités temporaires supprimables ;
- l’accès d’urgence est distinct et à plusieurs personnes ;
- aucun secret n’apparaît dans les journaux ;
- les incidents disposent de procédures de révocation et investigation ;
- le Domaine reste indépendant de Laravel ;
- aucun package n’est choisi ;
- aucune règle métier validée n’est modifiée ;
- le document reste conceptuel.

---

# 25. Questions ouvertes

## Bloquantes avant J0

1. Quelle capacité dédiée de gestion des secrets sera retenue ?
2. Quel algorithme de dérivation de mots de passe et quels paramètres seront approuvés pour PHP 8.5 ?
3. Quel mécanisme de session Laravel 13 satisfait séparation, révocation et observabilité sans entrer dans le Domaine ?
4. Quels domaines de déploiement séparent public, utilisateur et administration ?
5. Quels environnements exacts seront créés dès J0 ?
6. Quelle autorité possède la rotation et l’accès d’urgence aux secrets ?
7. Quels délais définitifs d’inactivité et absolus sont validés par type de compte ?
8. Quel outil contrôle l’absence de secret dans le dépôt et les journaux ?

## Avant Identité et accès

9. Email, téléphone ou les deux peuvent-ils initier une connexion pour un Particulier ?
10. Quel niveau de vérification d’identité est exigé pour un Professionnel ?
11. À quelles actions professionnelles exactes A2 devient-il obligatoire ?
12. Quel authentificateur résistant au phishing sera offert en premier ?
13. TOTP reste-t-il un repli permanent ou transitoire pour les comptes internes ?
14. SMS peut-il intervenir dans la récupération d’un Particulier au Sénégal, et avec quelles preuves complémentaires ?
15. Combien d’authentificateurs et de codes de récupération un Compte peut-il conserver ?
16. Quelle période de sécurité suit un changement d’email, téléphone ou facteur ?
17. Quels signaux de risque déclenchent un step-up automatique ?

## Avant exploitation

18. Quel fournisseur ou quelle capacité WebAuthn/passkeys est compatible sans verrouillage ?
19. Quels rôles internes exigent A3 dès le premier jour ?
20. Quels seuils financiers imposent A3 et quatre yeux ?
21. Quelle conservation s’applique aux événements d’authentification et d’autorisation ?
22. Quel processus humain traite une récupération interne ?
23. Combien de comptes d’urgence sont autorisés et où sont conservés leurs facteurs ?
24. Quelles classes de secrets peuvent utiliser une durée différente des cibles proposées ?
25. Comment versionner et faire tourner les clés de chiffrement de données existantes ?
26. Quelle procédure de fermeture accompagne le départ d’un employé ou prestataire ?
27. Quels tests externes d’authentification et de récupération sont exigés avant ouverture ?

---

# 26. Références aux décisions validées

## ADR-1000

Le présent ADR ferme les principes d’authentification, autorisation, secrets, séparation et rotation laissés ouverts par ADR-1000. Il conserve refus par défaut, moindre privilège, sécurité dès l’origine et indépendance du Domaine.

## ADR-1001

Les mécanismes futurs devront être compatibles avec PHP 8.5.x et Laravel 13.x. Cet ADR ne choisit aucune capacité Laravel ni dépendance PHP.

## ADR-1002

PostgreSQL 18.x conservera les données d’identité et d’audit selon les futures structures, mais les secrets techniques resteront dans la capacité dédiée. Les mots de passe seront dérivés, jamais chiffrés de façon réversible.

## ADR-1003

Le cœur Identité et accès sera sous `src/Modules/IdentityAccess`. Les interfaces et adaptateurs d’authentification resteront sous la périphérie `app`. Aucun objet Laravel n’entrera dans le Domaine.

---

# Synthèse des décisions retenues

APPART.SN adopte une identité locale stable, des comptes internes séparés, une authentification web par session, des mots de passe longs sans règles artificielles, MFA pour les professionnels sensibles et tous les rôles internes, et une authentification résistante au phishing pour A3.

L’autorisation combine rôle, action, ressource, ownership, Mandat, état, conflit d’intérêts, niveau d’assurance et quatre yeux. Laravel applique les décisions sans les posséder.

Les secrets seront gérés hors du dépôt par une capacité dédiée, séparés par environnement, attribués à des identités techniques spécifiques, rotatifs et révocables. La récupération lie de nouveaux authentificateurs après preuve suffisante, notification et audit.

# Décisions restant ouvertes

Restent à décider : produit de gestion des secrets, algorithme et paramètres de dérivation, mécanisme de session, domaines de déploiement, facteurs proposés en premier, place exacte du SMS, durées définitives, signaux de risque, conservation des audits, procédures internes et gouvernance des comptes d’urgence.

# Confirmation de périmètre

Ce livrable est exclusivement conceptuel et documentaire. Aucun code, projet Laravel, commande Composer, package, fichier `.env`, configuration ou secret n’a été créé.
