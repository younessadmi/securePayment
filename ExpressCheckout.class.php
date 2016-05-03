<?php
session_start();
require('config.php');

class ExpressCheckout{
    private $CancelUrl = null;
    private $ReturnUrl = null;
    private $Amount = 0;
    private $CurrencyCode = 'EUR';
    private $Description = '';
    private $LocaleCode = 'FR';
    private $Logo = null;

    private $listCurrencyPossible = [];
    private $listCountriesPossible = [];

    public function __construct(){        
        //set list of the possible countries
        $string = file_get_contents(SITE_PATH.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR.'countries.json');
        $this->listCountriesPossible = json_decode($string, true);
        //set list of the possible currencies
        $string = file_get_contents(SITE_PATH.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR.'currencies.json');
        $this->listCurrencyPossible = json_decode($string, true);
    }

    public function __call($method, $params) {
        $var = substr($method, 3);
        if(strncasecmp($method, "set", 3) || strncasecmp($method, "get", 3)){
            //SETTERS
            if(strncasecmp($method, "set", 3) == 0 && isset($params[0])) {
                $this->$var = $params[0];
                return $this;
            }
            //GETTERS
            if(strncasecmp($method, "get", 3) == 0) {
                return $this->$var;
            }
        }
    }

    public function setExpressCheckout(){
        $requete = $this->getOptionBase();

        //faire des vérifications de sécurité sur tous les champs
        //ok
        $requete .= '&METHOD=SetExpressCheckout';
        //check if url
        $requete .= '&CANCELURL='.urlencode($this->CancelUrl);
        //check if url
        $requete .= '&RETURNURL='.urlencode($this->ReturnUrl);
        //check not null and positive
        $requete .= '&AMT='.$this->Amount;
        //check if in the list
        $requete .= '&CURRENCYCODE='.$this->CurrencyCode;
        //ok
        $requete .= '&DESC='.urlencode($this->Description);
        //check if in the list
        $requete .= '&LOCALECODE='.$this->LocaleCode;
        //if null, remove it and if not, check if is image
        $requete .= '&HDRIMG='.urlencode($this->Logo);

        $ch = curl_init($requete);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resultat_paypal = curl_exec($ch);
        if($resultat_paypal){
            $liste_param_paypal = $this->transformUrlParametersToArray($resultat_paypal);
            // Si la requête a été traitée avec succès
            if ($liste_param_paypal['ACK'] == 'Success')
            {
                // Redirige le visiteur sur le site de PayPal
                header("Location: ".PAYPAL_SERVER."webscr&cmd=_express-checkout&token=".$liste_param_paypal['TOKEN']);
                exit();
            }else{ // En cas d'échec, affiche la première erreur trouvée.
                die("<p>Erreur de communication avec le serveur PayPal.<br />".$liste_param_paypal['L_SHORTMESSAGE0']."<br />".$liste_param_paypal['L_LONGMESSAGE0']."</p>");
            }
        }else die('<p>Erreur:</p><p>'.curl_error($ch).'</p>');
        // On ferme notre session cURL.
        curl_close($ch);
    }

    public function doExpressCheckout(){
        $requete = $this->getOptionBase();

        // On ajoute le reste des options
        $requete .= '&METHOD=DoExpressCheckoutPayment';
        $requete .= '&TOKEN='.htmlentities($_GET['token'], ENT_QUOTES);
        $requete .= '&AMT='.$this->Amount;
        $requete .= '&CURRENCYCODE='.$this->CurrencyCode;
        $requete .= '&PayerID='.htmlentities($_GET['PayerID'], ENT_QUOTES);
        $requete .= '&PAYMENTACTION=sale';

        $ch = curl_init($requete);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resultat_paypal = curl_exec($ch);

        if($resultat_paypal){ // S'il y a une erreur, on affiche "Erreur", suivi du détail de l'erreur.
            $liste_param_paypal = $this->transformUrlParametersToArray($resultat_paypal);
            return $liste_param_paypal;
        }else echo "<p>Erreur</p><p>".curl_error($ch)."</p>";
        // On ferme notre session cURL.
        curl_close($ch);
    }

    public function getExpressCheckout(){
        $requete = $this->getOptionBase();
        $requete .= '&METHOD=GetExpressCheckoutDetails';
        $requete .= '&TOKEN='.htmlentities($_GET['token'], ENT_QUOTES); // Ajoute le jeton

        $ch = curl_init($requete);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resultat_paypal = curl_exec($ch);

        if($resultat_paypal){            
            $liste_param_paypal = $this->transformUrlParametersToArray($resultat_paypal);
            echo "<pre>";
            print_r($liste_param_paypal);
            echo "</pre>";
            // Mise à jour de la base de données & traitements divers... Exemple :
        }else echo "<p>Erreur</p><p>".curl_error($ch)."</p>";
        curl_close($ch);
    }

    private function transformUrlParametersToArray($resultat_paypal){
        $liste_parametres = explode("&",$resultat_paypal); // Crée un tableau de paramètres
        foreach($liste_parametres as $param_paypal) // Pour chaque paramètre
        {
            list($nom, $valeur) = explode("=", $param_paypal); // Sépare le nom et la valeur
            $liste_param_paypal[$nom]=urldecode($valeur); // Crée l'array final
        }
        return $liste_param_paypal; // Retourne l'array
    }

    private function getOptionBase(){
        return BASE_URL_API_PAYPAL.'VERSION='.VERSION_API_PAYPAL.'&USER='.USERNAME_FACILITATOR.'&PWD='.PASSWORD_FACILITATOR.'&SIGNATURE='.SIGNATURE_FACILITATOR;
    }
}
