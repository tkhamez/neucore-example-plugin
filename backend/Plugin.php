<?php

declare(strict_types=1);

namespace Neucore\Plugin\Example;

use Neucore\Plugin\Core\EsiClientInterface;
use Neucore\Plugin\Core\Exception;
use Neucore\Plugin\Core\FactoryInterface;
use Neucore\Plugin\Core\OutputInterface;
use Neucore\Plugin\Data\CoreAccount;
use Neucore\Plugin\Data\CoreRole;
use Neucore\Plugin\Data\NavigationItem;
use Neucore\Plugin\Data\PluginConfiguration;
use Neucore\Plugin\GeneralInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class Plugin implements GeneralInterface
{
    public function __construct(
        LoggerInterface $logger,
        private PluginConfiguration $configuration,
        private FactoryInterface $factory,
    ) {
    }

    public function onConfigurationChange(): void
    {
    }

    public function request(
        string $name,
        ServerRequestInterface $request,
        ResponseInterface $response,
        ?CoreAccount $coreAccount,
    ): ResponseInterface {

        // Redirect to frontend
        if ($name === 'index') {
            return $response
                ->withHeader('Location', "/plugin/example/index.html?id={$this->configuration->id}")
                ->withStatus(302);
        }

        // Ajax request, return logged-in user.
        if ($name === 'user') {
            $json = [
                'name' => '(not logged in)',
                'authenticated' => false,
            ];
            if ($coreAccount) {
                $json['name'] = $coreAccount->main->name;
                $json['authenticated'] = true;
            }
            return $this->jsonResponse($response, $json);
        }

        // Ajax request, return ESI data.
        if ($name === 'esi') {
            if (!$coreAccount) {
                return $response->withStatus(403); // not authorized
            }
            $json = [
                'result' => null,
                'error' => null,
            ];
            $num = $request->getQueryParams()['num'] ?? '1';
            $charId = $coreAccount->main->id;
            try {
                $esiResponse = $this->factory->getEsiClient()->request(
                    esiPath: $num === '1' ? "/latest/characters/$charId/wallet/" : "/latest/characters/$charId/",
                    characterId: $num === '1' ? $charId : null,
                    eveLoginName: $num === '1' ? 'wallet' : EsiClientInterface::DEFAULT_LOGIN_NAME,
                );
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
                return $this->jsonResponse($response, $json);
            }
            if ($esiResponse->getStatusCode() === 200) {
                $json['result'] = json_decode($esiResponse->getBody()->__toString());
            } else {
                $json['error'] = json_decode($esiResponse->getBody()->__toString());
            }
            return $this->jsonResponse($response, $json);
        }

        return $response;
    }

    public function getNavigationItems(): array
    {
        return [
            new NavigationItem(
                NavigationItem::PARENT_ROOT,
                'Example Plugin',
                '/index',
                '_self',
                [CoreRole::ANONYMOUS, CoreRole::USER]
            ),
        ];
    }

    public function command(array $arguments, array $options, OutputInterface $output): void
    {
        $output->writeLine('Done.');
    }

    private function jsonResponse(ResponseInterface $response, array $data): ResponseInterface
    {
        $json = (string)json_encode($data);
        $response->getBody()->write($json);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', (string)strlen($json));
    }
}
