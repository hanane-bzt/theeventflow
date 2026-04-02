# Registre des activités de traitement — EventFlow
**Conformément à l'article 30 du Règlement Général sur la Protection des Données (RGPD)**

---

## Responsable du traitement

**Dénomination :** EventFlow
**Forme :** Projet académique — M2 Développement Web
**Adresse :** 1 rue de l'Université, 75005 Paris

## Délégué à la Protection des Données (DPO)

**Nom :** Thomas Lefèvre
**Fonction :** Délégué à la Protection des Données (DPO fictif)
**Contact :** dpo@eventflow.fr

---

## Vue d'ensemble des traitements

| N° | Traitement | Base légale | Durée de conservation |
|----|-----------|-------------|----------------------|
| 1 | Gestion des comptes utilisateurs | Consentement | 2 ans après dernière connexion |
| 2 | Authentification et gestion des sessions | Intérêt légitime | Durée de la session (1h) |
| 3 | Gestion des événements | Exécution d'un contrat | 1 an après la date de l'événement |
| 4 | Gestion des inscriptions aux événements | Exécution d'un contrat | 1 an après la date de l'événement |
| 5 | Demandes de passage au rôle Organisateur | Consentement | 1 an après traitement |
| 6 | Gestion du consentement (cookies et préférences) | Obligation légale | 5 ans |
| 7 | Journalisation des accès aux données personnelles | Obligation légale | 3 ans |
| 8 | Administration et modération des comptes | Intérêt légitime | Durée de vie de la plateforme |

---

## Traitement n°1 — Gestion des comptes utilisateurs

| Champ | Détail |
|-------|--------|
| **Finalité** | Créer et gérer les comptes utilisateurs pour accéder à la plateforme |
| **Base légale** | Consentement explicite de la personne concernée (art. 6.1.a RGPD) |
| **Données concernées** | Nom, prénom, adresse e-mail, mot de passe (haché bcrypt), numéro de téléphone (optionnel), rôle, date d'inscription, version et date du consentement |
| **Personnes concernées** | Toute personne s'inscrivant sur la plateforme |
| **Destinataires** | Équipe technique interne, administrateurs de la plateforme |
| **Durée de conservation** | 2 ans à compter de la dernière connexion, puis anonymisation automatique |
| **Transfert hors UE** | Aucun |
| **Mesures de sécurité** | Mots de passe hachés (bcrypt, algorithme `auto` Symfony), validation des données via Symfony Validator, consentement obligatoire à l'inscription |

---

## Traitement n°2 — Authentification et gestion des sessions

