
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/vue@2.6.14"></script>
<script type="text/javascript" src="https://unpkg.com/@johmun/vue-tags-input/dist/vue-tags-input.js"></script>
<script>

    function tagLocation() {

        if (table.row('.selected').data()) {

        let row = table.rows('.selected').data();
        console.log("0", row[0]);
        if (row.length > 1) {
            swal('Please select only location at a time');
            return;
        }
        loadNameTags(row[0]);
        $('#tagLocations').modal('show');

        } else {
            swal('Please select a Location!');
        }

    }

    //here is the componenet for the location tagging.
	var storedNameTags = {!!json_encode(\App\NameTags::getNameTags('locations'))!!} || [];
	var nameTagsApp = new Vue({
		el: '#name-tags',
		data() {
			return {
				selectedPlacement: null,
				autocompleteItems: storedNameTags.map(item => {
					return item.tag;
				}) || [],
				acceptKeys: [13, ':', ';', ','],
				allowEditTags: false,
				acceptSeparators: [':', ';', ','],
				tags: [],
				tag: '',
				message: '',
				newTags:[]
			}
		},
		computed: {
			filteredTags() {
				return this.autocompleteItems.filter(tag => {
					var text = tag.text ? tag.text : tag;
					return text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
				});
			}
		},
		watch: {
			selectedPlacement: function (newPlacement, oldPlacement) {
				this.selectedPlacement = newPlacement;
				this.fetchTags();
			}
		},
		methods:{
			fetchTags(){
				if(this.selectedPlacement){
					$.ajax({
						url:"/name-tags/location/"+this.selectedPlacement.id,
					})
					.success((response) => {
						this.message = '';
						this.tags = response.tags.map(item => {
							return item.tag;
						});
					})
					.error((response) => {
						this.message = 'Failed to fetch tags! Please reload the page to try again';
					});
				}
			},
			updateTags(tags){
				var postTags = {};
				this.tags = [];

				for(i in tags){
					var text = tags[i].text ? tags[i].text.trim() : tags[i].trim();
					var lowerCaseText = text.toLowerCase();

					foundTag = storedNameTags.find(s => s.tag.toLowerCase() == lowerCaseText);

					if(!foundTag){
						foundTag = {"id": (-(this.newTags.length+1)), "tag": text};
						this.newTags.push(foundTag);
					}

					if(!postTags[lowerCaseText]){
						postTags[lowerCaseText] = foundTag;
						this.tags.push(foundTag.tag);
					}
				}

                //posts update to tags.
				if(this.selectedPlacement){
					$.ajax({
						url:"/name-tags/location/"+this.selectedPlacement.id,
						data: JSON.stringify(postTags),
						type:'POST',
						dataType: 'json',
						contentType: 'application/json',
					})
					.success((response) => {
						this.message = '';

						for(i in this.newTags){
							foundTag = response.tags.find(s => s.tag.toLowerCase() == this.newTags[i].tag.toLowerCase());
							if(foundTag){
								storedNameTags.push(foundTag);
								this.newTags.splice(i,1);
							}
						}

					})
					.error((response) => {
						this.message = 'Failed to save tags!';
					});
				}
			},
		}
	});
    //end of componenet

    //this function loads the selected datatable row into the Vue component.
    function loadNameTags($placement){
        nameTagsApp.$root.$data.selectedPlacement = $placement;
    }



</script>