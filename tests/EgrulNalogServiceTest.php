<?php

namespace Tests;

use EgrulNalogClient\EgrulNalogService;
use EgrulNalogClient\EgrulNalogClient;
use EgrulNalogClient\Exceptions\EgrulNalogCaptchaRequiredException;
use EgrulNalogClient\Exceptions\EgrulNalogException;
use EgrulNalogClient\Exceptions\EgrulNalogNotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class EgrulNalogServiceTest extends TestCase {



    /**
     * @param Response[] $responses
     *
     * @return EgrulNalogService
     */
    protected function getService(array $responses): EgrulNalogService {
        $mock = new MockHandler($responses);

        $handler = HandlerStack::create($mock);
        $transport = new Client(['handler' => $handler]);

        $client = new EgrulNalogClient($transport);
        $service = new EgrulNalogService($client);

        return $service;
    }



    public function testBlankResultVirtual(): void {
        $this->expectException(EgrulNalogNotFoundException::class);

        $service = $this->getService([
            new Response(200, [], '')
        ]);

        $service->search('661904082998');
    }



    public function testEmptyResultVirtual(): void {
        $this->expectException(EgrulNalogNotFoundException::class);

        $service = $this->getService([
            new Response(200, [], '{}')
        ]);

        $service->search('661904082998');
    }



    public function testStatusWaitVirtual(): void {
        $this->markTestSkipped('Need to implement!');

        $service = $this->getService([
            new Response(200, [], '{"status":"wait"}')
        ]);

        $service->search('661904082998');
    }



    public function testCaptchaSearch(): void {
        $this->expectException(EgrulNalogCaptchaRequiredException::class);

        $service = $this->getService([
            new Response(200, [], '{"ERRORS":{"captchaSearch":["Требуется ввести цифры с картинки"]}}')
        ]);

        $service->search('661904082998');
    }



    public function testErrorsResponse(): void {
        $this->expectException(EgrulNalogException::class);

        $service = $this->getService([
            new Response(200, [], '{"ERRORS":{"query":["Не заполнено обязательное поле \"Поисковый запрос\""]}}')
        ]);

        $service->search('661904082998');
    }



    public function testEmptyErrorsResponseVirtual(): void {
        $this->expectException(EgrulNalogException::class);

        $service = $this->getService([
            new Response(200, [], '{"ERRORS":["Не заполнено обязательное поле \"Поисковый запрос\""]}')
        ]);

        $service->search('661904082998');
    }



    public function testEmptyDataVirtual(): void {
        $this->expectException(EgrulNalogNotFoundException::class);

        $service = $this->getService([
            new Response(200, [], file_get_contents('tests/assets/search-result.json') ),
            new Response(200, [], '{}')
        ]);

        $result = $service->search('661904082998');

        $this->assertEquals([], $result);
    }



    public function testBlankDataVirtual(): void {
        $this->expectException(EgrulNalogNotFoundException::class);

        $service = $this->getService([
            new Response(200, [], file_get_contents('tests/assets/search-result.json') ),
            new Response(200, [], '')
        ]);

        $result = $service->search('661904082998');

        $this->assertEquals([], $result);
    }



    public function testEmptyRows(): void {
        $this->expectException(EgrulNalogNotFoundException::class);

        $service = $this->getService([
            new Response(200, [], file_get_contents('tests/assets/search-result.json') ),
            new Response(200, [], '{"rows":[]}')
        ]);

        $result = $service->search('661904082998');

        $this->assertEquals([], $result);
    }



    public function testCurrent(): void {
        $service = $this->getService([
            new Response(200, [], file_get_contents('tests/assets/search-result.json') ),
            new Response(200, [], file_get_contents('tests/assets/search-data.json') )
        ]);

        $result = $service->search('0905003544');

        $this->assertEquals([
            'title' => 'КФХ  "КЕКЛЕ-1"',
            'address' => '369229 КАРАЧАЕВО-ЧЕРКЕССКАЯ РЕСПУБЛИКА РАЙОН КАРАЧАЕВСКИЙ АУЛ КАМЕННОМОСТПЕРЕУЛОК КОМСОМОЛЬСКИЙ 4А',
            'inn' => '0905003544',
            'kpp' => '090501001',
            'ogrn' => '1030901075633',
            'type' => 'ul'
        ], $result);
    }



    public function testCurrentAndTermination(): void {
        $service = $this->getService([
            new Response(200, [], file_get_contents('tests/assets/search-result.json') ),
            new Response(200, [], file_get_contents('tests/assets/search-data-with-termination.json') )
        ]);

        $result = $service->search('661904082998');

        $this->assertEquals([
            'title' => 'ЯХИНА ОКСАНА РАШИТОВНА',
            'address' => null,
            'inn' => '661904082998',
            'kpp' => null,
            'ogrn' => '316965800019321',
            'type' => 'fl'
        ], $result);

    }



    public function testCurrentAndTermination2(): void {
        $service = $this->getService([
            new Response(200, [], file_get_contents('tests/assets/search-result.json') ),
            new Response(200, [], file_get_contents('tests/assets/search-data-termination.json') )
        ]);

        $result = $service->search('583716175273');

        $this->assertEquals([
            'title' => 'ЛАКОМКИНА ЕЛЕНА ЮРЬЕВНА',
            'address' => null,
            'inn' => '583716175273',
            'kpp' => null,
            'ogrn' => '316583500051066',
            'type' => 'fl'
        ], $result);

    }



    public function testSkipRecordOfRegistrationIsRecognizedAsErroneous() {
        $service = $this->getService([
            new Response(200, [], file_get_contents('tests/assets/search-result.json') ),
            new Response(200, [], file_get_contents('tests/assets/search-data-with-errorneous.json') )
        ]);

        $result = $service->search('390500180300');

        $this->assertEquals([
            'title' => 'МАСЛОВ АНДРЕЙ ВЛАДИМИРОВИЧ',
            'address' => null,
            'inn' => '390500180300',
            'kpp' => null,
            'ogrn' => '304390522500026',
            'type' => 'fl'
        ], $result);
    }



    public function testOnlyRecordOfRegistrationIsRecognizedAsErroneous() {
        $this->expectException(\Exception::class);

        $service = $this->getService([
            new Response(200, [], file_get_contents('tests/assets/search-result.json') ),
            new Response(200, [], file_get_contents('tests/assets/search-data-only-errorneous.json') )
        ]);

        $service->search('390500180300');
    }



    public function testOnlyTermination(): void {
        $this->expectExceptionMessage('Дата прекращения деятельности: 14.02.2011');

        $service = $this->getService([
            new Response(200, [], file_get_contents('tests/assets/search-result.json') ),
            new Response(200, [], file_get_contents('tests/assets/search-data-only-termination.json') )
        ]);

        $result = $service->search('661904082998');
    }



    public function testWrongSearch(): void {
        $this->expectException(EgrulNalogNotFoundException::class);

        $service = $this->getService([
            new Response(200, [], file_get_contents('tests/assets/search-result.json') ),
            new Response(200, [], file_get_contents('tests/assets/search-data-wrong-search.json') )
        ]);

        $service->search('661904082998');
    }



}