| Champ | Détail |
|-------|--------|
| **Finalité** | Vérifier l'identité des utilisateurs et maintenir leur session de manière sécurisée |
| **Base légale** | Intérêt légitime du responsable du traitement (art. 6.1.f RGPD) — sécurité du service |
| **Données concernées** | Adresse e-mail (identifiant), token JWT (contient : identifiant, rôles, date d'expiration), adresse IP de connexion |
| **Personnes concernées** | Utilisateurs tentant de se connecter à la plateforme |
| **Destinataires** | Système d'authentification interne (LexikJWTBundle) |
| **Durée de conservation** | Token JWT valide 1 heure (TTL configurable via `JWT_TOKEN_TTL`). Aucun stockage serveur (stateless) |
| **Transfert hors UE** | Aucun |
| **Mesures de sécurité** | Tokens JWT signés avec clés RSA (paire public/privé), transport HTTPS obligatoire en production, tokens non persistés côté serveur |

---

## Traitement n°3 — Gestion des événements

| Champ | Détail |
|-------|--------|
| **Finalité** | Permettre aux organisateurs de créer, publier et gérer des événements accessibles aux utilisateurs |
| **Base légale** | Exécution d'un contrat (art. 6.1.b RGPD) |
| **Données concernées** | Titre, description, date et heure, lieu, capacité maximale, statut de publication, identifiant de l'organisateur, date de création |
| **Personnes concernées** | Organisateurs d'événements |
| **Destinataires** | Utilisateurs de la plateforme (données publiques pour les événements publiés), administrateurs |
| **Durée de conservation** | 1 an après la date de l'événement, puis suppression ou archivage |
| **Transfert hors UE** | Aucun |
| **Mesures de sécurité** | Contrôle d'accès par Voter Symfony `EventVoter` (seul le propriétaire peut modifier ou supprimer son événement), validation des données entrantes |

---

## Traitement n°4 — Gestion des inscriptions aux événements

| Champ | Détail |
|-------|--------|
| **Finalité** | Permettre aux utilisateurs de s'inscrire à des événements et aux organisateurs de gérer les listes de participants |
| **Base légale** | Exécution d'un contrat (art. 6.1.b RGPD) |
| **Données concernées** | Identifiant utilisateur, identifiant événement, date d'inscription, statut de l'inscription (confirmée / annulée) |
| **Personnes concernées** | Participants inscrits à des événements |
| **Destinataires** | Organisateur de l'événement concerné, administrateurs de la plateforme |
| **Durée de conservation** | 1 an après la date de l'événement |
| **Transfert hors UE** | Aucun |
| **Mesures de sécurité** | Accès restreint au propriétaire de l'inscription (annulation) et à l'organisateur (liste participants), vérification de capacité maximale |

---

## Traitement n°5 — Demandes de passage au rôle Organisateur

| Champ | Détail |
|-------|--------|
| **Finalité** | Gérer les demandes d'élévation de privilèges des utilisateurs souhaitant devenir organisateur d'événements |
| **Base légale** | Consentement explicite (art. 6.1.a RGPD) — l'utilisateur initie volontairement la démarche |
| **Données concernées** | Identifiant utilisateur, statut de la demande (en attente / approuvée / refusée), date de soumission, date de traitement, commentaire de l'administrateur |
| **Personnes concernées** | Utilisateurs ayant soumis une demande de rôle organisateur |
| **Destinataires** | Administrateurs de la plateforme |
| **Durée de conservation** | 1 an après traitement de la demande |
| **Transfert hors UE** | Aucun |
| **Mesures de sécurité** | Accès restreint aux administrateurs (ROLE_ADMIN), vérification qu'une seule demande pending existe par utilisateur |

---

## Traitement n°6 — Gestion du consentement (cookies et préférences)

| Champ | Détail |
|-------|--------|
| **Finalité** | Recueillir, enregistrer et prouver le consentement des utilisateurs pour les cookies et l'utilisation de leurs données personnelles |
| **Base légale** | Obligation légale (art. 6.1.c RGPD) — directive ePrivacy et RGPD art. 7 |
| **Données concernées** | Identifiant utilisateur, type de consentement (nécessaires / analytiques / marketing), version du consentement, date et heure du consentement, adresse IP hashée |
| **Personnes concernées** | Tous les visiteurs et utilisateurs de la plateforme |
| **Destinataires** | Équipe technique interne, autorité de contrôle (CNIL) sur demande |
| **Durée de conservation** | 5 ans (obligation de preuve du consentement) |
| **Transfert hors UE** | Aucun |
| **Mesures de sécurité** | Enregistrement automatique horodaté de chaque action de consentement, bannière de cookies avec granularité par catégorie, possibilité de retrait à tout moment |

---

## Traitement n°7 — Journalisation des accès aux données personnelles

| Champ | Détail |
|-------|--------|
| **Finalité** | Assurer la traçabilité des accès, modifications et suppressions de données personnelles à des fins de conformité RGPD (art. 30) et de détection d'incidents |
| **Base légale** | Obligation légale (art. 6.1.c RGPD) |
| **Données concernées** | Type d'action (`data_accessed`, `data_deleted`, `consent_given`, `consent_withdrawn`), horodatage, adresse IP pseudonymisée (hash SHA-256), description de l'action, identifiant de l'utilisateur concerné |
| **Personnes concernées** | Tous les utilisateurs authentifiés effectuant des opérations sur leurs données |
| **Destinataires** | Équipe technique interne, DPO, autorité de contrôle (CNIL) sur demande |
| **Durée de conservation** | 3 ans |
| **Transfert hors UE** | Aucun |
| **Mesures de sécurité** | Adresses IP non stockées en clair (hash SHA-256 irréversible), journalisation automatique via `RgpdSubscriber` (EventDispatcher Symfony), accès aux logs restreint aux administrateurs |

---

## Traitement n°8 — Administration et modération des comptes

| Champ | Détail |
|-------|--------|
| **Finalité** | Permettre aux administrateurs de surveiller l'activité de la plateforme, modérer les contenus et anonymiser les comptes en cas de violation des conditions d'utilisation ou de demande RGPD |
| **Base légale** | Intérêt légitime du responsable du traitement (art. 6.1.f RGPD) — sécurité et intégrité de la plateforme |
| **Données concernées** | Données complètes des utilisateurs (nom, prénom, e-mail, rôle, nombre d'inscriptions, nombre d'événements créés), données des événements, historique des demandes de rôle |
| **Personnes concernées** | Tous les utilisateurs inscrits sur la plateforme |
| **Destinataires** | Administrateurs de la plateforme uniquement |
| **Durée de conservation** | Accès actif tant que le compte existe. Les logs d'anonymisation sont conservés 3 ans |
| **Transfert hors UE** | Aucun |
| **Mesures de sécurité** | Accès exclusivement réservé aux comptes avec le rôle `ADMIN`, contrôle par firewall Symfony (`access_control: ROLE_ADMIN`), anonymisation RGPD complète (données remplacées par des valeurs neutres, flag `isAnonymized` activé, log de suppression créé) |

---

## Droits des personnes concernées

Les utilisateurs peuvent exercer les droits suivants directement depuis la plateforme :

| Droit | Base légale | Point d'accès |
|-------|------------|--------------|
| **Droit d'accès** (art. 15) | RGPD art. 15 | `GET /api/me` — consultation du profil complet |
| **Droit de rectification** (art. 16) | RGPD art. 16 | `PUT /api/me` — modification du prénom, nom, téléphone |
| **Droit à l'effacement** (art. 17) | RGPD art. 17 | `DELETE /api/me` — pseudonymisation du compte |
| **Retrait du consentement** (art. 7.3) | RGPD art. 7 | `PUT /api/me/consent` avec `{"granted": false}` |

> **Note sur le droit à l'effacement :** les données sont pseudonymisées (nom → "Utilisateur supprimé", e-mail → hash SHA-256) et non supprimées physiquement, afin de préserver l'intégrité référentielle des logs RGPD obligatoires.

Pour toute autre demande (portabilité, opposition, limitation de traitement), contacter le DPO : **dpo@eventflow.fr**

---

## Synthèse des mesures de sécurité globales

| Mesure | Implémentation technique |
|--------|--------------------------|
| Authentification | JSON Web Token (JWT) signé RSA, durée de vie 1h |
| Hachage des mots de passe | bcrypt via Symfony PasswordHasher |
| Pseudonymisation des IP | Hash SHA-256 irréversible avant tout stockage |
| Contrôle d'accès | Voters Symfony + guards Vue Router + `access_control` dans `security.yaml` |
| Anonymisation des comptes | Pseudonymisation des champs personnels + flag `isAnonymized` + log de traçabilité |
| Journalisation automatique | `RgpdSubscriber` via EventDispatcher — log à chaque accès/modification de données personnelles |
| Validation des entrées | Symfony Validator (contraintes Assert sur les entités) |
| Cloisonnement des rôles | USER / ORGANIZER / ADMIN avec escalade par demande explicite |

---

*Document établi dans le cadre du projet académique EventFlow — M2 Développement Web — Avril 2025.*
*DPO désigné : Thomas Lefèvre — dpo@eventflow.fr*
