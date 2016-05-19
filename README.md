# Secure Payment

Ce projet de 4ème année en Ingénierie du Web à [l'ESGI](http://esgi.fr/) a pour objectif de développer une librarie PHP facilement intégrable dans un projet. Cette librarie utilise l'API NVP de PayPal.
## Prerequisities

Pour utiliser, vous devrez avoir l'extension `curl` activée.
## Getting Started

Pour utiliser cette librarie, vous devez inclure:

1. Inclure la classe `ExpressCheckout` 
```
require('/path/to/ExpressCheckout.class.php');
```
## Methods
### 1) Créer une instance
```
$obj = new ExpressCheckout();
```
### 2) Setter/Getter
Après avoir créer une instance de la classe ExpressCheckout, il faut d'abord indiquer quelques informations avant d'envoyer la requête à PayPal. Voici la liste des informations que vous pouvez envoyer:
```
// On indique l'url sur laquelle le client sera redirigé après paiement
$obj->setReturnUrl('https://mysite.com/return.php');

// On indique l'url sur laquelle le client sera redirigé s'il a annulé la transaction
$obj->setCancelUrl('https://mysite.com/cancel.php');

// On indique le montant de la transaction
$obj->setAmount(121);

// On indique la devise
$obj->setCurrencyCode('EUR');

// On indique la langue d'affichage sur PayPal
$obj->setLocaleCode('FR');

// On indique si le nom du user, du compte indiqué sur PayPal, qui recevra l'argent
$obj->setUsername_facilitator('exemple@mail.com');

// On indique si le mot de passe du compte indiqué sur PayPal
$obj->setPassword_facilitator('0ba3ee5c105af0fb2e3db9b50d1eb7e0');

// On indique si la signature du compte indiqué sur PayPal
$obj->setSignature_facilitator('eecda80d40891ec572ff00f31a92fa60');

// On indique si on utilise la librarie dans le cadre de la sandbox de PayPal ou non
$obj->isSandbox(true);

//FACULTATIF
//on indique notre logo qui sera affiché sur PayPal
$obj->setLogo('https://mysite.com/img/logo.png');

//on indique une description
$obj->setDescription("Ceci est une description que le client pourra retrouver lors du paiement sur PayPal.");
```
### 3) Get Token
Une fois toutes les informations indiquées dans notre objet, on appelle la méthode `getToken()` qui retourne le token de PayPal:
```
$token = $obj->getToken();
```
Si la requête ne s'est pas bien déroulée, une erreur de type `Exception` sera envoyée.

Lors de l'appel de cette méthode, le serveur web de votre site envoit une requête HTTP via cURL, avec les informations précédemment indiquées, à PayPal. Cela permettra la création d'un TOKEN qui sera associé à la transaction ainsi qu'un statut de la requête (et bien d'autres informations, voir l'API NVP de PayPal).

### 4) Set Express Checkout
Si le token a été récupéré avec succès, il suffit de renvoyer le client effectuer la transaction en appelant la méthode `setExpressCheckout()`:
```
$obj->setExpressCheckout();
```

Le client sera redirigé vers PayPal afin d'effectuer le paiement. Deux cas possible:
1. Le client annule. Il sera redirigé vers la page indiquée lors de l'appel de la méthode `$obj->setCancelUrl();`
2. Le client confirme son paiement. Il sera redirigé vers la page indiquée lors de l'appel de la méthode `$obj->setReturnUrl()`.

Si la requête ne s'est pas bien déroulée, une erreur de type `Exception` sera envoyée.

### 5) Do Express Checkout
Afin de valider le paiement, on appelle la méthode `$obj->doExpressCheckout()`. L'appel de cette méthode doit être à la page `ReturnUrl`. 

Mais avant cela, il faut de nouveau créer un objet `ExpressCheckout` et indiquer le montant (`setAmount()`), le cadre de d'utilisation (`isSandbox()`), la devise (`setCurrencyCode()`), la langue (`setLocaleCode()`) et éventuellement la description et le logo. 

La méthode `$obj->doExpressCheckout()` renvoit un tableau avec les informations concernant la transaction (voir l'API NVP PayPal). 

### 6) Get Express Checkout Details
Mais si vous souhaitez plus de détail, vous pouvez utiliser la methode `$obj->getExpressCheckout($token)` en indiquant en paramètre le TOKEN de la transaction.



## Authors

* [**Younes SADMI**](https://github.com/younessadmi)
* [**Benjamin HUBERT**](https://github.com/BenjaminHubert)
* [**Bertrand FREYLIN**](https://github.com/BertrandF26/)
* [**Axel DELANNAY**](https://github.com/axeldelannay/)
* [**Thibault LENORMAND**](https://github.com/ThibaultLenormand)
