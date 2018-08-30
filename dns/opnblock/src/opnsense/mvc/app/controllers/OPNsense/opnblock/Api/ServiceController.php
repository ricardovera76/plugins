<?php
namespace OPNsense\opnblock\Api;

use \OPNsense\Base\ApiControllerBase;
use \OPNsense\Core\Backend;

class ServiceController extends ApiControllerBase
{
    public function refreshAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
                $backend->configdRun("opnblock refresh");
                return array("message" => gettext("OPNblock's lists have been updated! Please restart your Unbound DNS server."));
        }
        return array("message" => gettext("Something went wrong..."));
    }
    
    public function reloadAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
             // refresh backend
               $backend->configdRun('template reload OPNsense/opnblock');       
        }
        return array("message" => gettext("Something went wrong..."));
    }
}
