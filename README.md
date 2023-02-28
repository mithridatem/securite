# securite
## Exercice pour mes cours symfony
Projet inscription et connexion sécurisée,
Activation des comptes par email.

### Installation du projet :
1 Cloner le repository avec la commande dans un terminal :
```git
git clone https://github.com/mithridatem/securite.git
```
2 Se déplacer dans le répertoire
```bash
cd securite
```
3 Créer un fichier .env à la racine du projet
```bash
touch .env
```
4 Ajouter les lignes suivantes dans le fichier .env :

APP_ENV=dev
APP_SECRET=fc2cd4284c88950bec8e886fb69223ce
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
DATABASE_URL="mysql://root:@127.0.0.1:3306/securite?serverVersion=mariadb-10.4.24&charset=utf8mb4"
#### Paramétrer avec vos identifiants SMTP
LOGIN=''

MDP=''

5 Installer le projet avec la commande suivante dans un terminal :
```bash
composer install
```
6 Créer la base de données et la migrer avec les commandes suivantes dans un terminal :
```bash
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```
