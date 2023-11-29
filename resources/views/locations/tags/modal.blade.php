{{-- new modal for tagging --}}
<div class="modal fade" id="tagLocations" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="top: 5%;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel">Location Tags</h4>
        </div>
        <div class="modal-body">
            <div id="name-tags" class="form-group specific-fields banner vast-tag-url">
                <label for="campaignTags">Location Tags</label>
                <div class="alert alert-danger" v-if="message">@{{message}}</div>
                <vue-tags-input
                    v-model="tag"
                    :tags="tags"
                    :autocomplete-items="filteredTags"
                    :allow-edit-tags="allowEditTags"
                    :save-on-key="acceptKeys"
                    :add-on-key="acceptKeys"
                    :separators="acceptSeparators"
                    @tags-changed="updateTags"
                />
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" id="closeTagLocations" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
</div>