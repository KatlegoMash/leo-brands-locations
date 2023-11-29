

Vue.component("Expander", {
    template: `
<div class="Expander">
    <div class="Expander__trigger_container">
        <div class="Expander__trigger"
          :class="open?'active':'beforeBorder'">
            <span  @click="open=!open">
                <svg
                class="Expander__trigger-Icon"
                :class="{open:open}"
                width="40" height="12"
                stroke="cornflowerblue">
                    <polyline points="12,2 20,10 28,2" stroke-width="3" fill="none"></polyline>
                </svg>
            </span>

            <input type="checkbox" class="cutomize-checkbox-styled" v-model="settings.using"/>
            <span @click="open=!open">{{settings.name}}</span>
            <label style="color: red;" v-if="settings.id == 5 || settings.id == 7 || settings.id == 8">- Unreleased</label>

            <p class="pull-right">
                <span style="padding: 0 4px; border-right: 1px solid;">Delivery Ratio {{dataStats.deliveryRatio}}</span>
                <span style="padding: 0 4px; border-right: 1px solid;">Served Impressions {{formatNumber(dataStats.impressions)}}</span>
                <span style="padding: 0 4px;">CTR {{ctrCalculation(dataStats.clicks, dataStats.impressions)}}</span>
            </p>
        </div>

    </div>
    <transition :name="animation">
        <div class="Expander__body" v-show="open">
            <slot></slot>
        </div>
    </transition>
</div>
  `,
    props: {
        animation: {
            type: String,
            default: 'rightToLeft'
        },
        settings: {
            type: Object,
            default: function () {
                return {}
            }
        },
        stats: {
            default: function () {
                return {}
            }
        }
    },
    methods: {
        formatNumber(value) {
            if (value > 0) {
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            } else {
                return 0;
            }
        },
        ctrCalculation(clicks, impressions) {
            ctr = 0;
            if (clicks > 0 && impressions > 0) {
                ctr = (clicks / impressions * 100).toFixed(2);
            }
            return ctr + '%';
        },
        deliveryRatioCalculation(data) {

            var deliveryRatio = 0;
            var count = 0;
            var minus = 0
            for (var i = 0; i < this.$root.settings.length; i++) {
                if (this.$root.settings[i].using == true) {
                    count++;
                } else {
                    if (this.stats.totals[this.$root.settings[i].id].impressions > 0) minus += parseInt(this.stats.totals[this.$root.settings[i].id].impressions)
                }
            }

            if (count > 0 && this.$root.campaign.targetquantity > 0) {
                deliveryRatio = ((data.impressions / ((this.$root.campaign.targetquantity - minus) / count)) * 100) / count
                deliveryRatio = deliveryRatio.toFixed(1)
            }

            return deliveryRatio;
        },

    },
    computed: {
        dataStats() {
            if (!this.stats.totals) return {}
            temp = 0
            var keys = Object.keys(this.stats.totals);

            if (keys.length == 0) return {}

            for (var i = 0; i < keys.length; i++) {
                temp += parseInt(this.stats.totals[keys[i]].impressions)
            }
            this.$root.totalDeliveryRatio = Math.ceil(temp / this.$root.campaign.quantity * 100);

            for (key in this.stats.data) {
                if (this.stats.data[key].targetId == this.settings.id) {
                    this.stats.data[key].deliveryRatio = this.deliveryRatioCalculation(this.stats.totals[key]);
                    this.stats.data[key].deliveryRatio += '%';

                    return this.stats.data[key];
                }
            }
            return {}
        }
    },
    data() {
        return {
            open: false
        }
    }
});

Vue.component("presets-save", {
    template: `
    <div style="width: 100%">
        <div class="row">
            <div class="col-lg-12">
                <div  ref="select"  class="input-group">
                <input type="text" v-model="presetName"   class="form-control mw72" placeholder="Preset name">
                <span class="input-group-btn">
                    <button  @click="confirmApplyPreset()"  class="btn btn-success" type="button" >Save As New Preset</button>
                    <button  @click="savePreset('update')"  class="btn btn-warning" type="button" >Update Current Preset</button>
                </span>
                </div>
            </div>
        </div>
    </div>
    `,
    mounted() {
        window.addEventListener("click", () => {
            this.$refs.select.blur();
        });



    },
    data() {
        return {
            presetName: ""
        }
    },
    props: [
        "type",
        "target",
        "settings"
    ],
    methods: {
        savePreset(is_update) {
            let update_id = null;
            if(is_update == 'update'){
                let update_id = $("#targeting-tab-select-presets").val();
                let name = $("#targeting-tab-select-presets option:selected").text();

                if(confirm(`Are you sure you want to update the following preset: \n${name}`)){
                    this.type = 'parent';
                    this.presetName = name;
                    setTimeout(()=>{
                        $("#targeting-tab-select-presets").change();
                        alert('Campaign and Preset Update!');
                    },2000);
                }else{
                    return false;
                }
            }

            switch (this.type) {
                case 'global':
                    this.sendPresetData('global', this.$root.global)
                    break;
                case 'options':
                    this.sendPresetData('options', this.target)
                    break;
                case 'parent':
                    data = {
                        global: this.$root.global,
                        settings: []
                    }
                    for (var i = 0; i < this.settings.length; i++) {
                        data.settings.push({
                            data: this.settings[i].data,
                            id: this.settings[i].id,
                            using: this.settings[i].using
                        });
                    }

                    this.sendPresetData('parent', {
                        parentData: data
                    })
                    break;
            }


        },
        confirmApplyPreset() {

            if (this.presetName.trim() == "") {
                swal("Preset name cannot be empty")
                return false;
            }
            this.presetName = this.presetName.trim();

            if (this.$root.presets.find(preset => preset.name == this.presetName && preset.type == this.type)) {
                swal("A preset with this name already exists")
                return false;
            }

            swal({
                    title: `Save Preset ?`,
                    text: "",
                    icon: "info",
                    buttons: true,
                    info: "info",
                    dangerMode: false,
                })
                .then((willApply) => {
                    if (willApply) {
                        this.savePreset()
                        swal(`Preset Saved`, {
                            icon: "success",
                        });
                    }
                });
        },
        sendPresetData(type, data) {
            this.$http.post("/targetting/save-presets", {
                data: data,
                name: this.presetName,
                settings: this.settings,
                type: type
            }).then((data) => {
                this.$root.presets.push(data.body)
            });
        }
    }
})

