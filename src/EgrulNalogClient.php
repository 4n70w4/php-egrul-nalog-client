<?php


namespace EgrulNalogClient;

use EgrulNalogClient\Exceptions\EgrulNalogCaptchaRequiredException;
use EgrulNalogClient\Exceptions\EgrulNalogException;
use BaseClient\BaseClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class EgrulNalogClient extends BaseClient {



    /**
     * @param ResponseInterface $response
     * @param string|null $content
     *
     * @throws EgrulNalogException
     */
    protected function exceptions(ResponseInterface $response, string $content = null) : void {
        if(405 === $response->getStatusCode() ) {
            throw new EgrulNalogException();
        }

        $data = json_decode($content, true);

        if(isset($data['ERRORS']) ) {
            if(isset($data['ERRORS']['captchaSearch']) ) {
                /*                array:1 [
                                    "ERRORS" => array:1 [
                                    "captchaSearch" => array:1 [
                                    0 => "Требуется ввести цифры с картинки"
                                ]
                                ]
                                ]*/
                throw new EgrulNalogCaptchaRequiredException();
            }

            if(isset($data['ERRORS']['query']) ) {
                throw new EgrulNalogException($data['ERRORS']['query'][0]);
            }

            throw new EgrulNalogException($content);
        }

    }



    /**
     * @param $query
     *
     * @return string
     */
    public function getResult($query) : string {
        return $this->getBody(
            new Request('POST', '/')
            , [RequestOptions::FORM_PARAMS => ['query' => $query]]
        );
    }



    /**
     * @param $result
     *
     * @return string
     */
    public function getData($result) : string {
        return $this->getBody(
            new Request('GET', "/search-result/{$result}")
        );
    }



}
