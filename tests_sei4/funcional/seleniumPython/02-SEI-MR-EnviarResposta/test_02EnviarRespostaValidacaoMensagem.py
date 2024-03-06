# Generated by Selenium IDE
# -*- coding: utf-8 -*-
import pytest
import os
import time
import json
import requests
import untangle
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities

class Test02SEIMREnviarResposta():
  def setup_method(self, method):
    if ("LOCAL" == os.environ["SELENIUMTEST_MODALIDADE"]):
        self.driver = webdriver.Chrome()
    else:
        self.driver = webdriver.Remote(command_executor=os.environ["SELENIUMTEST_SELENIUMHOST_URL"], desired_capabilities=DesiredCapabilities.CHROME)
        
    if ((not 'maximizar_screen' in os.environ) or os.environ['maximizar_screen'] == 'true'):
        self.driver.maximize_window()
        
    self.driver.implicitly_wait(5)
    self.vars = {}
  
  def teardown_method(self, method):
    self.driver.quit()
  
  def wait_for_window(self, timeout = 2):
    time.sleep(round(timeout / 1000))
    wh_now = self.driver.window_handles
    wh_then = self.vars["window_handles"]
    if len(wh_now) > len(wh_then):
      return set(wh_now).difference(set(wh_then)).pop()
  
  def test_00GerarProcesso(self):
    url = os.environ["SELENIUMTEST_SISTEMA_URL"]+"/sei/ws/SeiWS.php"
      
    # structured XML
    payload = """<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sei="Sei" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">
                  <soapenv:Header/>
                  <soapenv:Body>
                      <sei:gerarProcedimento soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                        <SiglaSistema xsi:type="xsd:string">PD_GOV_BR</SiglaSistema>
                        <IdentificacaoServico xsi:type="xsd:string">SeiResposta</IdentificacaoServico>
                        <IdUnidade xsi:type="xsd:string">110000001</IdUnidade>
                        <Procedimento xsi:type="sei:Procedimento">
                            <!--You may enter the following 9 items in any order-->
                            <IdTipoProcedimento xsi:type="xsd:string">100000381</IdTipoProcedimento>
                            <!--Optional:-->
                            <NumeroProtocolo xsi:type="xsd:string"></NumeroProtocolo>
                            <!--Optional:-->
                            <DataAutuacao xsi:type="xsd:string"></DataAutuacao>
                            <!--Optional:-->
                            <Especificacao xsi:type="xsd:string"></Especificacao>
                            <Assuntos xsi:type="sei:ArrayOfAssunto" SOAP-ENC:arrayType="sei:Assunto[]"/>
                            <Interessados xsi:type="sei:ArrayOfInteressado" SOAP-ENC:arrayType="sei:Interessado[]"/>
                            <Observacao xsi:type="xsd:string"></Observacao>
                            <NivelAcesso xsi:type="xsd:string">0</NivelAcesso>
                            <!--Optional:-->
                            <IdHipoteseLegal xsi:type="xsd:string"></IdHipoteseLegal>
                        </Procedimento>
                        <Documentos xsi:type="sei:ArrayOfDocumento" SOAP-ENC:arrayType="sei:Documento[]"/>
                        <ProcedimentosRelacionados xsi:type="sei:ArrayOfProcedimentoRelacionado" SOAP-ENC:arrayType="xsd:string[]"/>
                        <UnidadesEnvio xsi:type="sei:ArrayOfIdUnidade" SOAP-ENC:arrayType="xsd:string[]"/>
                        <SinManterAbertoUnidade xsi:type="xsd:string"></SinManterAbertoUnidade>
                        <SinEnviarEmailNotificacao xsi:type="xsd:string"></SinEnviarEmailNotificacao>
                        <DataRetornoProgramado xsi:type="xsd:string"></DataRetornoProgramado>
                        <DiasRetornoProgramado xsi:type="xsd:string"></DiasRetornoProgramado>
                        <SinDiasUteisRetornoProgramado xsi:type="xsd:string"></SinDiasUteisRetornoProgramado>
                        <IdMarcador xsi:type="xsd:string"></IdMarcador>
                        <TextoMarcador xsi:type="xsd:string"></TextoMarcador>
                      </sei:gerarProcedimento>
                  </soapenv:Body>
                </soapenv:Envelope>"""  
    # headers
    headers = {
        'Content-Type': 'text/xml; charset=utf-8'
    }
    # POST request
    global procedimentoFormatado
    response = requests.request("POST", url, headers=headers, data=payload)

    new_response = response.text.replace("SOAP-ENV:","").replace("ns1:","")
    obj = untangle.parse(new_response)
    procedimentoFormatado = obj.Envelope.Body.gerarProcedimentoResponse.parametros.ProcedimentoFormatado.cdata
  
  def test_02EnviarRespostaValidacaoMensagem(self):
    self.driver.get(os.environ["SELENIUMTEST_SISTEMA_URL"]+"/sip/login.php?sigla_orgao_sistema="+os.environ["SELENIUMTEST_SISTEMA_ORGAO"]+"&sigla_sistema=SEI")
    self.driver.find_element(By.ID, "txtUsuario").send_keys("teste")
    self.driver.find_element(By.ID, "pwdSenha").click()
    self.driver.find_element(By.ID, "pwdSenha").send_keys("teste")
    self.driver.find_element(By.ID, "sbmAcessar").click()

    self.driver.switch_to.default_content()
    print(procedimentoFormatado)
    self.driver.find_element(By.LINK_TEXT, procedimentoFormatado).click()

    self.driver.switch_to.frame(1)
    WebDriverWait(self.driver, 30).until(expected_conditions.visibility_of_element_located((By.XPATH, "//img[@alt=\'Enviar Resposta\']")))
    self.driver.find_element(By.XPATH, "//img[@alt=\'Enviar Resposta\']").click()
    self.driver.find_element(By.NAME, "btnEnviar").click()
    assert self.driver.switch_to.alert.text == "Informe a Mensagem."
    self.driver.switch_to.alert.accept()