Vue.component("preset-select", {
    name: 'preset-select',
    template: `
    <v-select
        v-model="selectedPreset"
        placeholder="Select a preset"
        :value="selectedPreset"
        label="name"
        :closeOnSelect="true"
        :options="presets"
        @input="setSelected"
    ></v-select>
    `,
    props: ['type', 'values', 'settings'],
    data() {
        return {
            selectedPreset: {},
            selectedPresetID: 0,
            presetSettings: null,
            exists: []
        }
    },

    methods: {
        setSelected(value) {
            this.selectedPreset = value
            this.selectedPresetID = value.id || 0
            this.confirmApplyPreset();
        },
        applyPreset() {
            if (this.selectedPresetID != 0) {
                if (this.isParentPreset) {
                    var settings = JSON.parse(this.selectedPreset.settings);

                    for (var i = 0; i < this.values.length; i++) {
                        for (key in this.values[i]) {
                            data = settings.parentData.settings.find(setting => setting.id == this.values[i].id);
                            for (key in data) {
                                this.values[i][key] = data[key];
                            }
                        }
                    }

                    this.$root.global = settings.parentData.global;

                    this.saveSelectedPreset(this.selectedPreset)

                } else {
                    this.presetSettings = JSON.parse(this.selectedPreset.settings);

                    for (key in this.presetSettings) {
                        this.values[key] = this.presetSettings[key];
                    }
                    this.values.name = this.selectedPreset.name;
                }
            }
        },
        saveSelectedPreset(selectedPreset) {
            var url = "/targetting/update-campaign-presets/"

            this.$http.post(url, {
                campaignID: this.$root.campaign.campaignId,
                presetID: selectedPreset.id,
                create_yn: 'create'
            }).then(function (res) {});

            this.$root.selectedPreset = selectedPreset

        },
        confirmApplyPreset() {

            if (this.isParentPreset) {

                if (this.hasTargetingRule) {
                    //swal("please remove all targeting rules before selecting a new preset")
                    alert("please remove all targeting rules before selecting a new preset")
                    return
                }
            }

            var prompt = false;
            var prompt = confirm(`Apply Preset ${this.selectedPreset.name} ?\nThis will override the existing settings`)

            if (prompt == true) {
                this.applyPreset()
            } else {
                return
            }

/*             swal({
                    title: `Apply Preset ${this.selectedPreset.name} ?`,
                    text: "This will override the existing settings",
                    icon: "info",
                    buttons: true,
                    dangerMode: false,
                })
                .then((willApply) => {
                    if (willApply) {
                        this.applyPreset()
                        swal(`Preset: ${this.selectedPreset.name} has been applied`, {
                            icon: "success",
                        });
                    }
                }); */
        },
    },
    computed: {
        hasTargetingRule() {
            var exists = this.$root.settings.find(setting => setting.using == true);
            if (exists) {
                return true
            }
            return false
        },
        isParentPreset() {
            return this.type == 'parent'
        },
        presets() {
            const presetArray = Object.values(this.$root.presets);
            return presetArray.filter(preset => preset.type == this.type)
        }
    }
});

