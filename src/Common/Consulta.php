<?php

namespace NFePHP\Gtin\Common;

use NFePHP\Common\Certificate;
use NFePHP\Common\DOMImproved;
use NFePHP\Common\Soap\SoapBase;
use NFePHP\Common\Soap\SoapInterface;
use NFePHP\Common\Exception\SoapException;

class Consulta extends SoapBase implements SoapInterface
{
    public $disablesec = true;

    /**
     * Constructor
     *
     * @param Certificate $certificate
     */
    public function __construct(Certificate $certificate)
    {
        parent::__construct($certificate);
    }

    /**
     * Realiza a consulta do GTIN no cadastro CCG
     *
     * @param  string $gtin
     * @return object
     */
    public function consulta($gtin)
    {
        $request = '<consGTIN versao="1.00" xmlns="http://www.portalfiscal.inf.br/nfe"><GTIN>' . $gtin . '</GTIN></consGTIN>';
        $url = 'https://dfe-servico.svrs.rs.gov.br/ws/ccgConsGTIN/ccgConsGTIN.asmx';
        $opration = 'ccgConsGTIN';
        $action = 'http://www.portalfiscal.inf.br/nfe/wsdl/ccgConsGtin/ccgConsGTIN';
        $response = $this->send(
            $url,
            'ccgConsGTIN',
            $action = 'http://www.portalfiscal.inf.br/nfe/wsdl/ccgConsGtin/ccgConsGTIN',
            SOAP_1_2,
            [],
            [],
            $request,
            null
        );
        return $this->extractResponse($response);
    }

    /**
     * Extrai os cados da resposta da SEFAZ
     *
     * @param  string $content
     * @return object
     */
    protected function extractResponse(string $content): object
    {
        $dom = new DOMImproved();
        $dom->loadXML($content);
        $node = $dom->getElementsByTagName('retConsGTIN')->item(0);
        $cstat = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
        if ($cstat == '9490') {
            $resp = [
                'sucesso' => true,
                'motivo' => "Dados encontrados.",
                'xProd' => $node->getElementsByTagName('xProd')->item(0)->nodeValue ?? null,
                'NCM' => $node->getElementsByTagName('NCM')->item(0)->nodeValue ?? null,
                'CEST' => $node->getElementsByTagName('CEST')->item(0)->nodeValue ?? null,
            ];
        } elseif ($cstat == '9496') {
            $resp = [
                'sucesso' => true,
                'motivo' => "Dados encontrados, mas não disponíveis."
            ];
        } else {
            //ocorreu algum erro
            $resp = [
                'sucesso' => false,
                'motivo' => $node->getElementsByTagName('xMotivo')->item(0)->nodeValue ?? null
            ];
        }
        return (object) $resp;
    }

    /**
     * @param  string           $url
     * @param  string           $operation
     * @param  string           $action
     * @param  int              $soapver
     * @param  array            $parameters
     * @param  array            $namespaces
     * @param  string           $request
     * @param  \SoapHeader|null $soapheader
     * @return mixed|string
     */
    public function send(
        $url,
        $operation = '',
        $action = '',
        $soapver = SOAP_1_2,
        $parameters = [],
        $namespaces = [],
        $request = '',
        $soapheader = null
    ) {
        //check or create key files
        //before send request
        $this->saveTemporarilyKeyFiles();
        $envelope = "<soap:Envelope "
            . "xmlns:soap=\"http://www.w3.org/2003/05/soap-envelope\" "
            . "xmlns:ccg=\"http://www.portalfiscal.inf.br/nfe/wsdl/ccgConsGtin\">"
            . "<soap:Header/>"
            . "<soap:Body>"
            . "<ccg:ccgConsGTIN>"
            . "<ccg:nfeDadosMsg>{$request}</ccg:nfeDadosMsg>"
            . "</ccg:ccgConsGTIN>"
            . "</soap:Body>"
            . "</soap:Envelope>";
        $msgSize = strlen($envelope);
        $parameters = [
            "Accept-Encoding: gzip,deflate",
            "Content-Type: application/soap+xml;charset=utf-8;action=\"$action\"",
            "Content-length: $msgSize"
        ];
        $this->requestHead = implode("\n", $parameters);
        $this->requestBody = $envelope;
        try {
            $oCurl = curl_init();
            curl_setopt($oCurl, CURLOPT_URL, $url);
            curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $this->soaptimeout);
            curl_setopt($oCurl, CURLOPT_TIMEOUT, $this->soaptimeout + 20);
            curl_setopt($oCurl, CURLOPT_HEADER, 1);
            curl_setopt($oCurl, CURLOPT_HTTP_VERSION, $this->httpver);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, $this->soapprotocol);
            curl_setopt($oCurl, CURLOPT_SSLCERT, $this->tempdir . $this->certfile);
            curl_setopt($oCurl, CURLOPT_SSLKEY, $this->tempdir . $this->prifile);
            if (!empty($this->temppass)) {
                curl_setopt($oCurl, CURLOPT_KEYPASSWD, $this->temppass);
            }
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $envelope);
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $parameters);

            $response = curl_exec($oCurl);
            $this->soaperror = curl_error($oCurl);
            $this->soaperror_code = curl_errno($oCurl);
            $ainfo = curl_getinfo($oCurl);
            if (is_array($ainfo)) {
                $this->soapinfo = $ainfo;
            }
            $headsize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
            $httpcode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
            curl_close($oCurl);
            $this->responseHead = trim(substr($response, 0, $headsize));
            $this->responseBody = trim(substr($response, $headsize));
            $this->saveDebugFiles(
                $operation,
                $this->requestHead . "\n" . $this->requestBody,
                $this->responseHead . "\n" . $this->responseBody
            );
        } catch (\Exception $e) {
            throw SoapException::unableToLoadCurl($e->getMessage());
        }
        if ($this->soaperror != '') {
            if (intval($this->soaperror_code) == 0) {
                $this->soaperror_code = 7;
            }
            throw SoapException::soapFault($this->soaperror . " [$url]", $this->soaperror_code);
        }
        if ($httpcode != 200) {
            $msg = $this->getCodeMessage($httpcode);
            if (intval($httpcode) == 0) {
                $httpcode = 52;
            } elseif ($httpcode == 500) {
                $httpcode = 89;
            }
            throw SoapException::soapFault($msg, $httpcode);
        }
        return $this->responseBody;
    }

    /**
     * Extrai mensagem da liste de erros HTTP
     *
     * @param  integer $code
     * @return string
     */
    private function getCodeMessage($code)
    {
        $codes = json_decode(file_get_contents(__DIR__ . '/httpcodes.json'), true);
        if (!empty($codes[$code])) {
            return $codes[$code]['description'];
        }
        return "Erro desconhecido.";
    }
}
