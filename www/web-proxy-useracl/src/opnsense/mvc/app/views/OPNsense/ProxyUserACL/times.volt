{#
Copyright (C) 2017-2018 Smart-Soft

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

    $(document).ready(function () {
        $("#grid-times").UIBootgrid(
            {
                'search': '/api/proxyuseracl/times/searchTime',
                'get': '/api/proxyuseracl/times/getTime/',
                'set': '/api/proxyuseracl/times/setTime/',
                'add': '/api/proxyuseracl/times/addTime/',
                'del': '/api/proxyuseracl/times/delTime/',
            }
        );
    });
</script>

<div id="times">
    <table id="times-content">
        <tr>
            <td colspan="2">
                <table id="grid-times" class="table table-condensed table-hover table-striped table-responsive"
                       data-editDialog="DialogTimes">
                    <thead>
                    <tr>
                        <th data-column-id="Names" data-type="string" data-sortable="false"
                            data-visible="true">{{ lang._('Time') }}</th>
                        <th data-column-id="commands" data-width="7em" data-formatter="commands"
                            data-sortable="false">{{ lang._('Commands') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td></td>
                        <td>
                            <button data-action="add" type="button" class="btn btn-xs btn-default"><span
                                        class="fa fa-plus"></span></button>
                            <button data-action="deleteSelected" type="button" class="btn btn-xs btn-default"><span
                                        class="fa fa-trash-o"></span></button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>
</div>
{{ partial("layout_partials/base_dialog",['fields':formDialogTimes,'id':'DialogTimes','label':lang._('Edit time interval for black and white lists')]) }}