Vue.component("category-viewer", {
    template: "#category-template",
    props: ["target", "title", "settings"],
    data: function () {
        return {
            subs: [],
            subsLabel: [],
            done: false,
            items: {},
            parentName: "",
            campaignSearch: false,
            search: ""
        }
    },

    computed: {
        searchMain: function () {
            var search = this.search.trim().split(" ");
            var items = [];
            if (search != "") {
                if ("categories" == this.title) {
                    var data = categoryApp.allData.data.indexed;
                } else if ("poi" == this.title) {
                    var data = categoryApp.allData.data.poi_map;
                } else {
                    var data = categoryApp.allData.data[this.title];
                }

                if (this.settings.id == 6) {
                    var temp;
                    temp = categoryApp.allData.data.visits_map;
                    temp = categoryApp.allData.data.locations_map;
                    for (key in temp) {
                        data[key] = temp[key]
                    }
                }
                if (this.settings.id == 7) {
                    var data = categoryApp.allData.data.device_content_indexed;
                }
                if (this.settings.id == 4) {
                    var data = categoryApp.allData.data.audience_content_indexed;
                }
                if (this.settings.id == 8) {
                  var data = categoryApp.allData.data.website_content_indexed;
                }
                if (this.settings.id == 9) {
                    var data = categoryApp.allData.data.nearme_content_indexed;
                }
                if (this.settings.id == 10) {
                    var data = categoryApp.allData.data.isp_content_indexed;
                }

                for (key in data) {
                    found = 0;
                    for (var i = 0; i < search.length; i++) {
                        if (data[key].name.toLowerCase().indexOf(search[i].toLowerCase()) !== -1) {
                            found++;
                        }
                    }
                    if (search.length == found) {
                        items.push(data[key].id);
                    }
                }
            }

            return items;
        },
        ready: function () {
            if (categoryApp.allData.data) {
                this.setData();
                return true;
            } else {
                return false;
            }
        },
        totalSelected: function () {
            total = 0;
            if (this.settings.data.selected) {
                for (key in this.settings.data.selected) {
                    total += this.settings.data.selected[key].length;
                }
            }
            return total;
        },
        totalLength: function () {
            if (this.target) {
                return this.target.selected.length;
            }
            return 0
        },
        selectAll: function () {}
    },
    methods: {
        orderBy: function (obj, name) {
            var items = [];



            for (key in obj) {

                if (!obj[key].name || !Number.isInteger(Number(key))) {
                    continue;
                }
                obj[key].name = obj[key].name.trim();
                items.push(obj[key]);
            }
            if (this.parentName != "visits_1" && this.parentName != "visits_2") {
                items.sort(function (a, b) {
                    if (a.name && b.name) {
                        return -1
                    }
                    var nameA = a.name.toUpperCase();
                    var nameB = b.name.toUpperCase(); // ignore upper and lowercase
                    if (nameA < nameB) {
                        return -1;
                    }
                    if (nameA > nameB) {
                        return 1;
                    }
                    return 0;
                });
            }
            return items;
        },
        hasErrors: function () {
            if (this.search != "") {
                if (this.validate()) return true
                if (this.validate('visits')) return true
                if (this.validate('location')) return true
            }
            return false
        },
        validate: function (type) {
            var missingFields = []
            var visits = provinces = cities = 0;
            var selectedData = []

            for (var i = 0; i < this.target.selected.length; i++) {
                selectedData.push(String(this.target.selected[i]))
            }

            for (var i = 0; i < selectedData.length; i++) {

                if (selectedData[i].indexOf('visits') >= 0) {
                    visits++;
                }

                if (selectedData[i].indexOf('provinceId') >= 0) {
                    provinces++;
                }

                if (selectedData[i].indexOf('cityId') >= 0) {
                    cities++;
                }
            }
            if (this.settings.id == 6) {
                if (type == "visits" && visits == 0) {
                    return true;
                }

                if (type == "location" && provinces == 0 && cities == 0) {
                    return true;
                }

                if (!type && (selectedData.length - visits - provinces - cities) == 0) {
                    return true;
                }
            } else {
                if (!type && (selectedData.length) == 0) {
                    return true;
                }
            }


            return false;
        },
        totalSubs: function (subs) {
            if (typeof subs == "object" || typeof subs == "Object") {
                return Object.keys(subs).length
            } else {
                return subs.length
            }
        },
        populateSub: function (id) {
            this.subs = [];
            this.parent = id;
            this.subsLabels = [];

            if (this.items.data) {
                this.subsLabels.push({
                    id: id,
                    label: this.items.data[id].name
                });


                if (this.title == "brands" || this.title == "campaigns") {

                    if (id == "visits_1" || id == "visits_2" || id == "countryId_237") {} else {
                        for (key in this.items.data[id].sub) {
                            var item = categoryApp.allData.data[this.title][this.items.data[id].sub[key]];
                            if (item) {

                                item.sub = null
                                this.subs.push(item);
                            }
                        }
                    }
                    if (this.settings.id == 6) {
                        var items = {}

                        for (key in this.subs) {
                            if (typeof this.subs[key] == "Object" || typeof this.subs[key] == "object") {
                                items[key] = this.subs[key]
                            }
                        }
                        this.subs = {}
                        this.subs = items
                    }
                } else {
                    this.subs = this.items.data[id].sub;
                }
            }

        },
        setData: function () {
            type = this.title;
            this.parentName = "";
            if (this.title == "campaigns") {
                type = "brands";
                this.parentName = "Campaigns";
            }

            if (this.title == "brands") {
                type = "agencies";
                this.parentName = "Brands";
            }

            if (this.title == "categories") {
                this.parentName = "Content";
            }

            if (this.title == "locations") {
                this.parentName = "Country";
            }
            if (this.title == "visits") {
                this.parentName = "Visits";
            }
            if (this.title == "poi") {
                this.parentName = "Vicinity POI";
            }

            var data = {}
            if (this.settings.id == 6) {
                const temp = JSON.parse(JSON.stringify(categoryApp.allData.data));
                data = temp;

                let brands = {};
                let parent = {}
                for(let key in data.brands){
                    if(data.brands[key].visits_yn == 1){
                        brands[key] = data.brands[key];
                        if(typeof parent[data.brands[key].parent_id] == 'undefined'){
                            parent[data.brands[key].parent_id] = data.agencies[data.brands[key].parent_id]
                            parent[data.brands[key].parent_id].sub = [];
                        }
                        parent[data.brands[key].parent_id].sub.push(key)
                    }
                }
                data.brands = brands;
                data.agencies = parent;
                categoryApp.allData.data.brands = brands;
                categoryApp.allData.data.agencies = parent;
            }

            if (this.settings.id == 7) {
                type = "device_content_categories";
                this.parentName = "Categories";
            }
            if (this.settings.id == 4) {
                type = "audience_content_categories";
                this.parentName = "Audience";
            }
            if (this.settings.id == 8) {
                type = "website_content_categories";
                this.parentName = "Websites";
            }
            if (this.settings.id == 9) {
                type = "nearme_content_categories";
                this.parentName = "Categories";
            }
            if (this.settings.id == 10) {
                type = "isp_content_categories";
                this.parentName = "Categories";
            }

            this.items.data = categoryApp.allData.data[type];
            var temp2 = categoryApp.allData.data[type];
            for (key in temp2) {
                data[key] = temp2[key]
            }
            this.items.data = data;

            this.subs = [];
            this.subsLabels = [];
        },
        ToggleCampaigns: function () {
            if (this.campaignSearch == true) {
                this.items.data = categoryApp.allData.data.camapigns;
                this.items.map = categoryApp.allData.data.camapignsIndexed;
                this.populateSub(8);
            } else {
                this.items.data = categoryApp.allData.data.categories;
                this.items.map = categoryApp.allData.data.indexed;
                this.populateSub(1);
            }
        },
        clearSelected: function () {
            this.target.selected = [];
        },
        breadBuilder: function (id) {
            item = this.subsLabels[0];
            this.subsLabels = [];
            this.subsLabels.push(item);
            this.subsLabels.push({
                id: id,
                label: this.items.data[this.parent].sub[id].name
            });

            this.subs = [];
            this.subs = this.items.data[this.parent].sub[id].sub;
        }
    }
})

