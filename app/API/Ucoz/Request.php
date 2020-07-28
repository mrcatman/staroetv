<?php
namespace App\API\Ucoz;
/**
 * Набор методов для запроса к API uCoz. Версия для uCoz PHP сервера
 * @author Sergey Driver86 Pugovkin <sergey@pugovk.in> – разработчик методов для запроса (php версия)
 * @author Dmitry Kiselev <api@ucoz.net> – модификация и адаптация под uAPI + images. api.ucoz.net
 * @version 2.3 от 1 октряб 2016
 */


/**
Ссылка на ваш сайт в юкозе, для обращения к uAPI
Обратите внимание, что нужно вводить с / на конце.
Если у сайта есть прикрепленный домен – необходимо указывать его.
Если же сайт доступен по wwww – сайт необходимо указывать вместе с www. Например: http://www.mywebsite.ucoz.ru/ или http://www.mywebsite.com/
 */

/**
Закончили формировать ссылку
 */

class Request {
    /**
     * Настройки
     * @var array
     */
    public $config;
    /**
     * Обязательные параметры, передаваемые через URL при запросе к API
     * @var array
     */
    private $params;
    /**
     * Конструктор класса
     * @param array $config Настройки
     */
    private $myWebsite = 'http://staroetv.su/';

    function __construct($config = array()) {
        $this->config = $config;
        $this->params = array(
            'oauth_version' =>  '1.0',
            'oauth_timestamp' => time(),
            'oauth_nonce' => md5(microtime() . mt_rand()),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_consumer_key' => $this->config['oauth_consumer_key'],
            'oauth_token' => $this->config['oauth_token'],
        );
    }

    /**
     * Создание подписи запроса
     * @param string $method    Метод запроса, например GET
     * @param string $url       URL запроса, например /blog
     * @param string $params    Все параметры, передаваемые через URL при запросе к API
     * @return string
     */
    private function getSignature($method, $url, $params) {
        ksort($params);
        $baseString = strtoupper($method) . '&' . urlencode($url) . '&' . urlencode(strtr(http_build_query($params), array ('+' => '%20')));
        return urlencode(base64_encode(hash_hmac('sha1', $baseString, $this->config['oauth_consumer_secret'] . '&' . $this->config['oauth_token_secret'], true)));
    }

    /**
     * Возвращает базовое имя файла для использования в подписи запроса
     * @param array $match  Совпадения при поиске по регулярному выражению preg_replace_callback
     * @return string
     */
    private function getBaseName($match) {
        return basename($match[1]);
    }

