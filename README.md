
![](https://raw.githubusercontent.com/rayenzaafouri/blob-storage-repo/main/fitverse/fitverse-logo.png "Fitverse logo")


# FitVerse
# An all in one fitness hub implementation based on Symfony 6.4 and MySQL

FitVerse est une plateforme connectée qui optimise la performance et le bien-être des sportifs grâce à un suivi personnalisé, des recommandations intelligentes et une communauté interactive.

## 📄 Description du Projet

FitVerse a pour objectif de centraliser les services de gestion sportive dans un même environnement digital. La plateforme permet aux utilisateurs de suivre leur progression, participer à des événements, suivre des conseils nutritionnels, planifier des exercices, et bien plus encore.

### Fonctionnalités principales :
- Gestion de shop
- Gestion d'événements
- Gestion de salle de sport (gym)
- Suivi nutritionnel
- Programmes d'entraînement (workout)
- Gestion des utilisateurs

## 🚀 Technologies Utilisées
- Symfony (PHP Framework)
- PHP > 8
- Twig
- JavaScript
- CSS


## Requirements

### PHP (Version>8)
1. Install xampp from the following link : 
[Download XAMPP](https://www.apachefriends.org/fr/download.html)

2. Add ( C:\xampp\php ) to your environement variables


Verify the current version

 ```bash
php -v
 ```


It should return a version >8 ✅

 ```bash
PHP 8.2.12 (cli) (built: Oct 24 2023 21:15:15) (ZTS Visual C++ 2019 x64)
Copyright (c) The PHP Group
Zend Engine v4.2.12, Copyright (c) Zend Technologies
 ```



### Symfony CLI

Open windows powershell as <b>ADMINISTRATOR</b> and run the following commands

STEP 1
 ```powershell
Set-ExecutionPolicy RemoteSigned -Scope CurrentUser
 ```


STEP 2
 ```powershell
irm get.scoop.sh | iex 
 ```


STEP 3
 ```bash
scoop install symfony-cli
 ```

VERIFICATION

STEP 3
 ```bash
symfony check:requirements
 ```




## Getting started 
### Twig template for new pages
 ```twig





{% extends 'base.html.twig' %}

{% block title %}Your title goes here{% endblock %}

{% block javascripts %}
{{ parent() }} 
{# Your javascript goes here #}
<script>
    
</script>

{% endblock %}


{% block stylesheets %}
{{ parent() }} 
{# Your CSS goes here #}
<style>

</style>
{% endblock %}



{% block body %}
{% include 'navbar.html.twig' %} 

<body data-context="gym">
{# data-context value should be one of the values "exercise","gym","events","shop","nutrition" or "user" depending on your module  #}
{# you HTML goes here  #}


<h2>Yeah it works you can procceed.</h2>
</body>

{% endblock %}
 ```
 Note : Le champ `data-context` peut être "exercise", "gym", "events", "shop", "nutrition" ou "user" selon le module.

## ✨ Remerciements

Merci à toute l'équipe FitVerse pour leur contribution exceptionnelle :
- **Hamza Boutar** : Gestion shop
- **Khalil Kammessi** : Gestion nutrition
- **Rayen Zaafouri** : Gestion workout
- **Ali Tlili** : Gestion gym
- **Ines Jelassi** : Gestion user
- **Chaima Miled** : Gestion event

---

✅ Ce projet est en constante évolution. N'hésitez pas à explorer, tester et améliorer !