Vue.component('v-select', VueSelect.VueSelect);

Vue.component('vue-multiselect', window.VueMultiselect.default);


Vue.component("targetting", {
    template: "#targetting-template",
    props: ['settings', 'campaign', 'global'],
    computed: {
        TargetingbudgetCalculation: function () {
            this.globalBudget = this.campaign.budget * this.global.budgetrange / 100
            this.targetingBudget = this.globalBudget * this.settings.budgetrange / 100;
            return this.targetingBudget;
        },
    },
    methods: {
        isNamePopulated: function (target) {
            if (target.type) {
                if (target.type && target.name != '') {
                    $("#accordion-visit-targeting").accordion({
                        collapsible: true
                    });
                    return true
                }
            }
            return false
        },
        addTargetRule: function () {
            const data = JSON.parse(JSON.stringify(this.settings.defaultData));
            this.settings.data.push(data);
            $('.selectpicker').selectpicker('refresh')
        },
        deleteTargetRule: function (index) {
            this.settings.data[index].deleted = true;
        },
        formatPrice(value) {
            if (value > 0) {
                let val = (value / 1).toFixed(2).replace(',', '.')
                this.price = val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            } else {
                this.price = 0;
            }
            return this.price;
        }

    }
});

Vue.component("global-settings", {
    template: "#global-template",
    props: ['global', 'campaign'],
    computed: {
        budgetCalculation: function () {
            return this.campaign.budget * this.global.budgetrange / 100;
        },
        quantityCalculationFormated: function () {
            quantity = (this.quantityCalculation()).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            return quantity + " " + this.campaign.metric;
        }
    },
    methods: {
        quantityCalculation: function () {
            budget = this.campaign.budget * this.global.budgetrange / 100;
            quantity = 0;
            if (this.campaign.metric == "CPC" || this.campaign.metric == "CPA" || this.campaign.metric == "CPV") {
                quantity = Math.ceil(budget / this.campaign.rate);
            } else {
                if (this.campaign.metric == "CPM") {
                    quantity = Math.ceil((budget / this.campaign.rate) * 1000);
                }
            }
            this.$root.campaign.targetquantity = quantity;
            return quantity;
        },
        formatPrice(value) {
            let val = (value / 1).toFixed(2).replace(',', '.')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
        }
    },
    mounted() {
    //     setTimeout(() => {
    //         $.fn.bootstrapSwitch.defaults.onColor = 'primary';
    //         $.fn.bootstrapSwitch.defaults.offColor = 'success';
    //         $.fn.bootstrapSwitch.defaults.onText = 'Yes';
    //         $.fn.bootstrapSwitch.defaults.offText = 'No';
    //         $("#global_placement_yn").bootstrapSwitch();
    //
    //
    //         $.fn.bootstrapSwitch.defaults.onColor = 'primary';
    //         $.fn.bootstrapSwitch.defaults.offColor = 'success';
    //         $.fn.bootstrapSwitch.defaults.onText = 'Yes';
    //         $.fn.bootstrapSwitch.defaults.offText = 'No';
    //         $("#geofence_yn").bootstrapSwitch();
    //
    //
    // }, 4000);
    }
})

