<!-- Brands-Sectors chart corresponding datatable -->
<section class="row p-5 brands-sectors-visits-chart-datatable">
    <article class="col-12 my-5">
        <div class="dt-header-title">
            <h2 class="title">Sector-Brands Visits</h2>
        </div>
        <div id="dt-visits"></div>
    </article>
</section>

<script id="brands-sectors-visits-datatable">
  document.addEventListener('DOMContentLoaded', function(event) {
    console.log(event);
  }, false);

  function fetch_sector_brands(id)
  {
    let url = "{{ action('BrandController@getSectorBrands') }}/?sectorId=" + id;
    
    // let response = $.get(url);
    $.ajax({
      url: url,

      success: function(response) {
        var brandSelector = $('#brandId');
        var brands = response.sector_location_brands
          .linked_locations
          .map((l) => { return l.brand; });

        Array.from(brands).forEach((brand) => {
          var value = brand.id;
          var name = brand.brandName;
          brandSelector.append(`
            <option value="${value}">${name}</option>
          `);
        });

        brandSelector.removeAttr('disabled');
      },

      failed: function(response) {
        return console.warn(response);
      }
    });

    // console.log(response);
  }

  function visitsDatatable(data)
  {
    var selector = '#dt-visits';

    console.log(selector);

    $(selector).DataTable().destroy();

    $(selector).DataTable({
      data,
      // columns: [
      //   {
      //     data: "",
      //     className: "p-3",
      //   },
      //   {
      //     data: "" ,
      //     className: "p-3"
      //   },
      // ]
    });

    console.log(dataTable);
  }
</script>