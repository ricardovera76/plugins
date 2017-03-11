<?php
namespace OPNsense\Quagga\Api;

use \OPNsense\Base\ApiControllerBase;
use \OPNsense\Quagga\OSPF;
use \OPNsense\Core\Config;
use \OPNsense\Base\ApiMutableModelControllerBase;
use \OPNsense\Base\UIModelGrid;

/**
 *    Copyright (C) 2015 - 2017 Deciso B.V.
 *    Copyright (C) 2015 J. Schellevis - Deciso B.V.
 *    Copyright (C) 2017 Fabian Franz
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */

class OspfsettingsController extends ApiMutableModelControllerBase
{
  static protected $internalModelName = 'OSPF';
  static protected $internalModelClass = '\OPNsense\Quagga\OSPF';
  public function getAction()
  {
    $result = array();
    if ($this->request->isGet()) {
        $mdlospf = new OSPF();
        $result['ospf'] = $mdlospf->getNodes();
    }
    return $result;
  }

  public function setAction()
  {
    $result = array("result"=>"failed");
    if ($this->request->isPost()) {
        // load model and update with provided data
        $mdlospf = new OSPF();
        $mdlospf->setNodes($this->request->getPost("ospf"));

        // perform validation
        $valMsgs = $mdlospf->performValidation();
        foreach ($valMsgs as $field => $msg) {
            if (!array_key_exists("validations", $result)) {
                $result["validations"] = array();
            }
            $result["validations"]["general.".$msg->getField()] = $msg->getMessage();
        }

        // serialize model to config and save
        if ($valMsgs->count() == 0) {
            $mdlospf->serializeToConfig();
            Config::getInstance()->save();
            $result["result"] = "saved";
        }
    }
    return $result;
  }


/////////////////////////////////////////////////////////////////////
    public function searchNetworkAction()
    {
        $this->sessionClose();
        $mdlOSPF = $this->getModel();
        $grid = new UIModelGrid($mdlOSPF->networks->network);
        return $grid->fetchBindRequest(
            $this->request,
            array("enabled", "ipaddr", "netmask", "area")
        );
    }
    /**
     * retrieve remote blacklist settings or return defaults
     * @param $uuid item unique id
     * @return array
     */
    public function getNetworkAction($uuid = null)
    {
        $mdlOSPF = $this->getModel();
        if ($uuid != null) {
            $node = $mdlOSPF->getNodeByReference('networks.network.' . $uuid);
            if ($node != null) {
                // return node
                return array("network" => $node->getNodes());
            }
        } else {
            $node = $mdlOSPF->networks->network->add();
            return array("network" => $node->getNodes());
        }
        return array();
    }
    
    public function addNetworkAction()
    {
        $result = array("result" => "failed");
        if ($this->request->isPost() && $this->request->hasPost("network")) {
            $result = array("result" => "failed", "validations" => array());
            $mdlOSPF = $this->getModel();
            $node = $mdlOSPF->networks->network->Add();
            $node->setNodes($this->request->getPost("network"));
            $valMsgs = $mdlOSPF->performValidation();

            foreach ($valMsgs as $field => $msg) {
                $fieldnm = str_replace($node->__reference, "network", $msg->getField());
                $result["validations"][$fieldnm] = $msg->getMessage();
            }

            if (count($result['validations']) == 0) {
                // save config if validated correctly
                $mdlOSPF->serializeToConfig();
                Config::getInstance()->save();
                $result["result"] = "saved";
            }
        }
        return $result;
    }
    public function delNetworkAction($uuid)
    {

        $result = array("result" => "failed");

        if ($this->request->isPost()) {
            $mdlOSPF = $this->getModel();
            if ($uuid != null) {
                if ($mdlOSPF->networks->network->del($uuid)) {
                    $mdlOSPF->serializeToConfig();
                    Config::getInstance()->save();
                    $result['result'] = 'deleted';
                } else {
                    $result['result'] = 'not found';
                }
            }
        }
        return $result;
    }
    public function setNetworkAction($uuid)
    {
        if ($this->request->isPost() && $this->request->hasPost("network")) {
            $mdlNetwork = $this->getModel();
            if ($uuid != null) {
                $node = $mdlNetwork->getNodeByReference('networks.network.' . $uuid);
                if ($node != null) {
                    $result = array("result" => "failed", "validations" => array());
                    $networkInfo = $this->request->getPost("network");

                    $node->setNodes($networkInfo);
                    $valMsgs = $mdlNetwork->performValidation();
                    foreach ($valMsgs as $field => $msg) {
                        $fieldnm = str_replace($node->__reference, "network", $msg->getField());
                        $result["validations"][$fieldnm] = $msg->getMessage();
                    }

                    if (count($result['validations']) == 0) {
                        // save config if validated correctly
                        $mdlNetwork->serializeToConfig();
                        Config::getInstance()->save();
                        $result = array("result" => "saved");
                    }
                    return $result;
                }
            }
        }
        return array("result" => "failed");
    }
    public function toggleNetworkAction($uuid)
    {

        $result = array("result" => "failed");

        if ($this->request->isPost()) {
            $mdlNetwork = $this->getModel();
            if ($uuid != null) {
                $node = $mdlNetwork->getNodeByReference('networks.network.' . $uuid);
                if ($node != null) {
                    if ($node->enabled->__toString() == "1") {
                        $result['result'] = "Disabled";
                        $node->enabled = "0";
                    } else {
                        $result['result'] = "Enabled";
                        $node->enabled = "1";
                    }
                    // if item has toggled, serialize to config and save
                    $mdlNetwork->serializeToConfig();
                    Config::getInstance()->save();
                }
            }
        }
        return $result;
    }
}