Vue.component("sub-category-viewer", {
    template: "#sub-category-viewer-template",
    props: ["lists", "title", "listSelected", 'settings'],
    data: function () {
        return {
            selected: {}
        }
    },
    watch: {
        lists: function () {
            this.arrange();
        }
    },

    methods: {
        selectAll: function (type) {
            if (!type) type = 'select'

            for (var i = 0; i < this.lists.length; i++) {
                if (type == 'select') {
                    if (this.listSelected.selected.indexOf(this.lists[i]) == -1) {
                        this.listSelected.selected.push(this.lists[i]);
                    }
                } else {
                    var index = this.listSelected.selected.indexOf(this.lists[i]);
                    if (index > -1) {
                        this.listSelected.selected.splice(index, 1);
                    }
                }
            }
        },

        orderBy: function (obj, name) {
            var items = [];

            for (key in obj) {
                obj[key].name = obj[key].name.trim();
                items.push(obj[key]);
            }
            if (this.parent != "visits_1" && this.parent != "visits_2") {
                items.sort(function (a, b) {
                    var nameA = a.name.toUpperCase();
                    var nameB = b.name.toUpperCase(); // ignore upper and lowercase
                    if (nameA < nameB) {
                        return -1;
                    }
                    if (nameA > nameB) {
                        return 1;
                    }
                    return 0;
                });
            }
            return items;
        },
        parentName: function (id) {
            if (this.title == "campaigns" || this.title == "brands") {
                return categoryApp.allData.data[this.title][id].name
            } else {
                parent_id = categoryApp.categories.map[id].parent_id;
                return categoryApp.categories.map[parent_id].name + " --> " + categoryApp.categories.map[id].name;
            }
        },
        arrange: function () {

            var items = {};

            var lists = this.lists
            var selected = lists;
            var type = this.title;
            var data1 = {};
            var data2 = {};

            if (this.title == "campaigns") {
                var data1 = categoryApp.allData.data["brands"];
                var data2 = categoryApp.allData.data["campaigns"];
                var items = {};
            }

            if (this.title == "brands") {
                var data1 = categoryApp.allData.data["agencies"];
                var data2 = categoryApp.allData.data["brands"];
                var items = {};
            }

            if (this.title == "categories") {
                var data1 = Object.assign({}, categoryApp.categories.map);
                var data2 = Object.assign({}, categoryApp.categories.map);
                var items = {};
            }

            if (this.title == "poi") {
                var data1 = Object.assign({}, categoryApp.allData.data.poi_map);
                var data2 = Object.assign({}, categoryApp.allData.data.poi_map);
                var items = {};
            }

            if (this.settings.id == 6) {
                var temp = Object.assign({}, categoryApp.allData.data.locations_map);
                for (key in temp) {
                    data1[key] = temp[key]
                    data2[key] = temp[key]
                }
                var items = {};
            }
            if (this.settings.id == 7) {
                var data1 = Object.assign({}, categoryApp.allData.data.device_content_indexed);
                var data2 = Object.assign({}, categoryApp.allData.data.device_content_indexed);
                var items = {};
            }
            if (this.settings.id == 4) {
                var data1 = Object.assign({}, categoryApp.allData.data.audience_content_indexed);
                var data2 = Object.assign({}, categoryApp.allData.data.audience_content_indexed);
                var items = {};
            }
            if (this.settings.id == 8) {
                var data1 = Object.assign({}, categoryApp.allData.data.website_content_indexed);
                var data2 = Object.assign({}, categoryApp.allData.data.website_content_indexed);
                var items = {};
            }
            if (this.settings.id == 9) {
                var data1 = Object.assign({}, categoryApp.allData.data.nearme_content_indexed);
                var data2 = Object.assign({}, categoryApp.allData.data.nearme_content_indexed);
                var items = {};
            }
            if (this.settings.id == 10) {
                var data1 = Object.assign({}, categoryApp.allData.data.isp_content_indexed);
                var data2 = Object.assign({}, categoryApp.allData.data.isp_content_indexed);
                var items = {};
            }

            var item = {
                main: [],
                children: []
            };

            for (var i = 0; i < selected.length; i++) {
                var item = {
                    main: [],
                    children: []
                };
                guy = data2[selected[i]];
                if (guy) {
                    if (guy.parent_id) {
                        if (item.main.indexOf(data1[guy.parent_id].name) == -1)
                            item.main.push(data1[guy.parent_id].name);

                        if (data1[guy.parent_id].parent_id && data1[data1[guy.parent_id].parent_id] && item.main.indexOf(data1[data1[guy.parent_id].parent_id].name) == -1) {
                            item.main.push(data1[data1[guy.parent_id].parent_id].name);
                        }


                        if (!items[guy.parent_id]) {
                            items[guy.parent_id] = item;
                        }
                    }
                }
            }
            // if (guy) {

            for (var i = 0; i < selected.length; i++) {
                if (selected[i]) {

                    guy = data2[selected[i]];

                    if (guy) {

                        if (typeof guy == "undefined" || !items[guy.parent_id]) {
                            continue;
                        }
                        items[guy.parent_id].children.push(guy);
                    }
                }
            }
            if (this.title == "campaigns") {
                for (key in items) {
                    items[key].main = items[key].main[0];
                }
                this.selected = items;
            } else {
                for (key in items) {
                    items[key].main = items[key].main.reverse().join(" -> ");
                }
                this.selected = items;
            }
            // }


        },
        clearSelected: function () {
            this.listSelected.selected = [];
        },
    },
    mounted() {
        this.arrange();
    }
});

Vue.component("location-select", {
    template: `
        <vue-multiselect
            ref="targetingtype"
            v-model="global.targetingtype"
            value="value"
            :options="options"
            :multiple="true"
        ></vue-multiselect>
    `,
    props: ['global'],
    data: () => ({
        value: [],
        options: [
            'shared',
            'fine',
            'wifi',
            'geoip',
            'Wifi - Micro',
            'Wifi - Small',
            'Wifi - Public'
        ]
    }),
    watch: {
        range(value) {
            this.$emit("change", value);
            this.$emit("input", value);
        }
    },

});

