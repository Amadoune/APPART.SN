# HTTP Foundation — Experience & Acceptance

Statut : GO CERTIFIÉ — OUVERTE.

La façade publique expose sept routes GET, chacune dépendant exclusivement de son Reader V1. Les contrôleurs ne connaissent ni Owner Source, ni Runtime, ni Persistence, ni Infrastructure. Les réponses sont limitées à status et observedAt, avec Cache-Control: no-store et X-Content-Type-Options: nosniff.

Composants : sept Controllers, sept Form Requests strictes, ExperienceAcceptanceResponseFactory, ExperienceAcceptanceHttpRuntimeV1 et ExperienceAcceptanceHttpServiceProvider singleton/lazy.

