<?php

namespace EgrulNalogClient;


use EgrulNalogClient\Exceptions\EgrulNalogCaptchaRequiredException;
use EgrulNalogClient\Exceptions\EgrulNalogException;
use EgrulNalogClient\Exceptions\EgrulNalogNotFoundException;

class EgrulNalogService {

    protected $client;



    public function __construct(EgrulNalogClient $client) {
        $this->client = $client;
    }



    /**
     * @param string $query
     *
     * @return array
     *
     * @throws EgrulNalogNotFoundException
     * @throws EgrulNalogException
     */
    public function search(string $query) : array {
        $json = $this->client->getResult($query);
        $data = json_decode($json, true);

        if(empty($data['t']) || false === $data['t']) {
            throw new EgrulNalogNotFoundException();
        }


        $json = $this->client->getData($data['t']);
        $data = json_decode($json, true);

        if(false === isset($data['rows'])) {
            throw new EgrulNalogNotFoundException();
        }

        if(count($data['rows']) === 0) {
            throw new EgrulNalogNotFoundException();
        }

        $requisits = [];
        $exclude = null;

        foreach($data['rows'] as $row) {

//            if(isset($row['tot'], $row['cnt']) && ('0' === $row['tot'] || '0' === $row['cnt']) ) {
//                throw new \Exception('По заданным критериям поиска данных не найдено.');
//            }

            if($row['i'] === $query || $row['o'] === $query) {

                // if already exists and this is expired
                if(isset($row['e']) && $row['e']) {
                    if(false === empty($requisits) ) {
                        continue;
                    }

                    $exclude = new \Exception("Дата прекращения деятельности: {$row['e']}" );
                    continue;
                }

                // if already exists and this is expired
                if(isset($row['v']) && $row['v']) {
                    if(false === empty($requisits) ) {
                        continue;
                    }

                    $exclude = new \Exception("Запись о регистрации признана ошибочной: {$row['v']}" );
                    continue;
                }

                $requisits = [
                    'title' => trim($row['n']),
                    'address' => isset($row['a']) ? trim($row['a']) : null,
                    'inn' => $row['i'],
                    'kpp' => $row['p'] ?? null,
                    'ogrn' => $row['o'],
                    'type' => $row['k']
                ];
            }

        }

        if($requisits) {
            return $requisits;
        }

        if($exclude) {
            throw $exclude;
        }

        throw new EgrulNalogNotFoundException();
    }



}
