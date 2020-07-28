<?php
namespace App\API;

class UcozAPI {

    private $request;
    public function __construct(){
        $this->request = new \App\API\Ucoz\Request([
            'oauth_consumer_key'=>'ztghe54sfDgb6HjsFa',
            'oauth_consumer_secret'=>'aSHHiFp7Ut9e3nGq8sOfMrAmuWCYtc',
            'oauth_token'=>'v1ovl0uM2808yxe.gDkg2.D.w73FEwTdBleCkEkP',
            'oauth_token_secret'=>'GWlbk8kegIp7W17C436g.jennvc4chIKjNgipuBR',
        ]);
    }

    public function getVideos($page = 1) {
        $data = $this->request->get('/video', [
            'page' => $page
        ]);
        $data = json_decode($data);
        if (isset($data->videos)) {
            return $data->videos;
        } else {
            dd($data);
        }
    }
}