Vue.component("date-range-picker", {
    template: `
        <div style="padding-left: 110px;">
            <label>Date Range: </label>
            <input style="border-radius: 4px;width:70%;" :placeholder="startToCurrent" type="text" :class="className" v-model="range"/>
        </div>
    `,
    props: {
        value: {},
        options: {
            type: Object,
            default: function () {
                return {};
            }
        },
        format: {
            type: String,
            default: "YYYY/MM/DD"
        },
        className: {
            type: String,
            default: ""
        },

    },
    data: () => ({
        range: [],
        startDate: 0,
        endDate: 0,
    }),
    computed: {
        isSingleDatePicker() {
            return this.options.singleDatePicker;
        },
        startToCurrent() {
            if (this.$root.campaign) {
                current = moment().format(this.format);
                start = this.$root.campaign.startDate;
                start = moment(start).format(this.format);
                return start + " - " + current;
            }
        },
        customOptions() {
            return {
                locale: {
                    format: this.format,
                },
                ranges: { //default value for ranges object (if you set this to false ranges will no be rendered)
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'This month': [moment().startOf('month'), moment().endOf('month')],
                    'This year': [moment().startOf('year'), moment().endOf('year')],
                    'Last week': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
                    'Last month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Start date to Current day': [moment().subtract(20, 'year').startOf('month'), moment()]
                },
                alwaysShowCalendars: true,
                ...this.options
            };
        },

    },
    created() {
        var tracker = setTimeout(function () {
            if (this.$root.campaign && Object.keys(this.$root.campaign).length) {
                current = moment().format('YYYY-MM-DD');
                this.startDate = '2014-01-01';
                this.endDate = current;
                this.dateSelected();

                clearInterval(tracker);
            }

        }.bind(this), 7000);
    },
    watch: {
        value(value) {
            this.range = value;
        },
        range(value) {
            this.dateSelected();
            this.$emit("change", value);
            this.$emit("input", value);
        }
    },
    methods: {
        formatNumber(value) {
            if (value > 0) {
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            } else {
                return 0;
            }
        },
        addTargetingStats() {
            var empty = 0;
            let list = [];
            let names = [];

            $.each(this.$root.$children[0].settings, function (key, value) {
                names[value.id] = value.name;
            });

            $.each(this.$root.targetingStats.totals, function (key, value) {
                var nameTooAdd = names[value.targetId];
                nameTooAdd = nameTooAdd.replace('Targeting', '');
                nameTooAdd = nameTooAdd.replace('Retargeting', '');
                value.name = nameTooAdd;
                list.push(value);
            });

            var chart1Data = [];
            var chart2Data = [];
            var colorArr = ["#84ceef", "#066287", "#ed7d18", "#9ed0ea", "#0f4f6e", "#faaf40", "#5cd3f2", "#073049", "#e38528", "#5cd3f2", "#082132"];
            let i = 0;

            list.forEach(function(item) {
                if (parseInt(item.impressions) > 0) {
                    let temp = {
                        "value": parseInt(item.impressions),
                        "category": item.name,
                    }
                    chart2Data.push(temp);
                }
                temp = {
                    "value": parseFloat(item.CTR),
                    "full": 30,
                    "category": item.name,
                    "columnSettings": {
                        "fill": colorArr[i]
                    }
                }
                chart1Data.push(temp);
                i++;
            })

            am5.ready(function() {

                // Create root element
                var root = am5.Root.new("chartdivTargetting");

                // Set themes
                root.setThemes([
                am5themes_Animated.new(root)
                ]);

                // Create chart
                var chart = root.container.children.push(am5radar.RadarChart.new(root, {
                panX: false,
                panY: false,
                wheelX: "panX",
                wheelY: "zoomX",
                innerRadius: am5.percent(20),
                startAngle: -90,
                endAngle: 180
                }));


                // Data
                var data = chart1Data;

                // Add cursor
                var cursor = chart.set("cursor", am5radar.RadarCursor.new(root, {
                behavior: "zoomX"
                }));

                cursor.lineY.set("visible", false);

                // Create axes and their renderers
                var xRenderer = am5radar.AxisRendererCircular.new(root, {
                //minGridDistance: 50
                });

                xRenderer.labels.template.setAll({
                radius: 10
                });

                xRenderer.grid.template.setAll({
                forceHidden: true
                });

                var xAxis = chart.xAxes.push(am5xy.ValueAxis.new(root, {
                renderer: xRenderer,
                min: 0,
                max: 2,
                strictMinMax: true,
                numberFormat: "#'%'",
                tooltip: am5.Tooltip.new(root, {})
                }));


                var yRenderer = am5radar.AxisRendererRadial.new(root, {
                minGridDistance: 20
                });

                yRenderer.labels.template.setAll({
                centerX: am5.p100,
                fontWeight: "500",
                fontSize: 18,
                templateField: "columnSettings"
                });

                yRenderer.grid.template.setAll({
                forceHidden: true
                });

                var yAxis = chart.yAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "category",
                renderer: yRenderer
                }));

                yAxis.data.setAll(data);


                // Create series
                var series1 = chart.series.push(am5radar.RadarColumnSeries.new(root, {
                xAxis: xAxis,
                yAxis: yAxis,
                clustered: false,
                valueXField: "full",
                categoryYField: "category",
                fill: root.interfaceColors.get("alternativeBackground")
                }));

                series1.columns.template.setAll({
                width: am5.p100,
                fillOpacity: 0.08,
                strokeOpacity: 0,
                cornerRadius: 20
                });

                series1.data.setAll(data);


                var series2 = chart.series.push(am5radar.RadarColumnSeries.new(root, {
                xAxis: xAxis,
                yAxis: yAxis,
                clustered: false,
                valueXField: "value",
                categoryYField: "category"
                }));

                series2.columns.template.setAll({
                width: am5.p100,
                strokeOpacity: 0,
                tooltipText: "{category}: {valueX}%",
                cornerRadius: 20,
                templateField: "columnSettings"
                });

                series2.data.setAll(data);

                // Animate chart and series in
                series1.appear(1000);
                series2.appear(1000);
                chart.appear(1000, 100); //end guage graph

                //<--------------------------------------------------------start pie graph------------------------------------------------->

                var root2 = am5.Root.new("chartdivTargettingBar");

                // Set themes
                root2.setThemes([
                  am5themes_Animated.new(root2)
                ]);


                // Create chart
                var chart = root2.container.children.push(am5percent.PieChart.new(root2, {
                  radius: am5.percent(60),
                  layout: root2.verticalLayout
                }));


                // Create series
                var series = chart.series.push(am5percent.PieSeries.new(root2, {
                  valueField: "value",
                  categoryField: "category",
                  alignLabels: false
                }));

                series.get("colors").set("colors", [
                    am5.color(colorArr[0]),
                    am5.color(colorArr[1]),
                    am5.color(colorArr[2]),
                    am5.color(colorArr[3]),
                    am5.color(colorArr[4]),
                    am5.color(colorArr[5]),
                    am5.color(colorArr[6]),
                    am5.color(colorArr[7]),
                    am5.color(colorArr[8]),
                    am5.color(colorArr[9]),
                ]);

                // Set data
                series.data.setAll(chart2Data);


                series.labels.template.setAll({
                    fontSize: 12,
                    text: "{category}",
                    textType: "radial",
                    centerX: am5.percent(120),
                    fill: am5.color(0xffffff)
                });

                series.slices.template.set("tooltipText", "{category}: [bold]{valuePercentTotal.formatNumber('0.00')}%[/] ({value})");

                series.ticks.template.set("visible", false);

                // Play initial series animation
                series.appear(1000, 100);

                });

            $('#targeting_stats svg > g > g > g:last-child').css('display','none');

            // for (const key in listTotals) {
            //     if (list.hasOwnProperty(key)) {
            //         const element = listTotals[key];
            //         var target = this.$root.settings.find(setting => setting.id == element.targetId)
            //         const stats = this.$root.targetingStats.totals[target.id];
            //         const statsDelivery = this.$root.targetingStats.data[target.id];

            //         // if (element.using == false && stats.impressions == 0) {
            //         //     empty = empty + 1
            //         //     continue
            //         // }

            //         var row = `
            //         <div class="row" style="margin-top: 5px;  border-bottom: 2px solid #ddd;">
            //             <div  style="width: 33%     " class="col-sm-3 ">
            //                 <h4 style="font-weight: normal; margin-top: 0; ">${target.name}</h4>
            //             </div>
            //             <div class="col-sm-3" style="padding: 0 0 10px 0 ">
            //                 <label for="servedClicks">Impressions:</label> <br>
            //                 <span id="servedClicks" class="campaignStats">${this.formatNumber(stats.impressions)}</span>
            //             </div>
            //             <div class="col-sm-2" style="padding: 0 0 10px 0px ">
            //             <label for="servedClicks">CTR%:</label> <br>
            //             <span id="servedClicks" class="campaignStats">${stats.CTR} %</span>
            //             </div>
            //             <div class="col-sm-3" style=" padding: 0 0 10px 0px ">
            //                 <label for="servedClicks">Delivery Ratio:</label> <br>
            //                 <span id="servedClicks" class="campaignStats">${statsDelivery.deliveryRatio}</span>

            //                 </div>
            //             <span style="border-bottom: 1px solid grey"> </span>
            //             <hr>
            //         </div>`
            //         // $('#targeting_stats').append(row)

            //     }
            // }


            if (list.length == empty) {
                this.$nextTick(() => {
                    $('#targeting_stats_container').append("<h4>No Targeting Stats</h4>")
                })
            }
            $('#targeting-stats-spinner').hide()
        },
        dateSelected: function () {
            var url = "/targetting/stats/" + this.$root.campaign.campaignId;

            if (this.startDate && this.endDate) {
                url += "/" + this.startDate + "/" + this.endDate;
            }

            this.$http.get(url).then((res) => {
                this.$root.targetingStats = res.body;

                this.$nextTick(() => {
                    // this.addTargthis.campaign = row[0];this.campaign = row[0];etingStats()
                })
            });

        }
    },
    mounted() {
        this.$nextTick(() => {
            const el = $(this.$el);
            el.daterangepicker(this.customOptions);
            el.on("apply.daterangepicker", (event, picker) => {

                const startDate = picker.startDate.format(this.format);
                const endDate = picker.endDate.format(this.format);
                this.startDate = picker.startDate.format("YYYY-MM-DD");
                this.endDate = picker.endDate.format("YYYY-MM-DD");
                if (this.isSingleDatePicker) {
                    this.range = startDate;
                } else {
                    this.range = [startDate + "-" + endDate];
                }
            });
            el.on("cancel.daterangepicker", () => {
                if (this.isSingleDatePicker) {
                    this.range = "";
                } else {
                    this.range = [];
                }
            });

            setTimeout(() => {
                this.isDirty = false
            }, 5000);


        });
    },
    beforeDestroy() {
        $(this.$el)
            .daterangepicker("hide")
            .daterangepicker("destroy");
    }
});

