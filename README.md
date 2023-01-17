# Symfony Docker (PHP8 / Caddy / Postgresql)

A [Docker](https://www.docker.com/)-based installer and runtime for the [Symfony](https://symfony.com) web framework, with full [HTTP/2](https://symfony.com/doc/current/weblink.html), HTTP/3 and HTTPS support.

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up` (the logs will be displayed in the current shell) or Run `docker compose up -d` to run in background
4. Check variable in .env : `DATABASE_URL="postgresql://symfony:ChangeMe@database:5432/app?serverVersion=13&charset=utf8"`
5. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
6. Run `docker compose down --remove-orphans` to stop the Docker containers.
7. Run `docker compose logs -f` to display current logs, `docker compose logs -f [CONTAINER_NAME]` to display specific container's current logs

## Commandes utiles
Lister l'ensemble des commandes existances `docker compose exec php bin/console`

#### Création de fichier vierge
Controller `docker compose exec php bin/console make:controller`

FormType `docker compose exec php bin/console make:form`

CRUD `docker compose exec php bin/console make:crud`

#### Debug
Supprimer le cache du navigateur 

`docker compose exec php bin/console cache:clear`

`docker compose exec php bin/console c:c`

Voir les routes actives 

`docker compose exec php bin/console debug:router`

## Gestion des routes
[https://symfony.com/doc/current/routing.html](https://symfony.com/doc/current/routing.html)

## Autowiring & ParamConverter
Autowiring [https://symfony.com/doc/current/service_container/autowiring.html](https://symfony.com/doc/current/service_container/autowiring.html)

ParamConverter [https://symfony.com/bundles/SensioFrameworkExtraBundle/current/annotations/converters.html](https://symfony.com/bundles/SensioFrameworkExtraBundle/current/annotations/converters.html)

## Gestion de base de données

#### Commandes de création des fichiers entity/repository et d'ajout de champs
`docker compose exec php bin/console make:entity`

Documentation sur les relations entre les entités [https://symfony.com/doc/current/doctrine/associations.html](https://symfony.com/doc/current/doctrine/associations.html)

#### Mise à jour de la base de données via migration
Generation d'une migration 

`docker compose exec php bin/console make:migration`

Jouer les migrations 

`docker compose exec php bin/console doctrine:migration:migrate`

`docker compose exec php bin/console d:m:m`

#### Mise à jour de la base de données via update de schema sans migration
Voir les requètes interprétées (sans mise à jour de la DB)

`docker compose exec php bin/console doctrine:schema:update --dump-sql`

`docker compose exec php bin/console d:s:u --dump-sql`

Executer les requètes en DB

`docker compose exec php bin/console doctrine:schema:update --force`

`docker compose exec php bin/console d:s:u --force`

#### Data Fixtures
[https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html](https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html)

`docker compose exec php bin/console doctrine:fixtures:load`

`docker compose exec php bin/console d:f:l -n`

#### Autres commandes utiles liés à doctrine

Suppression de la base de donnée 

`docker compose exec php bin/console doctrine:database:drop --force`

`docker compose exec php bin/console d:d:d --force`

Création de la base de donnée

`docker compose exec php bin/console doctrine:database:create`

`docker compose exec php bin/console d:d:c`

#### Bundle DoctrineExtension
Installation

`docker compose exec php composer require stof/doctrine-extensions-bundle`

Activation des features

```
# config/packages/stof_doctrine_extensions.yaml

stof_doctrine_extensions:
    default_locale: en_US
    orm:
        default:
            sluggable: true
            timestampable: true
            sortable: true
            # other features...
```

Documentation du bundle

[https://symfony.com/bundles/StofDoctrineExtensionsBundle/current/index.html](https://symfony.com/bundles/StofDoctrineExtensionsBundle/current/index.html)

Github du bundle

[https://github.com/stof/StofDoctrineExtensionsBundle](https://github.com/stof/StofDoctrineExtensionsBundle)

## Authentification & Sécurité

#### Création du système d'authentification

Création de l'entity user `docker compose exec php bin/console make:user`

Création du système d'authentification `docker compose exec php bin/console make:auth`

Création d'un hash de PWD `docker compose exec php bin/console security:hash-password`

#### Gestion des accès par rôle utilisateur

Gestion de la hiérarchie des rôles [https://symfony.com/doc/current/security.html#access-control-authorization](https://symfony.com/doc/current/security.html#access-control-authorization)

Contrôle des accès par rôles (access_control) [https://symfony.com/doc/current/security.html#add-code-to-deny-access](https://symfony.com/doc/current/security.html#add-code-to-deny-access)

#### Sécurité :  Check des permissions utilisateurs pour une resource donnée (Voter)

[https://symfony.com/doc/current/security/voters.html](https://symfony.com/doc/current/security/voters.html)

#### Sécurité : expression via attributes
[https://symfony.com/doc/current/security/expressions.html](https://symfony.com/doc/current/security/expressions.html)

[https://symfony.com/bundles/SensioFrameworkExtraBundle/current/annotations/security.html#security](https://symfony.com/bundles/SensioFrameworkExtraBundle/current/annotations/security.html#security)

#### Sécurité : globale documentation

Documentation complète liée à la sécurité [https://symfony.com/doc/current/security.html](https://symfony.com/doc/current/security.html)

## Gestion des formulaires

Documentation [https://symfony.com/doc/current/forms.html](https://symfony.com/doc/current/forms.html)
Types [https://symfony.com/doc/current/reference/forms/types.html](https://symfony.com/doc/current/reference/forms/types.html)

#### Contrainte de validation (Sécurité des formulaires : Assert)
[https://symfony.com/doc/current/validation.html](https://symfony.com/doc/current/validation.html)

Exemple d'utilisation
```
    #[ORM\Column(length: 50)]
    #[NotBlank(message: 'Cette valeur ne peut pas être vide.')]
    #[Type('string', message: 'Type de donnée invalide.')]
    #[Length(
        min: 10,
        max: 50,
        minMessage: 'Il faut minimum 10 caractères.',
        maxMessage: 'Il faut maximum 50 caractères.'
    )]
    private ?string $name = null;
```

#### Sécurité : globale documentation

Documentation complète liée à la sécurité [https://symfony.com/doc/current/security.html](https://symfony.com/doc/current/security.html)
