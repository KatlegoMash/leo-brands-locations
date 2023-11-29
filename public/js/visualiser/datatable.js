function showConcatUserAgent(data, type, full, meta) {
  return `<span title="${full.user_agent}">${full.user_agent.substring(
    0,
    20
  )}</span>`;
}

Vue.component("datatable-jquery", {
  template: `<div>
                <slot></slot>
                <table style="width: 100%"
                        class="table table-striped table-bordered table-hover"
                        :id="'layer-table-' + layerid"
                      >
                  <thead>
                    <tr>
                      <th>Campaign Id</th>
                      <th>Vicinity Id</th>
                      <th>Location Type</th>
                      <th>Placements</th>
                      <th>Ip Address</th>
                      <th>Latitude</th>
                      <th>Longitude</th>
                      <th>Distance</th>
                      <th>Date Time</th>
                      <th>Layer Type</th>
                      <th>UA String</th>
                      </tr>
                      </thead>
                  </table>
              </div>`,
  props: {
    points: {
      type: Array,
      required: false
    },
    layerid: {
      type: Number,
      required: true
    },
    pushToMapSelection: {
      type: Boolean,
      required: true
    },
    layerToMapSelection: {
      type: Boolean,
      required: true
    }
  },
  methods: {
    addMarkerToLayer(activeLayer, pointInfo) {
      const myCustomColour = activeLayer.colour;

      const markerHtmlStyles = `
                  background-color: ${myCustomColour};
                  width: 3rem;
                  height: 3rem;
                  display: block;
                  left: -1.5rem;
                  top: -1.5rem;z
                  position: relative;
                  border-radius: 3rem 3rem 0;
                  transform: rotate(45deg);
                  border: 1px solid #FFFFFF`;

      const icon = L.divIcon({
        className: `${pointInfo.timestamp}`,
        iconAnchor: [0, 24],
        labelAnchor: [-6, 0],
        popupAnchor: [0, -36],
        html: `<span style="${markerHtmlStyles}" />`
      });

      let info = `
            <strong> Location Type: </strong> ${pointInfo.location_type} <br>
            <strong> Campaign:  </strong>${pointInfo.campaignId} <br>
            <strong> Placement:  </strong>${pointInfo.zoneId} <br>
            <strong> Date Time: </strong> ${pointInfo.date_time} <br>
            <strong> Layer Type: </strong> ${pointInfo.layer_type} <br>
            <div title="Add UserID to filter" style="height: 42px;">
            <strong> User ID: </strong> ${pointInfo.vicinity_id}
              <span style="margin-top: 10px; cursor: pointer" onclick="pushToUserID('${pointInfo.vicinity_id}')">
              <i class="fa fa-clipboard fa-2x"></i>
            </span> <br>
            </div>

          `;

      var marker = L.marker([pointInfo.latitude, pointInfo.longitude], {
        icon: icon,
        info: info,
        draggable: false
      });
      marker.bindPopup(info, { maxWidth: 560 });

      this.tableRowsLayer.push(marker);
    },
    toggleSelectedRows2(val) {
      this.$root.removeLayerPoints();
      for (const key in val) {
        if (val.hasOwnProperty(key)) {
          const element = val[key];
          this.$root.map.removeLayer(element);
        }
      }
      this.tableRowsLayer = [];

      for (let index = 0; index < val.length; index++) {
        this.addMarkerToLayer(this.$root.activeLayerGroup, val[index]);
      }
      this.$root.addCircle(this.$root.activeLayerGroup);
    },
    toggleSelectedRows(val) {
      if (this.$root.activeLayerGroup.pushTomMapSelection) {
        this.$root.removeLayerPoints();
        for (const key in this.tableRowsLayer) {
          if (this.tableRowsLayer.hasOwnProperty(key)) {
            const element = this.tableRowsLayer[key];
            this.$root.map.removeLayer(element);
          }
        }
        this.tableRowsLayer = [];

        for (let index = 0; index < val.length; index++) {
          this.addMarkerToLayer(this.$root.activeLayerGroup, val[index]);
        }

        for (const key in this.tableRowsLayer) {
          if (this.tableRowsLayer.hasOwnProperty(key)) {
            const element = this.tableRowsLayer[key];
            element.addTo(this.$root.map);
          }
        }
        this.$root.addCircle(this.$root.activeLayerGroup);
      }
    },
    toggleDeselectedRows(val) {
      if (this.$root.activeLayerGroup.pushTomMapSelection == false) {
        this.$root.removeLayerPoints();

        for (const key in this.tableRowsLayer) {
          if (this.tableRowsLayer.hasOwnProperty(key)) {
            const element = this.tableRowsLayer[key];
            this.$root.map.removeLayer(element);
          }
        }
        this.tableRowsLayer = [];

        for (let index = 0; index < val.length; index++) {
          this.addMarkerToLayer(this.$root.activeLayerGroup, val[index]);
        }

        for (const key in this.tableRowsLayer) {
          if (this.tableRowsLayer.hasOwnProperty(key)) {
            const element = this.tableRowsLayer[key];
            element.addTo(this.$root.map);
          }
        }
        this.$root.addCircle(this.$root.activeLayerGroup);
      }
    }
  },
  watch: {
    pushToMapSelection: function(val, oldVal) {
      if (val) {
        this.selectedRows = this.dataTable.rows({ selected: true }).data();
        this.toggleSelectedRows(this.selectedRows);
      } else {
        this.deselectedRows = this.dataTable.rows({ selected: false }).data();
        this.toggleDeselectedRows(this.deselectedRows);
      }
    },
    points: function(val, oldVal) {
      this.dataTable.clear().draw();
      this.dataTable.rows.add(this.points);
      this.dataTable.columns.adjust().draw();
    },
    selectedRows: function(val, oldVal) {
      this.toggleSelectedRows(val);
    },
    deselectedRows: function(val, oldVal) {
      this.toggleDeselectedRows(val);
    }
  },
  computed: {
    selectedPoints() {
      if (
        this.selectedRows.length > 0 &&
        this.$root.activeLayerGroup.pushTomMapSelection == false
      ) {
        this.rowData = [];
        for (let index = 0; index < this.selectedRows.length; index++) {}
      }
      return this.rowData;
    },
    deelectedPoints() {
      if (
        this.deselectedRows.length > 0 &&
        this.$root.activeLayerGroup.pushTomMapSelection
      ) {
        this.rowData = [];
        for (let index = 0; index < this.deselectedRows.length; index++) {}
      }
      return this.rowData;
    }
  },
  data() {
    return {
      dataTable: null,
      selectedRows: {},
      data: {},
      rowData: [],
      deselectedRows: {},
      tableRowsLayer: []
    };
  },
  mounted() {
    this.dataTable = $(`#layer-table-${this.layerid}`).DataTable({
      paging: true,
      searching: true,
      search: { regex: true },
      info: true,
      data: this.points,
      lengthMenu: [10, 25, 50, 75, 100],
      columns: [
        { data: "campaignId" },
        { data: "vicinity_id" },
        { data: "location_type" },
        { data: "zoneId" },
        { data: "ip_address" },
        { data: "latitude" },
        { data: "longitude" },
        // { data: "distance", render: stripMeters },@devin
        { data: "distance" },
        { data: "date_time" },
        { data: "layer_type" },
        { data: "user_agent", render: showConcatUserAgent }
      ],
      autoWidth: false,
      columnDefs: [{ "max-width": "10%", targets: 7 }],
      initComplete: function() {
        this.api()
          .columns()
          .every(function() {
            var column = this;
            var select = $('<select><option value=""></option></select>')
              .appendTo($(column.footer()).empty())
              .on("change", function() {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());

                column.search(val ? "^" + val + "$" : "", true, false).draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function(d, j) {
                select.append('<option value="' + d + '">' + d + "</option>");
              });
          });
      },
      select: { style: "multi" },
      dom: "Blfrtip",
      buttons: [
        {
          text: "Select All",
          action: function() {
            table.rows({ search: "applied" }).select();
          }
        },
        "selectNone",
        {
          title: "",
          extend: "excel",
          header: true,
          className: "btn btn-primary btn-export",
          text: "Export",
          exportOptions: {
            format: {
              header: function(data) {
                return data.replace(/ /g, "");
              },
              body: function(data, row, column, node) {
                if (column == 9) {
                  return $(data).attr("title");
                }

                return data;
              }
            }
          }
        }
      ]
    });

    yadcf.init(this.dataTable, [
      {
        column_number: 0,
        filter_type: "multi_select"
      },
      {
        filter_type: "multi_select",
        column_number: 1
      },
      { column_number: 2 },
      {
        column_number: 3,
        filter_type: " ",

        date_format: "YYYY MM DD"
      },
      { column_number: 4, filter_type: "multi_select" },
      { column_number: 5, filter_type: "column_number" },
      { column_number: 6, filter_type: "column_number" },
      { column_number: 7, filter_type: "range_number_slider" },
      { column_number: 8, filter_type: "column_number" },
      { column_number: 9, filter_type: "column_number" },
      { column_number: 10, filter_type: "column_number" }
    ]);

    $(`#layer-table-${this.layerid} thead tr`)
      .clone(true)
      .appendTo(`#layer-table-${this.layerid} thead`);

    var table = this.dataTable;
    $(`#layer-table-${this.layerid} thead tr:eq(1) th`).each(function(i) {
      var title = $(this).text();
      $(this).empty();

      $(this).html(`
      <input type="text" class="form-control" id="filter_value" style="width: 80%;" placeholder="Search ' +  ${title} +'" />`);

      $("input", this).on("keyup change", function() {
        var select = $("select", this).val();

        if (table.column(i).search() !== this.value) {
          table
            .column(i)
            .search(this.value.replace(/\s+/g, "|"), true, false)
            .draw();
        }
      });
    });

    var that = this;
    this.dataTable.on("select", function(e, dt, type, indexes) {
      if (type === "row") {
        that.selectedRows = that.dataTable.rows({ selected: true }).data();
        that.deselectedRows = that.dataTable.rows({ selected: false }).data();
      }
    });
    this.dataTable.on("deselect", function(e, dt, type, indexes) {
      if (type === "row") {
        that.selectedRows = that.dataTable.rows({ selected: true }).data();
        that.deselectedRows = that.dataTable.rows({ selected: false }).data();
      }
    });
  }
});
