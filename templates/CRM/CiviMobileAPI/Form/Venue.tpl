{include file="CRM/common/jsortable.tpl"}
{literal}
  <style>
    .ui-dialog-content.ui-widget-content.modal-dialog {
      height: auto!important;
    }
    .venue-attached-file {
      width: 100px;
      height: 100px;
      object-fit: cover;
    }
  </style>
{/literal}
<div class="crm-container">
  <div class="crm-block crm-form-block crm-civimobile-venue-form-block">
    <div class="crm-submit-buttons">
      {if $action eq 4}
        {if $can_edit_venue}
          <a href="{crmURL p='civicrm/civimobile/venue' q='reset=1&action=update&id='}{$venue.id}{"&location_id="}{$location_id}" class="edit button" title="{ts}Edit{/ts}"><span><i class="crm-i fa-pencil"></i> {ts}Edit{/ts}</span></a>
        {/if}
        {if $can_delete_venue}
          <a href="{crmURL p='civicrm/civimobile/venue' q='reset=1&action=delete&id='}{$venue.id}{"&location_id="}{$location_id}" class="delete button" title="{ts}Delete{/ts}"><span><i class="crm-i fa-trash"></i> {ts}Delete{/ts}</span></a>
        {/if}
      {/if}
      {include file="CRM/common/formButtons.tpl" location="top"  multiple="multiple"}
    </div>
    {if $action eq 1 or $action eq 2} {*ADD and EDIT*}
      <table class="form-layout-compressed">
        <tr role="row">
          <td class="label">{$form.venue_name.label}</td>
          <td class="view-value">{$form.venue_name.html}</td>
        </tr>
        <tr role="row">
          <td class="label">{$form.description.label}</td>
          <td class="view-value">{$form.description.html}</td>
        </tr>
        <tr role="row">
          <td class="label">{$form.is_active.label}</td>
          <td class="view-value">{$form.is_active.html}
          </td>
        </tr>
        <tr role="row">
          <td class="label">{$form.weight.label}</td>
          <td class="view-value">{$form.weight.html}
          </td>
        </tr>
      </table>
      <fieldset id="location_g" class="crm-collapsible">
        <legend class="collapsible-title">{ts}Location{/ts}</legend>
        <div id="location_screen">
          <table class="form-layout-compressed">
            <tr role="row">
              <td class="label">{$form.address.label} {help id="address-help"}</td>
              <td class="view-value">{$form.address.html}</td>
            </tr>
            <tr role="row">
              <td class="label">{$form.address_description.label} {help id="address-description-help"}</td>
              <td class="view-value">{$form.address_description.html}
              </td>
            </tr>
            <tr role="row">
              <td class="label">{$form.attached_file.label} {help id="scheme-help"}</td>
              <td class="view-value">
                {$form.attached_file.html}
                {if $venue.attached_files[0].url}
                  <div>
                    {if $venue.attached_files[0].type eq 'image/jpeg' or $venue.attached_files[0].type eq 'image/png'}
                      <a href="{$venue.attached_files[0].url}" class="crm-image-popup">
                        <img class="venue-attached-file" src="{$venue.attached_files[0].url}">
                      </a>
                    {else}
                      <a href="{$venue.attached_files[0].url}" target="_blank" class="attached-file-link"><i
                                class="crm-i fa-file"></i> View attached file</a>
                    {/if}
                  </div>
                  <a class="delete-venue-attached-file" style="color:red" href="javascript:deleteVenueImage();"><i
                            class="crm-i fa-trash"></i> Delete venue file</a>
                {/if}
              </td>
            </tr>
          </table>
        </div>
      </fieldset>
    {/if}

    {if $action eq 4 } {*view*}
      <table class="crm-info-panel">
        <tr>
          <td class="label">{$form.venue_name.label}</td>
          <td>{$venue.name}</td>
        </tr>
        <tr class="odd" role="row">
          <td class="label">{$form.description.label}</td>
          <td>{$venue.description}</td>
        </tr>
        <tr class="odd" role="row">
          <td class="label">{$form.is_active.label}</td>
          <td>{if $venue.is_active eq 1}Yes{else}No{/if}</td>
        </tr>
      </table>
      <fieldset id="location" class="crm-collapsible">
        <legend class="collapsible-title">{ts}Location{/ts}</legend>
        <div id="location_screen">
          <table class="crm-info-panel">
            <tr class="odd" role="row">
              <td class="label">{$form.address.label}</td>
              <td>{$venue.address}</td>
            </tr>
            <tr class="odd" role="row">
              <td class="label">{$form.address_description.label}</td>
              <td>{$venue.address_description}</td>
            </tr>
            <tr class="odd" role="row">
              <td class="label">{$form.attached_file.label}</td>
              <td>
                {if $venue.attached_files[0].url}
                  {if $venue.attached_files[0].type eq 'image/jpeg' or $venue.attached_files[0].type eq 'image/png'}
                    <a href="{$venue.attached_files[0].url}" class="crm-image-popup">
                        <img class="venue-attached-file" src="{$venue.attached_files[0].url}">
                    </a>
                  {else}
                    <a href="{$venue.attached_files[0].url}" target="_blank"><i class="crm-i fa-file"></i> View attached file</a>
                  {/if}
                {/if}
              </td>
            </tr>
          </table>
        </div>
      </fieldset>
    {/if}

    {if $action eq 8 } {*delete*}
      <div class="status">Are you sure ?</div>
    {/if}
    <div class="crm-submit-buttons">
      {if $action eq 4}
        {if $can_edit_venue}
          <a href="{crmURL p='civicrm/civimobile/venue' q='reset=1&action=update&id='}{$venue.id}{"&location_id="}{$location_id}" class="edit button" title="{ts}Edit{/ts}"><span><i class="crm-i fa-pencil"></i> {ts}Edit{/ts}</span></a>
        {/if}
        {if $can_delete_venue}
          <a href="{crmURL p='civicrm/civimobile/venue' q='reset=1&action=delete&id='}{$venue.id}{"&location_id="}{$location_id}" class="delete button" title="{ts}Delete{/ts}"><span><i class="crm-i fa-trash"></i> {ts}Delete{/ts}</span></a>
        {/if}
      {/if}
      {include file="CRM/common/formButtons.tpl" location="bottom"  multiple="multiple"}
    </div>
  </div>
</div>

{if $venue.attached_files[0].url}
{literal}
  <script>
    function deleteVenueImage() {
      var confirmPopup = confirm("Are you sure you want to delete this image ?");
      if (confirmPopup == true) {
        CRM.api3('CiviMobileVenueAttachFile', 'delete', {
          "id": {/literal}{$venue.id}{literal}
        }).then(function (result) {
          if (result.is_error == 0) {
            if (CRM.$('.ui-dialog').length) {
              CRM.alert(ts('File deleted'), ts('Success'), 'success');
            }
            CRM.$('.venue-attached-file').remove();
            CRM.$('.delete-venue-attached-file').remove();
            CRM.$('.attached-file-link').remove();

          } else if (result.error_code == 'venue_attached_file_not_deleted') {
            CRM.alert(ts('Something went wrong. Try to reload page.'), ts('Error'), 'Error');
          } else {
            CRM.alert(ts('Something went wrong. Try to reload page. Error message: ') + result.error_message, ts('Error'), 'Error');
          }
        }, function (error) {
          CRM.alert(ts('Something went wrong. Try to reload page.'), ts('Error'), 'Error');
        });
      }
    }
  </script>
{/literal}
{/if}