    /**
     * Запрос к API методом GET
     * @param string $url   URL запроса, например /blog
     * @param array $data   Массив данных
     * @return array
     */
    public function get($url, $data = array()) {
        $this->params['oauth_nonce'] = md5(microtime() . mt_rand());
        $url = $this->myWebsite.'uapi' . trim(strtolower($url), '').'';
        $queryString = http_build_query($this->params + $data + array('oauth_signature' => $this->getSignature('GET', $url, $this->params + $data)));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url . '?' . $queryString);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * Запрос к API методом POST
     * @param string $url   URL запроса, например /blog
     * @param array $data   Массив данных
     * @return array
     */
    public function post($url, $data) {
        $myWebsite = $this->myWebsite;
        $this->params['oauth_nonce'] = md5(microtime() . mt_rand());

        /**
        Делаем так, чтобы изображения при отправке отправлялись,
        а не валились в инвалид сигнутаре
         */
        $x=1;
        while ($x<50) {
            if(empty($data['file'.$x])) break;
            $getfile1others = basename($data['file'.$x]);
            $findme = '@';
            $pos = strpos($getfile1others, $findme);
            if ($pos === false) {
                $getfile1shop_array = array(
                    'file'.$x => '@'.$getfile1others
                );
            } else {
                $getfile1shop_array = array(
                    'file'.$x => ''.$getfile1others
                );
            }
            unset($data['file'.$x]);
            $data = array_merge($getfile1shop_array, $data);
            $x++;
        }




        if(!empty($data['file_add_cnt']))  {
            $allcountfilesshop = $data['file_add_cnt'];
        }

        if ($url == '/shop/editgoods') {

            $i= $allcountfilesshop;
            while ($i<50) {
                if(empty($data['file_add_'.$i]) && $data['file_add_'.$i] != 'file_add_cnt') break;
                $getfile1shop = basename($data['file_add_'.$i]);
                $findme = '@';
                $pos = strpos($getfile1shop, $findme);
                if ($pos === false) {
                    $getfile1shop_array = array(
                        'file_add_'.$i => '@'.$getfile1shop
                    );
                } else {
                    $getfile1shop_array = array(
                        'file_add_'.$i => ''.$getfile1shop
                    );
                }
                unset($data['file_add_'.$i]);
                $data = array_merge($getfile1shop_array, $data);
                $i++;
            }

        }

        $url = $myWebsite.'uapi' . trim(strtolower($url), '').'/';
        $sign = array('oauth_signature' => $this->getSignature('POST', $url, $this->params + preg_replace_callback('/^@(.+)$/', array($this, 'getBaseName'), $data)));
        $queryString = http_build_query($sign);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($curl, CURLOPT_URL, $url . '?' . $forcurlpost);
        curl_setopt($curl, CURLOPT_POST, true);
        $forcurlpost = array_merge($this->params + $data, $sign);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $forcurlpost);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * Запрос к API методом PUT
     * @param string $url   URL запроса, например /blog
     * @param array $data   Массив данных
     * @return array
     */
    public function put($url, $data) {
        $myWebsite = $this->myWebsite;
        $this->params['oauth_nonce'] = md5(microtime() . mt_rand());

        /**
        Делаем так, чтобы изображения при отправке отправлялись,
        а не валились в инвалид сигнутаре
         */
        $x=1;
        while ($x<50) {
            if(empty($data['file'.$x])) break;
            $getfile1others = basename($data['file'.$x]);
            $findme = '@';
            $pos = strpos($getfile1others, $findme);
            if ($pos === false) {
                $getfile1shop_array = array(
                    'file'.$x => '@'.$getfile1others
                );
            } else {
                $getfile1shop_array = array(
                    'file'.$x => ''.$getfile1others
                );
            }
            unset($data['file'.$x]);
            $data = array_merge($getfile1shop_array, $data);
            $x++;
        }




        if(!empty($data['file_add_cnt']))  {
            $allcountfilesshop = $data['file_add_cnt'];
        }

        if ($url == '/shop/editgoods') {

            $i= $allcountfilesshop;
            while ($i<50) {
                if(empty($data['file_add_'.$i]) && $data['file_add_'.$i] != 'file_add_cnt') break;
                $getfile1shop = basename($data['file_add_'.$i]);
                $findme = '@';
                $pos = strpos($getfile1shop, $findme);
                if ($pos === false) {
                    $getfile1shop_array = array(
                        'file_add_'.$i => '@'.$getfile1shop
                    );
                } else {
                    $getfile1shop_array = array(
                        'file_add_'.$i => ''.$getfile1shop
                    );
                }
                unset($data['file_add_'.$i]);
                $data = array_merge($getfile1shop_array, $data);
                $i++;
            }

        }

        $url = $myWebsite.'uapi' . trim(strtolower($url), '').'/';
        $sign = array('oauth_signature' => $this->getSignature('PUT', $url, $this->params + preg_replace_callback('/^@(.+)$/', array($this, 'getBaseName'), $data)));
        $queryString = http_build_query($sign);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        $forcurlpost = array_merge($this->params + $data, $sign);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $forcurlpost);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * Запрос к API методом DELETE
     * @param string $url   URL запроса, например /blog
     * @param array $data   Массив данных
     * @return array
     */
    public function delete($url, $data) {
        $myWebsite = $this->myWebsite;
        $this->params['oauth_nonce'] = md5(microtime() . mt_rand());
        $url = $myWebsite.'uapi' . trim(strtolower($url), '').'/';
        $queryString = http_build_query($this->params + $data + array('oauth_signature' => $this->getSignature('DELETE', $url, $this->params + $data)));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url . '?' . $queryString);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $result = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $result;
    }
}

?>