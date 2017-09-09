{#

    Copyright (C) 2017 Fabian Franz
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

    1. Redistributions of source code must retain the above copyright notice,
       this list of conditions and the following disclaimer.

    2. Redistributions in binary form must reproduce the above copyright
       notice, this list of conditions and the following disclaimer in the
       documentation and/or other materials provided with the distribution.

    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.


#}


<script type="text/javascript">

$( document ).ready(function() {
    var data_get_map = {
        'general': '/api/tor/general/get', 
        'relay': '/api/tor/relay/get'
    };
    mapDataToFormUI(data_get_map).done(function(data){
        formatTokenizersUI();
        $('select.dropdownstyle').selectpicker('refresh');
    });
    ajaxCall(url="/api/tor/service/status", sendData={}, callback=function(data,status) {
        updateServiceStatusUI(data['result']);
    });

    // link save button to API set action
    [
      {'selector': '#generalsaveAct', 'endpoint': '/api/tor/general/set', 'formid': 'general'},
      {'selector': '#relaysaveAct', 'endpoint': '/api/tor/relay/set', 'formid': 'relay'}
    ].forEach(function (cfg) {
        $(cfg.selector).click(function(){
            saveFormToEndpoint(url=cfg.endpoint, formid=cfg.formid,callback_ok=function(){
                $(cfg.selector + " .saveAct_progress").addClass("fa fa-spinner fa-pulse");
                ajaxCall(url="/api/tor/service/restart", sendData={}, callback=function(data,status) {
                    ajaxCall(url="/api/tor/service/status", sendData={}, callback=function(data,status) {
                        updateServiceStatusUI(data['result']);
                    });
                    $(cfg.selector + " .saveAct_progress").removeClass("fa fa-spinner fa-pulse");
                });
            });
        });
    });
    $("#grid-hidden").UIBootgrid(
        { 'search':'/api/tor/hiddenservice/searchservice',
          'get':'/api/tor/hiddenservice/getservice/',
          'set':'/api/tor/hiddenservice/setservice/',
          'add':'/api/tor/hiddenservice/addservice/',
          'del':'/api/tor/hiddenservice/delservice/',
          'toggle':'/api/tor/hiddenservice/toggleservice/',
          'options':{selection:false, multiSelect:false}
        }
    );
    $("#grid-hiddenacl").UIBootgrid(
        { 'search':'/api/tor/hiddenserviceacl/searchacl',
          'get':'/api/tor/hiddenserviceacl/getacl/',
          'set':'/api/tor/hiddenserviceacl/setacl/',
          'add':'/api/tor/hiddenserviceacl/addacl/',
          'del':'/api/tor/hiddenserviceacl/delacl/',
          'toggle':'/api/tor/hiddenserviceacl/toggleacl/',
          'options':{selection:false, multiSelect:false}
        }
    );
    $("#grid-toracl").UIBootgrid(
        { 'search':'/api/tor/socksacl/searchacl',
          'get':'/api/tor/socksacl/getacl/',
          'set':'/api/tor/socksacl/setacl/',
          'add':'/api/tor/socksacl/addacl/',
          'del':'/api/tor/socksacl/delacl/',
          'toggle':'/api/tor/socksacl/toggleacl/',
          'options':{selection:false, multiSelect:false}
        }
    );
});

</script>
<ul class="nav nav-tabs" data-tabs="tabs" id="maintabs">
    <li class="active"><a data-toggle="tab" href="#general">{{ lang._('General') }}</a></li>
    <li><a data-toggle="tab" href="#acl">{{ lang._('ACL (SOCKS Proxy)') }}</a></li>
    <li><a data-toggle="tab" href="#hidden">{{ lang._('Hidden Services') }}</a></li>
    <li><a data-toggle="tab" href="#hiddenrouting">{{ lang._('Routing (Hidden Services)') }}</a></li>
    <li><a data-toggle="tab" href="#relay">{{ lang._('Relaying') }}</a></li>
</ul>

<div class="tab-content content-box tab-content" style="padding-bottom: 1.5em;">
    <div id="general" class="tab-pane fade in active">
        {{ partial("layout_partials/base_form",['fields': general,'id':'general'])}}
        <div class="col-md-12">
            <hr />
            <button class="btn btn-primary" id="generalsaveAct" type="button"><b>{{ lang._('Save') }}</b> <i class="saveAct_progress"></i></button>
        </div>
    </div>
    <div id="acl" class="tab-pane fade in">
        <table id="grid-toracl" class="table table-responsive" data-editDialog="toracldlg">
          <thead>
              <tr>
                  <th data-column-id="enabled" data-type="string" data-formatter="rowtoggle">{{ lang._('Enabled') }}</th>
                  <th data-column-id="action" data-type="string" data-visible="true">{{ lang._('Action') }}</th>
                  <th data-column-id="type" data-type="string" data-visible="true">{{ lang._('Protocol') }}</th>
                  <th data-column-id="network" data-type="string" data-visible="true">{{ lang._('Network') }}</th>
                  <th data-column-id="uuid" data-type="string" data-identifier="true" data-visible="false">{{ lang._('ID') }}</th>
                  <th data-column-id="commands" data-formatter="commands" data-sortable="false">{{ lang._('Commands') }}</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
          <tfoot>
              <tr>
                  <td colspan="3"></td>
                  <td>
                      <button data-action="add" type="button" class="btn btn-xs btn-default"><span class="fa fa-plus"></span></button>
                      <!-- <button data-action="deleteSelected" type="button" class="btn btn-xs btn-default"><span class="fa fa-trash-o"></span></button> -->
                  </td>
              </tr>
          </tfoot>
      </table>
    </div>
    <div id="hidden" class="tab-pane fade in">
        <table id="grid-hidden" class="table table-responsive" data-editDialog="hiddenservicedlg">
          <thead>
              <tr>
                  <th data-column-id="enabled" data-type="string" data-formatter="rowtoggle">{{ lang._('Enabled') }}</th>
                  <th data-column-id="name" data-type="string" data-visible="true">{{ lang._('Name') }}</th>
                  <th data-column-id="uuid" data-type="string" data-identifier="true" data-visible="false">{{ lang._('ID') }}</th>
                  <th data-column-id="commands" data-formatter="commands" data-sortable="false">{{ lang._('Commands') }}</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
          <tfoot>
              <tr>
                  <td colspan="3"></td>
                  <td>
                      <button data-action="add" type="button" class="btn btn-xs btn-default"><span class="fa fa-plus"></span></button>
                      <!-- <button data-action="deleteSelected" type="button" class="btn btn-xs btn-default"><span class="fa fa-trash-o"></span></button> -->
                  </td>
              </tr>
          </tfoot>
      </table>
    </div>
    <div id="hiddenrouting" class="tab-pane fade in">
        <table id="grid-hiddenacl" class="table table-responsive" data-editDialog="hiddenserviceacl">
            <thead>
                <tr>
                    <th data-column-id="enabled" data-type="string" data-formatter="rowtoggle">{{ lang._('Enabled') }}</th>
                    <th data-column-id="hiddenservice" data-type="string" data-visible="true">{{ lang._('Hidden Service') }}</th>
                    <th data-column-id="port" data-type="string" data-visible="true">{{ lang._('Port') }}</th>
                    <th data-column-id="target_host" data-type="string" data-visible="true">{{ lang._('Target Host') }}</th>
                    <th data-column-id="target_port" data-type="string" data-visible="true">{{ lang._('Target Port') }}</th>
                    <th data-column-id="uuid" data-type="string" data-identifier="true" data-visible="false">{{ lang._('ID') }}</th>
                    <th data-column-id="commands" data-formatter="commands" data-sortable="false">{{ lang._('Commands') }}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5"></td>
                    <td>
                        <button data-action="add" type="button" class="btn btn-xs btn-default"><span class="fa fa-plus"></span></button>
                        <!-- <button data-action="deleteSelected" type="button" class="btn btn-xs btn-default"><span class="fa fa-trash-o"></span></button> -->
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div id="relay" class="tab-pane fade in">
        {{ partial("layout_partials/base_form",['fields': relay,'id':'relay'])}}
        <div class="col-md-12">
            <hr />
            <button class="btn btn-primary" id="relaysaveAct" type="button"><b>{{ lang._('Save') }}</b> <i class="saveAct_progress"></i></button>
        </div>
    </div>
</div>

{{ partial("layout_partials/base_dialog",['fields': toracl,'id':'toracldlg', 'label':lang._('Edit ACL Entry')]) }}
{{ partial("layout_partials/base_dialog",['fields': hidden_service,'id':'hiddenservicedlg', 'label':lang._('Edit Hidden Service')]) }}
{{ partial("layout_partials/base_dialog",['fields': hidden_service_acl,'id':'hiddenserviceacl', 'label':lang._('Edit Hidden Service Route')]) }}
