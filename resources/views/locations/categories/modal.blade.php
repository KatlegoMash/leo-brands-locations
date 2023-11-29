<style>

    #linkModalDialog{
      max-width: 90%;
    }
    #linkModalContent {
      height: auto;
      min-height: 100%;
      border-radius: 0;
    }

</style>

<div class="modal fade" id="linkCategoriesModal" tabindex="-1" role="dialog" aria-labelledby="linkCategoriesModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document" id="linkModalDialog">
    <div class="modal-content" id="linkModalContent">
      <div class="modal-header">
        <h3 class="modal-title" id="categoriesTitle">Widget Categories</h3>
        <button type="button" id="closeCategory" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">

          <div class="col-md-6">
            <div class="panel panel-default">

              <div class="panel-body">
                <div class="col-sm-3">
                  <h4>Linking Actions:</h4>
                </div>
                <div class="col-sm-9">
                  <button type="button" class="btn btn-success" id="linking">Link Categories</button>
                  <button type="button" class="btn btn-danger" id="unlinking">Un-Link Categories</button>
                  <button type="button" class="btn btn-warning" data-bs-dismiss="modal" id="closeModal">Close</button>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="panel panel-default">
              <div class="panel-body">
                <div class="col-sm-12">
                    <h4>Number of linked categories to brand locations:<span id="linkedInfo">0</span></h4>

                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
      <div class="modal-body" >
          <div class="row">


            <div class="col-sm-7">
              <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3>Categories</h3>
                  </div>
                  <div class="panel-body">
                    <div id="selectCategories">
                    <table id="categoriesSelected" class="display table table-striped table-bordered table-hover dataTable no-footer" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <th></th>
                          <th>Category Name</th>
                          <th>Icons</th>
                          <th>Category Geofence</th>
                        </tr>
                      </thead>
                      <tbody>
                          @foreach($categories as $category)
                            <tr>
                                <td>{{$category->id}}</td>
                                <td>{{$category->categoriesName}}</td>
                                <td>
                                    <div style="width: 60px;height: 60px;">
                                        @include('/template_pins/templates/template91/icon-'.$category->id,array($distance="",$type="dropdown"))
                                    </div>
                                </td>
                                <td>
                                    <form id="update-category" action="/update-category" method="POST">
                                      <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                      <input type="hidden" name="id" value={{$category->id}}>
                                      <input type="text" name="geofence" placeholder="{{$category->geofence}}">
                                      <input type="submit">
                                    </form>
                                </td>
                            </tr>
                           @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-sm-5">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h3>Linked Locations with Categories</h3>
                </div>
                <div class="panel-body">
                  <div id="linkedCategories">
                     @foreach($categories as $category)
                          <h3>{{$category->categoriesName}} (<span id="category-counter-{{$category->id}}">0</span>)</h3>
                          <div>
                            <p id="category-content-{{$category->id}}">

                            </p>
                          </div>
                      @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>
      </div>

    </div>
  </div>
</div>
