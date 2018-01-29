<?php

namespace Soukicz\FlexibeeFioFixer;

use FioApi\Downloader;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

class FlexibeeFioFixer {
    /** @var Downloader */
    private $bankDownloader;

    /** @var ClientInterface */
    private $httpClient;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(Downloader $bankDownloader, ClientInterface $httpClient, LoggerInterface $logger = null) {
        $this->bankDownloader = $bankDownloader;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public static function factory(string $accountingUsername, string $accountingPassword, string $accountingDomain, int $accountingPort, string $accountingCompany, string $bankToken, LoggerInterface $logger = null): self {
        return new self(new Downloader($bankToken), new Client([
            'base_uri' => 'https://' . $accountingDomain . ':' . $accountingPort . '/c/' . $accountingCompany . '/',
            'auth' => [$accountingUsername, $accountingPassword]
        ]), $logger);
    }

    public function update(\DateTimeInterface $dateFrom, \DateTimeInterface $dateTo) {
        $transactionList = $this->bankDownloader->downloadFromTo($dateFrom, $dateTo);
        foreach ($transactionList->getTransactions() as $transaction) {
            $id = str_pad($transaction->getId(), 13, '0', STR_PAD_LEFT);
            $message = $transaction->getUserMessage();
            if(mb_strlen($message) > 20) {
                $response = $this->httpClient->request('POST', 'banka.json', [
                    'json' => [
                        'flexibee' => [
                            'banka' => [
                                '@filter' => "vypisCisDokl='$id'",
                                '@create' => 'ignore',
                                'popis' => $message,
                            ]
                        ]
                    ],
                ]);

                if($this->logger) {
                    if($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                        $this->logger->info($id . ' updated');
                    } else {
                        $this->logger->critical($id . ': ' . $response->getBody());
                    }
                }
            }
        }
    }

}
