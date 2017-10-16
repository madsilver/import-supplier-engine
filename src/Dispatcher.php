<?php

/**
 * Created by PhpStorm.
 * User: silver
 * Date: 12/08/17
 * Time: 11:40
 */

namespace Silver;

/**
 * Class Dispatcher
 * @package Silver
 */
class Dispatcher
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $urlES;

    /**
     * @var string
     */
     protected $urlOwner;

    /**
     * @var array|bool
     */
    protected $params;

    /**
     * Dispatcher constructor.
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->params = parse_ini_file("conf/param.ini");
        $this->httpClient = new \GuzzleHttp\Client();
        $this->url  = strpos($url, "http://") ? 'http://'.$url : $url;
        $this->urlES = $url.$this->params['es_owner'];
        $this->urlOwner = $url.$this->params['owner'];
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function send(array $data) : string
    {
        try
        {
            $response = $this->httpClient->post($this->urlES, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'token' => $this->params['token']
                ],
                'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
            ]);

            if($response->getStatusCode() >= 400) {
                throw new \Exception($this->parseMessage($response->getBody()));
            }

            return mb_substr($response->getBody()->read(31), 7);
        }
        catch(\Exception $e)
        {
            $ex = $e->getResponse()->getBody()->getContents();
            throw new \Exception($this->parseMessage($ex));
        }

        return "0";
    }

    /**
     * @param array $data
     * @param string $id
     * @return string
     * @throws \Exception
     */
     public function ownerApproval(array $data, string $id)
     {
         try
         {
            $response = $this->httpClient->put($this->urlOwner.$id, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'token' => $this->params['token']
                ],
                'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
            ]);

            if($response->getStatusCode() >= 400) {
                throw new \Exception($this->parseMessage($response->getBody()));
            }
         }
         catch(\Exception $e)
         {
            $ex = $e->getResponse()->getBody()->getContents();
            throw new \Exception($this->parseMessage($ex));
         }
     }

     public function esApproval(array $data, string $id)
     {
         try
         {
            $response = $this->httpClient->put($this->urlES.$id, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'token' => $this->params['token']
                ],
                'body' => json_encode($data, JSON_UNESCAPED_UNICODE)
            ]);

            if($response->getStatusCode() >= 400) {
                throw new \Exception($this->parseMessage($response->getBody()));
            }
         }
         catch(\Exception $e)
         {
            $ex = $e->getResponse()->getBody()->getContents();
            throw new \Exception($this->parseMessage($ex));
         }
     }

     private function parseMessage(string $msg)
     {
         $invalidEmail = 'This value is not a valid email address';
         $sameCnpj = 'Already exists other owner with same cnpj';
         $shortPhone = 'This value is too short. It should have 10 characters or more';
         $blankPhone = 'phone"":{""errors"":[""This value should not be blank';

        if(strpos($msg, $invalidEmail) != false) {
            return $invalidEmail;
        }
        else if(strpos($msg, $sameCnpj) != false) {
            return $sameCnpj;
        }
        else if(strpos($msg, $shortPhone) != false) {
            return $shortPhone;
        }
        else if(strpos($msg, $blankPhone) != false) {
            return 'The phone value should not be blank';
        }

        return $msg;
     }
}
