<?php


namespace EgrulNalogClient;


use BaseClient\BaseClientFactory;

class EgrulNalogFactory extends BaseClientFactory {

    /**
     * @param string $base_uri
     *
     * @return \GuzzleHttp\Client
     */
    public function getTransport($base_uri = 'https://egrul.nalog.ru/') : \GuzzleHttp\Client {
        return $this->getClient($base_uri);
    }

}
