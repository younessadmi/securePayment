<?php
session_start();
require('config.php');

class ExpressCheckout{
    private cancelUrl = null;
    private returnUrl = null;
    private static amount = 0;
    private currencyCode = 'EUR';
    private description = '';
    private localeCode = 'FR';
    private logo = null;

    private listCurrencyPossible = [];
    private listCountriesPossible = [];

    public function __construct(){
        //set list of the possible countries
        $string = file_get_contents('./json/countries.json');
        $this->listCountriesPossible = json_decode($string, true);
        //set list of the possible currencies
        $string = file_get_contents('./json/currencies.json');
        $this->listCurrencyPossible = json_decode($string, true);
    }

    public function setExpressCheckout(){
        $requete = $this->getOptionBase();

        if($this->cancelUrl() == null){
            die('Cancel URL has not been set');
        }else $requete .= '&CANCELURL='.urlencode($this->cancelUrl);
        if($this->returnUrl() == null){
            die('Return URL has not been set');
        }else $requete .= '&RETURNURL='.urlencode($this->returnUrl);
        if($this->amout <= 0){
            die('The amount cannot be null or less than zero.');
        }else $requete .= '&AMT='.$this->amount;

        $requete .= '&CURRENCYCODE='.$this->currencyCode;
        $requete .= '&DESC='.urlencode($this->description);
        $requete .= '&LOCALECODE='.$this->localeCode;

        if($this->logo != null){
            $requete .= '&HDRIMG='.urlencode($this->logo);
        }

        $ch = curl_init($requete);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resultat_paypal = curl_exec($ch);
        if($resultat_paypal){

            $liste_param_paypal = transformUrlParametersToArray($resultat_paypal);
            // Si la requête a été traitée avec succès
            if ($liste_param_paypal['ACK'] == 'Success')
            {
                // Redirige le visiteur sur le site de PayPal
                header("Location: https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=".$liste_param_paypal['TOKEN']);
                exit();
            }else{ // En cas d'échec, affiche la première erreur trouvée.
                die("<p>Erreur de communication avec le serveur PayPal.<br />".$liste_param_paypal['L_SHORTMESSAGE0']."<br />".$liste_param_paypal['L_LONGMESSAGE0']."</p>");
            }
        }else die('<p>Erreur:</p><p>'.curl_error($ch).'</p>');
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
