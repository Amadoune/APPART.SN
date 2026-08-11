# F3 — Implementation Evidence

## Preuves acquises

| Exigence | Preuve | Statut |
|---|---|---|
| principal IAM réel | AccountId durable créé par `RegisterAccount` | PASS |
| hash autoritatif | produit par `CredentialHashAuthorityV1` | PASS |
| aucun SQL | provisioning via Application et Registry | PASS |
| HTTPS Home | `https://appart.test/`, titre APPART.SN | PASS |
| Login navigateur | aucune surface Web exécutable | BLOCKED |
| cookie Secure accepté | Login non atteignable | BLOCKED |
| Reload Workspace | session navigateur absente | BLOCKED |
| Logout et reload refusé | Login préalable absent | BLOCKED |

## Audit de la surface

- le bouton « Se connecter » est un `button` sans navigation ou soumission IAM ;
- aucune vue publique ne contient de formulaire Login ;
- aucune vue publique ne fournit le token CSRF nécessaire ;
- le Controller IAM attend un `Idempotency-Key`, impossible à produire par un formulaire HTML seul ;
- `/authoring/workspace` contient le token CSRF mais exige déjà une session IAM valide.

## Contournements rejetés

- injection manuelle du cookie ;
- création directe d'une Session ;
- désactivation CSRF ;
- ajout d'une exemption CSRF ;
- script ou formulaire local ad hoc ;
- transfert d'un cookie obtenu par curl vers le navigateur ;
- modification F2, Runtime, Controller, Cookie ou HTTPS.

La capture `F3-HTTPS-SESSION-PROOF.png` documente la Home HTTPS et ses actions encore informatives. Elle ne revendique pas les quatre états Session demandés.
