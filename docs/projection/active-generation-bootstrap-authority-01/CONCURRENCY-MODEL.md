# Concurrency Model

Deux bootstraps avec la même identité convergent. Avec deux identités, un verrou opérationnel/advisory global de bootstrap doit sérialiser le préflight et Create; la seconde commande retourne `BootstrapInProgress` ou `ActiveAlreadyExists`. Deux activations restent sérialisées par les verrous du Manager et l'index Active. Crash après Create: reprendre la même identité et le même scope; ne jamais en créer une nouvelle.
