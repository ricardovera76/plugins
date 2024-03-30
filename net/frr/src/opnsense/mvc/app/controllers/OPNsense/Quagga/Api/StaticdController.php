<?php

/**
 *    Copyright (C) 2024 Mike Shuey
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

namespace OPNsense\Quagga\Api;

use OPNsense\Base\ApiMutableModelControllerBase;

class StaticdController extends ApiMutableModelControllerBase
{
    protected static $internalModelName = 'staticd';
    protected static $internalModelClass = '\OPNsense\Quagga\Staticd';

    public function searchRouteAction()
    {
	return $this->searchBase(
	    'iproutes.iproute',
	    array("enabled", "iproute", "interfacename"));
    }
    public function getRouteAction($uuid = null)
    {
	$this->sessionClose();
	return $this->getBase('iproute', 'iproutes.iproute', $uuid);
    }
    public function setRouteAction($uuid)
    {
	return $this->setBase('iproute', 'iproutes.iproute', $uuid);
    }
    public function addRouteAction()
    {
	return $this->addBase('iproute', 'iproutes.iproute');
    }
    public function delRouteAction($uuid)
    {
	return $this->delBase('iproutes.iproute', $uuid);
    }
    public function toggleRouteAction($uuid)
    {
	return $this->toggleBase('iproutes.iproute', $uuid);
    }

    public function searchRoute6Action()
    {
	return $this->searchBase(
	    'ip6routes.ip6route',
	    array("enabled", "ip6route", "interfacename"));
    }
    public function getRoute6Action($uuid = null)
    {
	$this->sessionClose();
	return $this->getBase('ip6route', 'ip6routes.ip6route', $uuid);
    }
    public function setRoute6Action($uuid)
    {
	return $this->setBase('ip6route', 'ip6routes.ip6route', $uuid);
    }
    public function addRoute6Action()
    {
	return $this->addBase('ip6route', 'ip6routes.ip6route');
    }
    public function delRoute6Action($uuid)
    {
	return $this->delBase('ip6routes.ip6route', $uuid);
    }
    public function toggleRoute6Action($uuid)
    {
	return $this->toggleBase('ip6routes.ip6route', $uuid);
    }
}
