<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class BaseTestCase extends TestCase
{
    protected $webDriver;

    protected function setUp(): void
    {
        parent::setUp();
        $capabilities = DesiredCapabilities::chrome();
        $this->webDriver = RemoteWebDriver::create(SELENIUM_SERVER_URL, $capabilities);
        $this->webDriver->manage()->timeouts()->implicitlyWait(IMPLICITLY_WAIT);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->webDriver->quit();
    }

    protected function acessarSistema($url, $usuario, $senha)
    {
        $this->webDriver->get($url);

        $this->webDriver->findElement(WebDriverBy::id('txtUsuario'))
            ->sendKeys($usuario);

        $this->webDriver->findElement(WebDriverBy::id('pwdSenha'))
            ->sendKeys($senha);

        $this->webDriver->findElement(WebDriverBy::id('Acessar'))
            ->submit();
    }

    protected function navegarPara($acao)
    {
        $acao = "acao=$acao";
        $xpath = "//a[contains(@href, '$acao')]";
        $link = $this->webDriver->findElement(WebDriverBy::xpath($xpath));
        $url = $link->getAttribute('href');
        $this->webDriver->get(SEI_URL . "/" . $url);
    }
}
