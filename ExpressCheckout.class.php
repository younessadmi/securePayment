<?php
DEFINE('SITE_PATH', realpath(dirname(__FILE__)));
class ExpressCheckout{

    const VERSION_API_PAYPAL = 124;
    
    const API_NVP_PROD = 'https://api-3t.paypal.com/nvp?';
    const SERVER_NVP_PROD = 'https://www.paypal.com/';

    const API_NVP_DEV = 'https://api-3t.sandbox.paypal.com/nvp?';
    const SERVER_NVP_DEV = 'https://www.sandbox.paypal.com/';

    private $CancelUrl = '';
    private $ReturnUrl = '';
    private $Amount = 0;
    private $CurrencyCode = '';
    private $Description = '';
    private $LocaleCode = '';
    private $Logo = '';
    private $paypal_token = '';

    private $Username_facilitator = '';
    private $Password_facilitator = '';
    private $Signature_facilitator = '';

    private $base_url_api_paypal = '';
    private $paypal_server = '';

    private $listCurrenciesPossible = [];
    private $listCountriesPossible = [];

    public function __construct(){
        //set list of the possible countries
        if(file_exists(SITE_PATH.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR.'countries.json')){
            $string = file_get_contents(SITE_PATH.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR.'countries.json');
        }else throw new Exception('The countries.json file is not exist'); 
        $this->listCountriesPossible = json_decode($string, true);

        //set list of the possible currencies
        if(file_exists(SITE_PATH.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR.'countries.json')){
            $string = file_get_contents(SITE_PATH.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR.'currencies.json');
        }else throw new Exception('The currencies.json file is not exist'); 
        $this->listCurrenciesPossible = json_decode($string, true);
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

    public function isSandbox($bool){
        if($bool === true){
            $this->base_url_api_paypal = self::API_NVP_DEV;
            $this->paypal_server = self::SERVER_NVP_DEV;
        }elseif($bool === false){
            $this->base_url_api_paypal = self::API_NVP_PROD;
            $this->paypal_server = self::SERVER_NVP_PROD;
        }else{
            throw new Exception('The method \'isSandbox\' expected a boolean.');   
        }
    }

    public function getToken(){
        $requete = $this->getOptionBase();

        $requete .= '&METHOD=SetExpressCheckout';
        if(filter_var($this->CancelUrl, FILTER_VALIDATE_URL)){
            $requete .= '&CANCELURL='.urlencode($this->CancelUrl);
        }else throw new Exception('The CancelUrl variable expected a valid URL.');

        if(filter_var($this->ReturnUrl, FILTER_VALIDATE_URL)){
            $requete .= '&RETURNURL='.urlencode($this->ReturnUrl);
        }else throw new Exception('The ReturnUrl variable expected a valid URL.');

        if($this->Amount > 0){
            $requete .= '&AMT='.$this->Amount;
        }else throw new Exception('The amount must be a positive number.');

        if(array_key_exists($this->CurrencyCode, $this->listCurrenciesPossible)){
            $requete .= '&CURRENCYCODE='.$this->CurrencyCode;
        }else throw new Exception('The currency code is not known.');

        if($this->Description != ''){
            $requete .= '&DESC='.urlencode($this->Description);
        }
        if(array_key_exists($this->LocaleCode, $this->listCountriesPossible)){
            $requete .= '&LOCALECODE='.$this->LocaleCode;
        }else throw new Exception('The countrie code is not known.');

        //if null, remove it and if not, check if is image
        if($this->Logo != ''){
            if(filter_var($this->Logo, FILTER_VALIDATE_URL)){
                $requete .= '&HDRIMG='.urlencode($this->Logo);
            }else throw new Exception('The Logo url is not valid.');
        }

        $ch = curl_init($requete);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resultat_paypal = curl_exec($ch);
        if($resultat_paypal){
            $liste_param_paypal = $this->transformUrlParametersToArray($resultat_paypal);
            if ($liste_param_paypal['ACK'] == 'Success'){
                $this->paypal_token = $liste_param_paypal['TOKEN'];
                return $liste_param_paypal['TOKEN'];
            }else throw new Exception($liste_param_paypal['L_SHORTMESSAGE0'].' - '.$liste_param_paypal['L_LONGMESSAGE0']);
        }else throw new Exception(curl_error($ch));
        curl_close($ch);
    }

    public function setExpressCheckout(){
        if($this->paypal_token != ''){
            header('Location: '.$this->paypal_server.'webscr&cmd=_express-checkout&token='.$this->paypal_token);
            exit();
        }else throw new Exception('No token has been sent');
    }

    public function doExpressCheckout(){
        $requete = $this->getOptionBase();

        // On ajoute le reste des options
        $requete .= '&METHOD=DoExpressCheckoutPayment';
        if(isset($_GET['token']) && ! empty($_GET['token'])){
            $requete .= '&TOKEN='.htmlentities($_GET['token'], ENT_QUOTES);

        } else throw new Exception('The token is invalid');
        if($this->Amount > 0){
            $requete .= '&AMT='.$this->Amount;
        }else throw new Exception('The amount must be a positive number.');

        if(array_key_exists($this->CurrencyCode, $this->listCurrenciesPossible)){
            $requete .= '&CURRENCYCODE='.$this->CurrencyCode;
        }else throw new Exception('The currency code is not known.');

        if(isset($_GET['PayerID']) && !empty($_GET['PayerID'])){
            $requete .= '&PayerID='.htmlentities($_GET['PayerID'], ENT_QUOTES);
        } else throw new Exception('The payer ID is unvalid.');

        $requete .= '&PAYMENTACTION=sale';

        $ch = curl_init($requete);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resultat_paypal = curl_exec($ch);

        if($resultat_paypal){ // S'il y a une erreur, on affiche "Erreur", suivi du détail de l'erreur.
            $liste_param_paypal = $this->transformUrlParametersToArray($resultat_paypal);
            return $liste_param_paypal;
        }else throw new Exception(curl_error($ch));
        // On ferme notre session cURL.
        curl_close($ch);
    }

    public function getExpressCheckout($token = ''){
        $requete = $this->getOptionBase();
        $requete .= '&METHOD=GetExpressCheckoutDetails';
        if($token != ''){
            $requete .= '&TOKEN='.htmlentities($token, ENT_QUOTES); // Ajoute le jeton
        }else throw new Exception('The token is missing');

        $ch = curl_init($requete);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resultat_paypal = curl_exec($ch);

        if($resultat_paypal){            
            $liste_param_paypal = $this->transformUrlParametersToArray($resultat_paypal);
            return $liste_param_paypal;
        }else throw new Exception(curl_error($ch));
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
        if($this->Username_facilitator == '' || $this->Password_facilitator == '' || $this->Signature_facilitator == ''){
            throw new Exception('Misconfiguration regarding the facilitator'); 
        }
        return $this->base_url_api_paypal.'VERSION='.self::VERSION_API_PAYPAL.'&USER='.$this->Username_facilitator.'&PWD='.$this->Password_facilitator.'&SIGNATURE='.$this->Signature_facilitator;
    }
}
?>
