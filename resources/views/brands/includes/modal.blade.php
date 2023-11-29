<div class="modal fade bs-modal-qr-codes" id="modal-qr-codes" tabindex="-1" role="dialog" aria-labelledby="targettingBreakdownLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
				<button id="qr-codes-close" type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<label id="brandLabel"></label>
				</h4>
				<hr>
			</div>
            <div class="card text-dark bg-light d-flex align-items-center justify-content-center flex-column bd-highlight mb-3 mx-auto" style="max-width: 18rem; text-align: center;">
                <div class="card-header" id="qrCodeFullUrl"></div>
                <div class="card-body">
                  <div class="card-text" id="qrCodeImage"></div>
                  <div class="p-2 bd-highlight" id="qrCodeDownload"></div>
                </div>
              </div>     
            <div class="modal-body">
                <div class="row">
                    <div class="data-table-container">
                        <div class="mb-10 align-items-center justify-content-center">
                            <span class="fa fa-spinner fa-spin fa-2x"></span>
                        </div>
                        <table id="qr-codes">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Short URL</th>
                                    <th>QR Code</th>
                                    <th>Download</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Location</th>
                                    <th>Short URL</th>
                                    <th>QR Code</th>
                                    <th>Download</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <script>
                function generateQrCodes(selector)
                {
                    var url = "{{ action('DigitialCatalogueController@postCampaignLocationsUrls') }}?brand_id="+selector;
                    // var brand_id = selector;
                    $.ajax({
                        url,
                        type : 'POST'
                    }).done(function(response){
                        drawQrCodeForBrandTable(response.brandData.original.brand_id);
                        getQrCodes(response.data.original.data.location_ids);
                    }).error(function(errorMsg){
                        console.log(errorMsg);
                    });
                }

                function getQrCodes(data)
                {
                    var url ="{{ action('DigitialCatalogueController@postGetCampaignLocationsUrls') }}";

                    $.ajax({
                        type: 'POST',
                        url: url,
                        data : { ids : data }

                    }).done(function(response){
                        drawQrCodeTable(JSON.parse(response));
                        
                    });
                }

                function drawQrCodeTable(data)
                {
                    let locname;
                    let selector = '#qr-codes';

                    $(selector).DataTable().destroy();

                    if ($.fn.dataTable.isDataTable(selector)) {
                        $(selector).DataTable();
                    } else {
                        $(selector).DataTable({
                            data,
                            columns: [
                                {
                                    data: "brand_location.locationName",
                                    render: function (data){
                                        locname = data;
                                        return data;
                                    },
                                    className: "p-6",
                                },
                                {
                                    data: "full_url" ,
                                    className: "p-6"
                                },
                                {
                                    data: "qr_code",
                                    render : function(data){
                                        data = '<img src="'+data+'" loading="lazy" alt="QR (Quick Response) Code for campaign location in store deals" width="50px" height="50px">';
                                        return data;
                                    },
                                },
                                {
                                    data : "qr_code",
                                    render:function(data){
                                        data = `<a href="`+data+`" class="btn btn-primary" download><span class="fa fa-download"></span></a>`;
                                        return data;
                                    },
                                    className:"p-6",
                                }
                            ],
                            rowCallback: function() {
                                $('#modal-qr-codes .fa-spin').addClass('hidden');
                            },
                        });
                    }
                }

                function drawQrCodeForBrandTable(data){
                    let {brand, brandQrcode} = data
                    let {status} = brandQrcode

                    $('#brandLabel').append(`<label>${brand.brandName}</label>`)
                    $('#qrCodeFullUrl').append(`<div class="p-6">${status.full_url}</div>`)
                    $('#qrCodeImage').append(`<img src="${status.qr_code}" loading="lazy" alt="QR (Quick Response) Code for campaign location in store deals" width="120px" height="120px">`)
                    $('#qrCodeDownload').append(`<a href="${status.qr_code}" class="btn btn-primary" download>Download<span class="fa fa-download"></span></a>`)
                    $('#modal-qr-codes .fa-spin').addClass('hidden');

                    // clears old data.
                    $('#modal-qr-codes').on('hidden.bs.modal', function () {
                        $('#brandLabel').html('');
                        $('#qrCodeFullUrl').html('');
                        $('#qrCodeImage').html('');
                        $('#qrCodeDownload').html('');    
                        $('#qr-codes').DataTable().clear().draw();
                        $('#brandLabel').append(`<label><span class="fa fa-spinner fa-spin fa-2x"></span></label>`);
                    });   
                };
            </script>
        </div>
    </div>
</div>
