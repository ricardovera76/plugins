<?php

/*
 * Copyright (C) 2020-2021 Frank Wall
 * Copyright (C) 2018 Deciso B.V.
 * Copyright (C) 2018 Franco Fichtner <franco@opnsense.org>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace OPNsense\AcmeClient\LeAutomation;

use OPNsense\Core\Backend;
use OPNsense\Core\Config;
use OPNsense\AcmeClient\LeAccount;
use OPNsense\AcmeClient\LeUtils;

/**
 * LeAutomation stub file, contains shared logic for all automations.
 * @package OPNsense\AcmeClient
 */
abstract class Base extends \OPNsense\AcmeClient\LeCommon
{
    public const CONFIG_PATH = 'actions.action';

    /**
     * Initialize LeAutomation object by adding the required configuration.
     * @return boolean
     */
    public function init(string $certid, string $certname, string $accountuuid, bool $certecc = false)
    {
        // Get config object
        $this->loadConfig(self::CONFIG_PATH, $this->uuid);

        // Get account object to query ID
        $account = new LeAccount($accountuuid);

        // Store auxiliary information (required to glue stuff together)
        $this->cert_id = $certid;
        $this->account_id = (string)$account->id;
        $this->account_uuid = (string)$account->uuid;

        // Teach acme.sh about DNS API hook location
        $this->acme_env['_SCRIPT_HOME'] = self::ACME_SCRIPT_HOME;

        // Set log level
        $this->setLoglevel();

        // Set ACME CA
        $this->setCa($accountuuid);

        // Store acme filenames
        $this->acme_args[] = LeUtils::execSafe('--home %s', self::ACME_HOME_DIR);
        $this->acme_args[] = LeUtils::execSafe('--certpath %s', sprintf(self::ACME_CERT_FILE, $this->cert_id));
        $this->acme_args[] = LeUtils::execSafe('--keypath %s', sprintf(self::ACME_KEY_FILE, $this->cert_id));
        $this->acme_args[] = LeUtils::execSafe('--capath %s', sprintf(self::ACME_CHAIN_FILE, $this->cert_id));
        $this->acme_args[] = LeUtils::execSafe('--fullchainpath %s', sprintf(self::ACME_FULLCHAIN_FILE, $this->cert_id));

        // Main domain for acme
        $this->acme_args[] = LeUtils::execSafe('--domain %s', $certname);

        // ECC cert
        $this->cert_ecc = $certecc;
        if ($this->cert_ecc) {
            // Pass --ecc to acme client to locate the correct cert directory
            $this->acme_args[] = '--ecc';
        }

        return true;
    }

    /**
     * run all tasks related to this automation
     * @return boolean
     */
    public function run()
    {
        if (!($this->isEnabled())) {
            LeUtils::log('ignoring disabled automation: ' . (string)$this->config->name);
            return true; // not an error
        }

        // The prefix determines which automation flavour is being used.
        if (preg_match('/acme.*/i', $this->getType())) {
            $this->runAcme();
        } elseif (preg_match('/configd_.*/i', $this->getType())) {
            $this->runConfigd();
        } else {
            LeUtils::log_error('unsupported automation flavour: ' . $this->getType());
            return false;
        }
    }

    /**
     * run acme.sh deploy hooks commands
     * @return boolean
     */
    public function runAcme()
    {
        LeUtils::log('running automation (acme.sh): ' . $this->config->name);

        // Preparation to run acme client
        $proc_env = $this->acme_env; // env variables for proc_open()
        $proc_env['PATH'] = $this::ACME_ENV_PATH;
        $proc_desc = array(  // descriptor array for proc_open()
            0 => array("pipe", "r"), // stdin
            1 => array("pipe", "w"), // stdout
            2 => array("pipe", "w")  // stderr
        );
        $proc_pipes = array();

        // Run acme client
        $acmecmd = self::ACME_CMD
          . ' '
          . '--deploy '
          . implode(' ', $this->acme_args);
        LeUtils::log_debug('running acme.sh command: ' . (string)$acmecmd, $this->debug);
        $proc = proc_open($acmecmd, $proc_desc, $proc_pipes, null, $proc_env);

        // Make sure the resource could be setup properly
        if (is_resource($proc)) {
            // Close all pipes
            fclose($proc_pipes[0]);
            fclose($proc_pipes[1]);
            fclose($proc_pipes[2]);
            // Get exit code
            $result = proc_close($proc);
        } else {
            LeUtils::log_error('unable to start acme client process');
            return false;
        }

        // Check result
        if ($result) {
            LeUtils::log_error('running acme.sh deploy hook failed (' . $this->getType() . ')');
            return false;
        }

        return true;
    }

    /**
     * run configd commands
     * @return boolean
     */
    public function runConfigd()
    {
        LeUtils::log('running automation (configd): ' . $this->config->name);
        $backend = new \OPNsense\Core\Backend();
        $response = $backend->configdRun((string)$this->command, $this->command_args);
        return true;
    }

    /**
     * get automation type from configuration
     * @return string
     */
    public function getType()
    {
        return $this->config->type;
    }
}
