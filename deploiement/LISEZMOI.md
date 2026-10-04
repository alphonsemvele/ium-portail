# Mise en ligne de l'IUM

Le site part sur **ium.lamajestueuse.com** à chaque poussée sur `main`. GitHub Actions
installe les dépendances, compile les assets, envoie les fichiers par rsync,
puis joue les migrations et reconstruit les caches sur le serveur.

Composer ne tourne jamais sur le serveur : `vendor/` arrive tout construit.

## Ce qu'il faut régler une seule fois

### 1. Les secrets du dépôt

`Settings → Secrets and variables → Actions → New repository secret`, dans
[ium-portail](https://github.com/alphonsemvele/ium-portail/settings/secrets/actions) :

| Secret | Obligatoire | Ce que c'est |
|---|---|---|
| `SSH_HOST` | oui | l'hôte SSH de PlanetHoster |
| `SSH_USER` | oui | l'utilisateur SSH du compte |
| `SSH_KEY` | oui | la **clé privée** de déploiement, en entier |
| `APP_PATH` | oui | où vit l'application, **hors** de toute racine web — par exemple `/home/<utilisateur>/apps/ium` |
| `RACINE_WEB` | recommandé | la racine du sous-domaine, `/home/<utilisateur>/ium` : elle devient un lien vers `public/` |
| `SSH_PORT` | non | si différent de 22 |
| `PHP_BIN` | non | chemin complet du PHP du serveur, si `php` n'est pas le bon |

Ce sont les mêmes valeurs que pour le portail, sauf `APP_PATH` et
`RACINE_WEB`, propres à ce site.

**Pourquoi l'application ne doit pas vivre dans la racine du sous-domaine :**
le panneau fait pointer ium.lamajestueuse.com sur `~/ium`. Si le code était
déposé là, `https://ium.lamajestueuse.com/.env` serait servi au premier venu. On dépose donc
l'application ailleurs, et `~/ium` devient un lien vers son dossier
`public/`, le seul qui doit être visible.

### 2. La clé de déploiement

Si vous réutilisez celle du portail, rien à faire côté serveur : la même clé
publique est déjà dans `~/.ssh/authorized_keys`. Sinon :

```bash
ssh-keygen -t ed25519 -C "deploiement-ium" -f ~/.ssh/deploiement-ium
cat ~/.ssh/deploiement-ium.pub   # à ajouter dans authorized_keys du serveur
cat ~/.ssh/deploiement-ium       # à coller dans le secret SSH_KEY
```

### 3. La base et le fichier .env

Créez la base depuis le panneau PlanetHoster, puis, sur le serveur :

```bash
mkdir -p ~/apps/ium
cp ~/apps/ium/deploiement/env-production.exemple ~/apps/ium/.env
# compléter DB_DATABASE, DB_USERNAME, DB_PASSWORD, puis :
php ~/apps/ium/artisan key:generate
```

Le `.env` reste sur le serveur : le pipeline ne l'envoie ni ne l'écrase.

### 4. Le certificat

La colonne SSL du panneau est à ✗ pour ce sous-domaine : émettez le
certificat avec le bouton **SSL/TLS**. Tant qu'il manque, le site répond en
clair — le pipeline l'accepte le temps de l'émission, mais `APP_URL` doit
alors rester en `http://`.

## Au quotidien

- **Déployer** : pousser sur `main`. L'onglet *Actions* montre le détail.
- **Rejouer un déploiement** : *Actions → Déploiement → Run workflow*.
- **Essai à blanc sur le serveur**, sans migration ni coupure :

```bash
bash ~/apps/ium/deploiement/apres-deploiement.sh --essai
```

## Les tests

La suite de tests de ce projet ne passe pas encore (il n'y a pas encore de tests). Le déploiement
n'est donc pas conditionné à elle — il vérifie seulement que l'application
démarre. Dès que la suite sera verte, il faudra la remettre en garde-fou
avant l'envoi, comme sur le portail du groupe.
