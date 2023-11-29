Vue.component("summary-datatable-jquery", {
  template: `<div style="float: left;width: 45%;padding-right: 10px" :id="'table-container-'+layerid">
                <table
                        class="table table-striped table-bordered table-hover"
                        :id="'summary-' + layerid"
                      >
                  <thead>
                    <tr>
                      <th>{{headings[0]}}</th>
                      <th>{{headings[1]}}</th>
                      </tr>
                      </thead>
                  </table>
              </div>`,
  props: {
    points: {
      type: Array,
      required: false
    },
    headings: {
      type: Array,
      required: false
    },
    layerid: {
      type: String,
      required: true
    }
  },
  watch: {
    points: function(val, oldVal) {
      this.dataTable.clear().draw();
      this.dataTable.rows.add(this.points);
      this.dataTable.columns.adjust().draw();
    }
  },
  mounted() {
    this.dataTable = $(`#summary-${this.layerid}`).DataTable({
      paging: true,
      data: this.points,
      searching: false,
      info: true,
      columns: [{ data: "key" }, { data: "total" }]
    });
  }
});