function vicinity_Theme(target) {
    if (target instanceof am4core.ColorSet) {
      target.list = [
        am4core.color("#84CEEF"),
        am4core.color("#066287"),
        am4core.color("#ED7D18"),
        am4core.color("#9ED0EA"),
        am4core.color("#0F4F6E"),
        am4core.color("#FAAF40")
      ];
    }
  }

var categoryApp = new Vue({
    el: "#targetting",
    data: {
        totalDeliveryRatio: 0,
        categories: {},
        allData: {},
        settings: [],
        global: [],
        brands: {},
        agencies: {},
        campaigns: {},
        campaign: {},
        presets: {},
        targetingStats: {},
        selectedPreset: {},
        displayPresetOptions: false,
        value: {
            name: 'Shared',
            value: 'shared'
        },
        loadedSettings: false,
        loadedGlobal: false,
        options: [{
                name: 'Shared',
                value: 'shared'
            },
            {
                name: 'Fine',
                value: 'fine'
            },
            {
                name: 'Wifi',
                value: 'wifi'
            },
            {
                name: 'GeoIP',
                value: 'geoip'
            }
        ],
        isDirty: false
    },
    computed: {
        bugetRangeMaxValue() {
            var userRetargeting = this.settings.find(setting => setting.name == "User Retargeting")
            if (userRetargeting.using && this.global.budgetrange > 90) {
                this.global.budgetrange = 90
            }
            return userRetargeting.using ? 90 : 100
        },
        hasTargetingRule() {
            var exists = this.$root.settings.find(setting => setting.using == true);
            if (exists) {
                return true
            }
            return false
        }
    },
    methods: {
        resetRulesToDefault() {
            this.displayPresetOptions = false
            this.$root.settings.find(setting => setting.using = false);

        },
        customLabel(option) {
            return `${option.name}`
        },
        isValidJSON(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        },
        isPresetDirty(changedOBJ, parentKey) {

            if (this.isValidJSON(this.selectedPreset.settings)) {
                var parsedCurrentlySetPreset = JSON.parse(this.selectedPreset.settings)

                if (this.selectedPreset.type == 'parent') {
                    parsedCurrentlySetPreset = parsedCurrentlySetPreset.parentData
                }

                if (JSON.stringify(parsedCurrentlySetPreset[parentKey]) === JSON.stringify(changedOBJ)) {
                    this.isDirty = false;
                } else {
                    this.isDirty = true;
                }
            }
        },
        getCategories: function (resource) {
            this.$http.get("/targetting/categories").then(function (res) {
                this.allData = res.body;
                this.agencies = {};
                this.brands = {};
                this.campaigns = {};
                this.visits = {};
                this.countries = {};
                this.categories = {
                    parent: [],
                    sub: [],
                    data: res.body.data.categories,
                    map: res.body.data.indexed
                };

                for (var i = 1; i < this.settings.length; i++) {
                    this.settings[i].categories = this.categories;
                }
                for (key in res.body.data.categories) {
                    this.categories.parent.push({
                        id: res.body.data.categories[key].id,
                        name: res.body.data.categories[key].name,
                        length: Object.keys(res.body.data.categories[key].sub).length
                    });
                }
            });
        }
    },
    watch: {
        settings: {
            handler: function (val, oldVal) {
                if (val.geofence == false || val.geofence == true) {
                    $(".geofence_yn").bootstrapSwitch();
                }
                if (oldVal && this.loadedSettings) {
                    this.isPresetDirty(val, 'settings')

                }

                this.loadedSettings = true
            },
            deep: true
        },
        global: {
            handler: function (val, oldVal) {
                if (val.placements) {
                    $("#global_placement_yn").bootstrapSwitch();
                }
                if (oldVal && this.loadedGlobal) {
                    this.isPresetDirty(val, 'global')
                }

                this.loadedGlobal = true


            },
            deep: true
        }
    },
    mounted() {
        var tracker = setInterval(function () {
        // var tracker = setTimeout(function () {

            row = table.rows('.selected').data();

            this.campaign = row[0];
            if (row[0]) {
                this.settings = row[0].targeting.settings;
                // this.global = row[0].targeting.global;
                this.selectedPreset = row[0].selectedPreset

                var childComp = this.$root.$children.find(child => {
                    return child.$options.name === "preset-select";
                })
                // childComp.selectedPreset = this.selectedPreset;


                $.fn.bootstrapSwitch.defaults.onColor = 'primary';
                $.fn.bootstrapSwitch.defaults.offColor = 'success';
                $.fn.bootstrapSwitch.defaults.onText = 'Yes';
                $.fn.bootstrapSwitch.defaults.offText = 'No';

                if (this.global.placements) {
                    $("#global_placement_yn").bootstrapSwitch('state', this.global.placements);
                }

                if (this.global.geofence) {
                    $("#geofence_yn").bootstrapSwitch('state', this.global.geofence);
                }

                clearInterval(tracker);
            }



        }.bind(this), 1000);
        this.getCategories();

        this.$http.get("/targetting/presets").then((res) => {
            this.presets = res.body
        });


    }
});
