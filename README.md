# Secure Payment

Ce projet de 4ème année en Ingénierie du Web à [l'ESGI](http://esgi.fr/) a pour objectif de développer une librarie PHP facilement intégrable dans un projet. Cette librarie utilise l'API NVP de Paypal.
## Prerequisities

Pour utiliser, vous devrez avoir l'extension `curl` activée.
## Getting Started

Pour utiliser cette librarie, vous devez tout d'abord:
1. Configurer le fichier `config.php` à la racine du projet, en y indiquant `USERNAME_FACILITATOR`, `PASSWORD_FACILITATOR` et `SIGNATURE_FACILITATOR` que vous trouverez sur votre compte Paypal (cf l'API de Paypal)
2. Inclure la classe `ExpressCheckout` : 
```
require('/path/to/ExpressCheckout.class.php')
```
3. C'est tout !


## Methods
### 1) Créer une instance
```
$obj = new ExpressCheckout();
```
### 2) Setter/Getter
Après avoir créer une instance de la classe ExpressCheckout, il faut d'abord indiquer quelques informations avant d'envoyer la requête à Paypal. Voici la liste des informations que vous pouvez envoyer:
```
/* On indique l'url sur laquelle le client sera redirigé après paiement */
$obj->setReturnUrl('https://mysite.com/return.php');
/* On indique l'url sur laquelle le client sera redirigé s'il a annulé la transaction */
$obj->setCancelUrl('https://mysite.com/cancel.php');
/* On indique le montant de la transaction */
$obj->setAmount(121);
/* On indique la devise */
$obj->setCurrencyCode('EUR');
/* On indique la langue d'affichage sur Paypal */
$obj->setLocaleCode('FR');
/* On indique si on utilise la librarie dans le cadre de la sandbox de Paypal ou non */
$obj->isSandbox(true);
/* Facultatif: on indique notre logo qui sera affiché sur Paypal */
$obj->setLogo('https://mysite.com/img/logo.png');
/* Facultatif: on indique une description */
$obj->setDescription("Ceci est une description que le client pourra retrouver lors du paiement sur Paypal.");
```
### 3) Set Express Checkout
Une fois toutes les informations indiquées dans notre objet, on appelle la méthode `setExpressCheckout`:
```
$obj->setExpressCheckout();
```
Lors de l'appel de cette méthode, le serveur web de votre site envoit une requête HTTP via cURL, avec les informations précédemment indiquées, à Paypal. Cela permettra la création d'un TOKEN qui sera associé à la transaction ainsi qu'un statut de la requête (et bien d'autres informations, voir l'API NVP de Paypal).

Si la requête s'est bien déroulé, le client sera redirigé vers Paypal afin d'effectuer le paiement. Deux cas possible:
1. Le client annule. Il sera redirigé vers la page indiquée lors de l'appel de la méthode `$obj->setCancelUrl();`
2. Le client confirme son paiement. Il sera redirigé vers la page indiquée lors de l'appel de la méthode `$obj->setReturnUrl()`.

Si la requête ne s'est pas bien déroulée, une erreur de type `Exception` sera envoyée.

### 4) Do Express Checkout
Afin de valider le paiement, on appelle la méthode `$obj->doExpressCheckout()`. L'appel de cette méthode doit être à la page `ReturnUrl`. 

Mais avant cela, il faut de nouveau créer un objet `ExpressCheckout` et indiquer le montant (`setAmount()`), le cadre de d'utilisation (`isSandbox()`), la devise (`setCurrencyCode`), la langue (`setLocaleCode()`) et éventuellement la description et le logo. 

La méthode `$obj->doExpressCheckout()` renvoit un tableau avec les informations concernant la transaction (voir l'API NVP Paypal). 

### 5) Get Express Checkout Details
Mais si vous souhaitez plus de détail, vous pouvez utiliser la methode `$obj->getExpressCheckout($token)` en indiquant en paramètre le TOKEN de la transaction.



## Authors

* [**Younes SADMI**](https://github.com/younessadmi)
* [**Benjamin HUBERT**](https://github.com/BenjaminHubert)
* [**Bertrand FREYLIN**](https://github.com/BertrandF26/)
* [**Axel DELANNAY**](https://github.com/axeldelannay/)
* [**Thibault LENORMAND**](https://github.com/ThibaultLenormand)